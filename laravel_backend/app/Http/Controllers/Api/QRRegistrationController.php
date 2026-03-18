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

        $qr = QRCode::where('code', $validated['qr_code'])
            ->where(function ($q) {
                $q->where('type', 'event_registration')
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

        // Allow registration only for events that are not draft/cancelled.
        if (in_array($event->status, ['draft', 'cancelled'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Event is not open for registration.',
            ], 400);
        }

        // Prevent duplicate registration, but allow re-entry if previously rejected/cancelled
        // by reusing the same row instead of creating a new one (to satisfy unique index).
        $existing = Registration::where('event_id', $event->id)
            ->where('user_id', auth()->id())
            ->latest()
            ->first();

        if ($existing) {
            if (!in_array($existing->status, ['rejected', 'cancelled'], true)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Already registered.',
                    'data' => $existing->load('event', 'qrCode', 'attendanceRecord'),
                ]);
            }

            // Re-activate existing registration
            $existing->approve(auth()->id(), 'Re-approved via QR scan');

            return response()->json([
                'success' => true,
                'message' => 'Registration successful (re-activated).',
                'data' => $existing->load('event', 'user'),
            ], 200);
        }

        if ($event->isRegistrationFull()) {
            return response()->json([
                'success' => false,
                'message' => 'Event registration is full.',
            ], 400);
        }

        // Instantly register AND approve (no rejection flow for QR) for first-time scan.
        $registration = Registration::create([
            'event_id' => $event->id,
            'user_id' => auth()->id(),
            'status' => 'approved',
        ]);

        $registration->approve(auth()->id(), 'Approved via QR scan');

        return response()->json([
            'success' => true,
            'message' => 'Registration successful.',
            'data' => $registration->load('event', 'user'),
        ], 201);
    }
}

