<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\QRCode;
use App\Models\Registration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QRCodeController extends Controller
{
    /**
     * Generate (or regenerate) a QR code for an approved registration.
     *
     * This powers:
     * - MID-17 Generate QR Code API
     * - MID-20 QR Expiration Logic (sets expires_at based on TTL)
     */
    public function generate(Event $event, Registration $registration, Request $request): JsonResponse
    {
        // Ensure registration belongs to the event
        if ($registration->event_id !== $event->id) {
            return response()->json([
                'success' => false,
                'message' => 'Registration does not belong to this event.',
            ], 404);
        }

        // Only approved registrations can have QR codes
        if ($registration->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'QR codes can only be generated for approved registrations.',
            ], 400);
        }

        $validated = $request->validate([
            // Optional custom TTL in minutes, otherwise use env/config default
            'ttl_minutes' => 'nullable|integer|min:1|max:1440',
        ]);

        $ttlMinutes = $validated['ttl_minutes']
            ?? (int) env('QR_CODE_TTL_MINUTES', 10);

        $expiresAt = now()->addMinutes($ttlMinutes);

        // Revoke any existing active QR for this registration
        QRCode::where('registration_id', $registration->id)
            ->where('event_id', $event->id)
            ->where('status', 'active')
            ->update(['status' => 'revoked']);

        // Generate a new secure random code string
        $code = Str::uuid()->toString().'|'.Str::random(32);

        $qr = QRCode::create([
            'registration_id' => $registration->id,
            'event_id' => $event->id,
            'code' => $code,
            'status' => 'active',
            'expires_at' => $expiresAt,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'QR code generated successfully.',
            'data' => [
                'id' => $qr->id,
                'event_id' => $event->id,
                'registration_id' => $registration->id,
                // This is the string Flutter will encode into a QR image.
                'payload' => $qr->code,
                'status' => $qr->status,
                'expires_at' => $qr->expires_at,
                'ttl_minutes' => $ttlMinutes,
            ],
        ], 201);
    }
}
