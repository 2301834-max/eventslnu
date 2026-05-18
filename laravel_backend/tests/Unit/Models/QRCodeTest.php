<?php

namespace Tests\Unit\Models;

use App\Models\Event;
use App\Models\QRCode;
use App\Models\Registration;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QRCodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_qr_code_without_expiry_is_not_expired(): void
    {
        $qrCode = new QRCode([
            'status' => 'active',
        ]);

        $this->assertFalse($qrCode->isExpired());
    }

    public function test_qr_code_detects_when_it_is_expired(): void
    {
        $qrCode = new QRCode([
            'status' => 'active',
            'expires_at' => Carbon::now()->subMinute(),
        ]);

        $this->assertTrue($qrCode->isExpired());
    }

    public function test_qr_code_is_active_only_when_status_is_active_and_not_expired(): void
    {
        $activeQr = new QRCode([
            'status' => 'active',
            'expires_at' => Carbon::now()->addMinutes(10),
        ]);

        $revokedQr = new QRCode([
            'status' => 'revoked',
            'expires_at' => Carbon::now()->addMinutes(10),
        ]);

        $expiredQr = new QRCode([
            'status' => 'active',
            'expires_at' => Carbon::now()->subMinute(),
        ]);

        $this->assertTrue($activeQr->isActive());
        $this->assertFalse($revokedQr->isActive());
        $this->assertFalse($expiredQr->isActive());
    }

    public function test_mark_as_scanned_updates_status_and_timestamp(): void
    {
        $qrCode = $this->makeQrCode();

        $qrCode->markAsScanned();
        $qrCode->refresh();

        $this->assertSame('scanned', $qrCode->status);
        $this->assertNotNull($qrCode->scanned_at);
    }

    public function test_revoke_updates_status_to_revoked(): void
    {
        $qrCode = $this->makeQrCode();

        $qrCode->revoke();
        $qrCode->refresh();

        $this->assertSame('revoked', $qrCode->status);
    }

    public function test_active_scope_returns_only_non_expired_active_codes(): void
    {
        $active = $this->makeQrCode('QR-ACTIVE-001', 'active', now()->addHour());
        $this->makeQrCode('QR-EXPIRED-001', 'active', now()->subHour());
        $this->makeQrCode('QR-REVOKED-001', 'revoked', now()->addHour());

        $this->assertSame([$active->id], QRCode::active()->pluck('id')->all());
    }

    public function test_scanned_scope_returns_only_scanned_codes(): void
    {
        $scanned = $this->makeQrCode('QR-SCANNED-001', 'scanned', now()->addHour());
        $this->makeQrCode('QR-ACTIVE-002', 'active', now()->addHour());

        $this->assertSame([$scanned->id], QRCode::scanned()->pluck('id')->all());
    }

    private function makeQrCode(string $code = 'QR-UNIT-001', string $status = 'active', $expiresAt = null): QRCode
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);
        $event = Event::create([
            'title' => fake()->unique()->sentence(3),
            'description' => 'QR unit event',
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(2),
            'location' => 'QR Hall',
            'max_participants' => 30,
            'status' => 'published',
            'created_by' => $admin->id,
        ]);
        $registration = Registration::create([
            'event_id' => $event->id,
            'user_id' => $student->id,
            'status' => 'approved',
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        return QRCode::create([
            'registration_id' => $registration->id,
            'event_id' => $event->id,
            'code' => $code,
            'status' => $status,
            'type' => 'attendance',
            'expires_at' => $expiresAt,
        ]);
    }
}
