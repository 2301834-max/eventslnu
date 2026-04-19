<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\QRCode;
use App\Models\Registration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QRRegistrationController extends Controller
{
    /**
     * Student scans an event-registration QR code and gets registered instantly.
     */
    public function registerViaQr(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'qr_code' => 'required|string',
        ]);

        $user = $request->user();

        if (!$user?->hasInstitutionalEmail()) {
            return response()->json([
                'success' => false,
                'message' => 'Only institutional @lnu.edu.ph accounts may register for events.',
            ], 422);
        }

        if (!$user->student_id) {
            return response()->json([
                'success' => false,
                'message' => 'A student ID is required before using event registration QR codes.',
            ], 422);
        }

        $qr = QRCode::where('code', $validated['qr_code'])
            ->where(function ($query) {
                $query->where('type', 'event_registration')
                    ->orWhereNull('type');
            })
            ->first();

        if (!$qr) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid registration QR code.',
            ], 404);
        }

        if (!$qr->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Registration QR is ' . ($qr->isExpired() ? 'expired' : 'inactive'),
            ], 400);
        }

        $event = Event::find($qr->event_id);
        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Event not found.',
            ], 404);
        }

        if (!$event->isOpenForRegistration()) {
            return response()->json([
                'success' => false,
                'message' => $event->hasRegistrationClosed()
                    ? 'Event registration is already closed.'
                    : 'Event is not open for registration.',
            ], 400);
        }

        $existing = Registration::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->latest()
            ->first();

        $approverId = $event->created_by ?: $user->id;

        if ($existing) {
            if (!in_array($existing->status, ['rejected', 'cancelled'], true)) {
                if ($existing->status === 'pending') {
                    $existing->approve($approverId, 'Approved via QR scan');
                    $this->generateAttendanceQrCode($existing);

                    return response()->json([
                        'success' => true,
                        'already_registered' => false,
                        'message' => 'Registration successful. Your attendance QR is ready.',
                        'data' => $existing->load('event', 'user', 'qrCode', 'attendanceRecord'),
                    ], 200);
                }

                if (!$existing->qrCode || !$existing->qrCode->isActive()) {
                    $this->generateAttendanceQrCode($existing);
                }

                return response()->json([
                    'success' => true,
                    'already_registered' => true,
                    'message' => 'You are already registered for this event.',
                    'data' => $existing->load('event', 'user', 'qrCode', 'attendanceRecord'),
                ]);
            }

            $existing->approve($approverId, 'Re-approved via QR scan');
            $this->generateAttendanceQrCode($existing);

            return response()->json([
                'success' => true,
                'already_registered' => false,
                'message' => 'Registration successful. Your attendance QR is ready.',
                'data' => $existing->load('event', 'user', 'qrCode', 'attendanceRecord'),
            ], 200);
        }

        if ($event->isRegistrationFull()) {
            return response()->json([
                'success' => false,
                'message' => 'Event registration is full.',
            ], 400);
        }

        $registration = Registration::create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'status' => 'approved',
        ]);

        $registration->approve($approverId, 'Approved via QR scan');
        $this->generateAttendanceQrCode($registration);

        return response()->json([
            'success' => true,
            'already_registered' => false,
            'message' => 'Registration successful. Your attendance QR is ready.',
            'data' => $registration->load('event', 'user', 'qrCode', 'attendanceRecord'),
        ], 201);
    }

    private function generateAttendanceQrCode(Registration $registration): QRCode
    {
        QRCode::where('registration_id', $registration->id)->delete();

        $code = 'QR-' . $registration->event_id . '-' . $registration->id . '-' . md5($registration->id . time());

        return QRCode::create([
            'registration_id' => $registration->id,
            'event_id' => $registration->event_id,
            'code' => $code,
            'status' => 'active',
            'type' => 'attendance',
            'expires_at' => now()->addDays(30),
            'qr_image_data' => json_encode(['type' => 'text', 'value' => $code]),
        ]);
    }
}
