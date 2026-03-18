<?php

namespace App\Http\Controllers\Api;

use App\Models\Event;
use App\Models\Registration;
use App\Models\QRCode;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class RegistrationController extends Controller
{
    /**
     * Get the authenticated user's registration for an event (if any),
     * including QR code and attendance record.
     */
    public function me(Event $event): JsonResponse
    {
        $registration = Registration::where('event_id', $event->id)
            ->where('user_id', auth()->id())
            ->with('user', 'event', 'qrCode', 'attendanceRecord')
            ->first();

        return response()->json([
            'success' => true,
            'data' => $registration,
        ]);
    }

    /**
     * Get all registrations for an event
     */
    public function index(Request $request, Event $event): JsonResponse
    {
        $query = $event->registrations()->with('user', 'approver');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search by user name or email
        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $registrations = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $registrations->items(),
            'pagination' => [
                'total' => $registrations->total(),
                'per_page' => $registrations->perPage(),
                'current_page' => $registrations->currentPage(),
                'last_page' => $registrations->lastPage(),
            ]
        ]);
    }

    /**
     * Create a registration (user self-register)
     */
    public function store(Request $request, Event $event): JsonResponse
    {
        // Check if event is open for registration
        if ($event->status === 'draft' || $event->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Event is not open for registration'
            ], 400);
        }

        // Check if user already registered, but allow re-register if last was rejected/cancelled
        // by reusing the existing row (to satisfy unique index).
        $existing = Registration::where('event_id', $event->id)
            ->where('user_id', auth()->id())
            ->latest()
            ->first();

        if ($existing) {
            if (!in_array($existing->status, ['rejected', 'cancelled'], true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are already registered for this event'
                ], 400);
            }

            // Reuse existing row: set back to pending
            $existing->update([
                'status' => 'pending',
                'approved_at' => null,
                'approved_by' => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Registration successful. Waiting for approval.',
                'data' => $existing->load('user', 'event'),
            ], 200);
        }

        // Check if registration is full
        if ($event->isRegistrationFull()) {
            return response()->json([
                'success' => false,
                'message' => 'Event registration is full'
            ], 400);
        }

        $registration = Registration::create([
            'event_id' => $event->id,
            'user_id' => auth()->id(),
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Registration successful. Waiting for approval.',
            'data' => $registration->load('user', 'event')
        ], 201);
    }

    /**
     * Get a single registration
     */
    public function show(Event $event, Registration $registration): JsonResponse
    {
        if ($registration->event_id !== $event->id) {
            return response()->json([
                'success' => false,
                'message' => 'Registration not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $registration->load('user', 'approver', 'event', 'qrCode', 'attendanceRecord')
        ]);
    }

    /**
     * Approve a registration
     */
    public function approve(Request $request, Event $event, Registration $registration): JsonResponse
    {
        if ($registration->event_id !== $event->id) {
            return response()->json([
                'success' => false,
                'message' => 'Registration not found'
            ], 404);
        }

        if ($registration->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending registrations can be approved'
            ], 400);
        }

        $remarks = $request->get('remarks');
        $registration->approve(auth()->id(), $remarks);

        // Generate QR code for this registration
        $qrCode = $this->generateQRCode($registration);

        return response()->json([
            'success' => true,
            'message' => 'Registration approved successfully',
            'data' => $registration->load('user', 'approver'),
            'qr_code' => $qrCode->only(['id', 'code', 'qr_image_data'])
        ]);
    }

    /**
     * Reject a registration
     */
    public function reject(Request $request, Event $event, Registration $registration): JsonResponse
    {
        if ($registration->event_id !== $event->id) {
            return response()->json([
                'success' => false,
                'message' => 'Registration not found'
            ], 404);
        }

        if ($registration->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending registrations can be rejected'
            ], 400);
        }

        $validated = $request->validate([
            'remarks' => 'required|string'
        ]);

        $registration->reject(auth()->id(), $validated['remarks']);

        return response()->json([
            'success' => true,
            'message' => 'Registration rejected successfully',
            'data' => $registration->load('user', 'approver')
        ]);
    }

    /**
     * Cancel a registration
     */
    public function cancel(Event $event, Registration $registration): JsonResponse
    {
        if ($registration->event_id !== $event->id) {
            return response()->json([
                'success' => false,
                'message' => 'Registration not found'
            ], 404);
        }

        if ($registration->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Registration is already cancelled'
            ], 400);
        }

        $registration->update(['status' => 'cancelled']);

        // Revoke QR code
        if ($registration->qrCode) {
            $registration->qrCode->revoke();
        }

        return response()->json([
            'success' => true,
            'message' => 'Registration cancelled successfully',
            'data' => $registration
        ]);
    }

    /**
     * Generate QR code for a registration
     */
    private function generateQRCode(Registration $registration)
    {
        // Delete existing QR code if any
        QRCode::where('registration_id', $registration->id)->delete();

        // Generate unique code
        $code = 'QR-' . $registration->event_id . '-' . $registration->id . '-' . md5($registration->id . time());

        // Create QR code entry (without image generation to avoid GD dependency)
        $qrCode = QRCode::create([
            'registration_id' => $registration->id,
            'event_id' => $registration->event_id,
            'code' => $code,
            'status' => 'active',
            'expires_at' => now()->addDays(30),
            'qr_image_data' => json_encode(['type' => 'text', 'value' => $code]), // Store as JSON
        ]);

        return $qrCode;
    }

    /**
     * Bulk approve registrations
     */
    public function bulkApprove(Request $request, Event $event): JsonResponse
    {
        $validated = $request->validate([
            'registration_ids' => 'required|array',
            'registration_ids.*' => 'integer|exists:registrations,id',
            'remarks' => 'nullable|string'
        ]);

        $registrations = Registration::whereIn('id', $validated['registration_ids'])
            ->where('event_id', $event->id)
            ->where('status', 'pending')
            ->get();

        $approved = 0;
        foreach ($registrations as $registration) {
            $registration->approve(auth()->id(), $validated['remarks'] ?? null);
            $this->generateQRCode($registration);
            $approved++;
        }

        return response()->json([
            'success' => true,
            'message' => "{$approved} registrations approved successfully",
            'approved_count' => $approved
        ]);
    }
}