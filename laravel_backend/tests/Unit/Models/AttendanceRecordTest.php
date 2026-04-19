<?php

namespace Tests\Unit\Models;

use App\Models\AttendanceRecord;
use App\Models\Event;
use App\Models\QRCode;
use App\Models\Registration;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceRecordTest extends TestCase
{
    use RefreshDatabase;

    public function test_duration_is_null_when_student_has_not_checked_out(): void
    {
        $record = new AttendanceRecord([
            'checked_in_at' => Carbon::parse('2026-03-31 09:00:00'),
        ]);

        $this->assertNull($record->getDurationInMinutes());
    }

    public function test_duration_is_calculated_in_minutes(): void
    {
        $record = new AttendanceRecord([
            'checked_in_at' => Carbon::parse('2026-03-31 09:00:00'),
            'checked_out_at' => Carbon::parse('2026-03-31 10:45:00'),
        ]);

        $this->assertEquals(105, $record->getDurationInMinutes());
    }

    public function test_checkout_sets_checked_out_at_timestamp(): void
    {
        $record = $this->makeAttendanceRecord();

        $record->checkout();
        $record->refresh();

        $this->assertNotNull($record->checked_out_at);
    }

    public function test_qr_code_relationship_uses_qr_code_reference(): void
    {
        [$record, $qrCode] = $this->makeAttendanceRecordWithQr();

        $this->assertSame($qrCode->id, $record->qrCode->id);
    }

    private function makeAttendanceRecord(): AttendanceRecord
    {
        [$record] = $this->makeAttendanceRecordWithQr();

        return $record;
    }

    private function makeAttendanceRecordWithQr(): array
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);
        $event = Event::create([
            'title' => fake()->unique()->sentence(3),
            'description' => 'Attendance unit event',
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(2),
            'location' => 'Attendance Hall',
            'max_participants' => 40,
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

        $qrCode = QRCode::create([
            'registration_id' => $registration->id,
            'event_id' => $event->id,
            'code' => 'ATT-QR-001',
            'status' => 'active',
            'type' => 'attendance',
            'expires_at' => now()->addDay(),
        ]);

        $record = AttendanceRecord::create([
            'registration_id' => $registration->id,
            'event_id' => $event->id,
            'user_id' => $student->id,
            'checked_in_at' => now()->subHour(),
            'qr_code_reference' => $qrCode->code,
        ]);

        return [$record, $qrCode];
    }
}
