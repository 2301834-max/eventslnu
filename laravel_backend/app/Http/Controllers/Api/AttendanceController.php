<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Event;
use App\Models\QRCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /**
     * Scan QR code and check in
     */
    public function checkIn(Request $request, Event $event): JsonResponse
    {
        $validated = $request->validate([
            'qr_code' => 'required|string',
            'location' => 'nullable|string|max:255',
        ]);

        // Find QR code (attendance-type only)
        $qrCode = QRCode::where('code', $validated['qr_code'])
            ->where('event_id', $event->id)
            ->where('type', 'attendance')
            ->first();

        if (! $qrCode) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid QR code',
            ], 404);
        }

        // Check if QR code is active
        if (! $qrCode->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'QR code is '.($qrCode->isExpired() ? 'expired' : 'inactive'),
            ], 400);
        }

        // Ensure QR code is linked to a registration
        if (! $qrCode->registration) {
            return response()->json([
                'success' => false,
                'message' => 'QR code is not linked to a registration.',
            ], 400);
        }

        // Check if already checked in
        $existing = AttendanceRecord::where('registration_id', $qrCode->registration_id)
            ->where('event_id', $event->id)
            ->whereNull('checked_out_at')
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'User is already checked in. Please check out first.',
                'data' => $existing->load('user', 'registration'),
            ], 400);
        }

        // Create attendance record
        $attendance = AttendanceRecord::create([
            'registration_id' => $qrCode->registration_id,
            'event_id' => $event->id,
            'user_id' => $qrCode->registration->user_id,
            'checked_in_at' => now(),
            'qr_code_reference' => $qrCode->code,
            'check_in_location' => $validated['location'] ?? null,
        ]);

        // Update QR code status
        $qrCode->markAsScanned();

        return response()->json([
            'success' => true,
            'message' => 'Check-in successful',
            'data' => $attendance->load('user', 'registration'),
        ], 201);
    }

    /**
     * Check out an attendee
     */
    public function checkOut(Request $request, Event $event, AttendanceRecord $attendance): JsonResponse
    {
        if ($attendance->event_id !== $event->id) {
            return response()->json([
                'success' => false,
                'message' => 'Attendance record not found',
            ], 404);
        }

        if ($attendance->checked_out_at) {
            return response()->json([
                'success' => false,
                'message' => 'User has already checked out',
            ], 400);
        }

        $attendance->checkout();

        return response()->json([
            'success' => true,
            'message' => 'Check-out successful',
            'data' => $attendance->load('user', 'registration'),
        ]);
    }

    /**
     * Get attendance records for an event
     */
    public function getEventAttendance(Request $request, Event $event): JsonResponse
    {
        $query = $event->attendanceRecords()->with('user', 'registration');

        // Filter by checked in/out
        if ($request->has('status')) {
            if ($request->status === 'checked_in') {
                $query->whereNull('checked_out_at');
            } elseif ($request->status === 'checked_out') {
                $query->whereNotNull('checked_out_at');
            }
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->where('checked_in_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->where('checked_in_at', '<=', $request->to_date);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'checked_in_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $records = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $records->items(),
            'pagination' => [
                'total' => $records->total(),
                'per_page' => $records->perPage(),
                'current_page' => $records->currentPage(),
                'last_page' => $records->lastPage(),
            ],
            'summary' => [
                'total_attended' => $event->attendanceRecords()->count(),
                'currently_present' => $event->attendanceRecords()->whereNull('checked_out_at')->count(),
                'checked_out' => $event->attendanceRecords()->whereNotNull('checked_out_at')->count(),
            ],
        ]);
    }

    /**
     * Get attendance record for a user in an event
     */
    public function getUserAttendance(Event $event, $userId): JsonResponse
    {
        $attendance = AttendanceRecord::where('event_id', $event->id)
            ->where('user_id', $userId)
            ->first();

        if (! $attendance) {
            return response()->json([
                'success' => false,
                'message' => 'No attendance record found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $attendance->load('user', 'registration', 'qrCode'),
        ]);
    }

    /**
     * Verify QR code (without checking in)
     */
    public function verifyQRCode(Request $request, Event $event): JsonResponse
    {
        $validated = $request->validate([
            'qr_code' => 'required|string',
        ]);

        $qrCode = QRCode::where('code', $validated['qr_code'])
            ->where('event_id', $event->id)
            ->where('type', 'attendance')
            ->first();

        if (! $qrCode) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid QR code',
                'valid' => false,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'QR code is valid',
            'valid' => true,
            'data' => $qrCode->registration
                ? [
                    'qr_code_id' => $qrCode->id,
                    'status' => $qrCode->status,
                    'user' => $qrCode->registration->user->only(['id', 'name', 'email']),
                    'registration_number' => $qrCode->registration->registration_number,
                    'is_active' => $qrCode->isActive(),
                    'is_expired' => $qrCode->isExpired(),
                    'expires_at' => $qrCode->expires_at,
                ]
                : null,
        ]);
    }

    /**
     * Bulk check-in (for multiple attendees at once)
     */
    public function bulkCheckIn(Request $request, Event $event): JsonResponse
    {
        $validated = $request->validate([
            'qr_codes' => 'required|array',
            'qr_codes.*' => 'string',
            'location' => 'nullable|string|max:255',
        ]);

        $successful = 0;
        $failed = 0;
        $results = [];

        foreach ($validated['qr_codes'] as $qrCode) {
            $qr = QRCode::where('code', $qrCode)
                ->where('event_id', $event->id)
                ->where('type', 'attendance')
                ->first();

            if (! $qr || ! $qr->isActive() || ! $qr->registration) {
                $failed++;
                $results[] = [
                    'qr_code' => $qrCode,
                    'status' => 'failed',
                    'reason' => $qr ? 'inactive_or_unlinked' : 'not_found',
                ];

                continue;
            }

            $alreadyCheckedIn = AttendanceRecord::where('registration_id', $qr->registration_id)
                ->where('event_id', $event->id)
                ->whereNull('checked_out_at')
                ->exists();

            if ($alreadyCheckedIn) {
                $failed++;
                $results[] = [
                    'qr_code' => $qrCode,
                    'status' => 'failed',
                    'reason' => 'already_checked_in',
                ];

                continue;
            }

            try {
                AttendanceRecord::create([
                    'registration_id' => $qr->registration_id,
                    'event_id' => $event->id,
                    'user_id' => $qr->registration->user_id,
                    'checked_in_at' => now(),
                    'qr_code_reference' => $qr->code,
                    'check_in_location' => $validated['location'] ?? null,
                ]);

                $qr->markAsScanned();
                $successful++;
                $results[] = [
                    'qr_code' => $qrCode,
                    'status' => 'success',
                    'user' => $qr->registration->user->name,
                ];
            } catch (\Exception $e) {
                $failed++;
                $results[] = [
                    'qr_code' => $qrCode,
                    'status' => 'failed',
                    'reason' => 'error',
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Processed {$successful} check-ins, {$failed} failed",
            'summary' => [
                'successful' => $successful,
                'failed' => $failed,
            ],
            'results' => $results,
        ]);
    }
}
