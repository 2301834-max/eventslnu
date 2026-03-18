<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\QRCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class AdminEventRegistrationQrController extends Controller
{
    public function generate(Event $event): RedirectResponse
    {
        // Revoke any previous active event registration QR for this event.
        QRCode::where('event_id', $event->id)
            ->where('type', 'event_registration')
            ->where('status', 'active')
            ->update(['status' => 'revoked']);

        $code = 'EVTREG|' . $event->id . '|' . Str::uuid()->toString();

        QRCode::create([
            'registration_id' => null,
            'event_id' => $event->id,
            'code' => $code,
            'status' => 'active',
            'type' => 'event_registration',
            'expires_at' => now()->addDays(7),
        ]);

        return redirect()
            ->route('admin.events.show', $event)
            ->with('success', 'Event registration QR generated.');
    }
}

