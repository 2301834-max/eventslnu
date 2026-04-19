<?php

namespace Tests\Feature\Api;

use App\Models\AttendanceRecord;
use App\Models\Event;
use App\Models\QRCode;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AttendanceApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_check_in_with_valid_attendance_qr_creates_attendance_record(): void
    {
        [$admin, $student, $event, $registration, $qrCode] = $this->makeAttendanceContext();

        Sanctum::actingAs($admin);

        $response = $this->postJson("/api/events/{$event->id}/attendance/check-in", [
            'qr_code' => $qrCode->code,
            'location' => 'North Gate',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.user_id', $student->id)
            ->assertJsonPath('data.check_in_location', 'North Gate');

        $this->assertDatabaseHas('attendance_records', [
            'registration_id' => $registration->id,
            'event_id' => $event->id,
            'user_id' => $student->id,
        ]);

        $qrCode->refresh();
        $this->assertSame('scanned', $qrCode->status);
    }

    public function test_check_in_rejects_invalid_qr_code(): void
    {
        [$admin, , $event] = $this->makeAttendanceContext();

        Sanctum::actingAs($admin);

        $this->postJson("/api/events/{$event->id}/attendance/check-in", [
            'qr_code' => 'UNKNOWN-QR',
        ])->assertStatus(404)
            ->assertJsonPath('message', 'Invalid QR code');
    }

    public function test_check_in_rejects_expired_qr_code(): void
    {
        [$admin, , $event, , $qrCode] = $this->makeAttendanceContext([
            'expires_at' => now()->subMinute(),
        ]);

        Sanctum::actingAs($admin);

        $this->postJson("/api/events/{$event->id}/attendance/check-in", [
            'qr_code' => $qrCode->code,
        ])->assertStatus(400)
            ->assertJsonPath('message', 'QR code is expired');
    }

    public function test_check_in_rejects_qr_code_without_registration(): void
    {
        [$admin, , $event] = $this->makeAttendanceContext();

        $qrCode = QRCode::create([
            'registration_id' => null,
            'event_id' => $event->id,
            'code' => 'ATD-UNLINKED-001',
            'status' => 'active',
            'type' => 'attendance',
            'expires_at' => now()->addHour(),
        ]);

        Sanctum::actingAs($admin);

        $this->postJson("/api/events/{$event->id}/attendance/check-in", [
            'qr_code' => $qrCode->code,
        ])->assertStatus(400)
            ->assertJsonPath('message', 'QR code is not linked to a registration.');
    }

    public function test_check_in_rejects_when_student_is_already_checked_in(): void
    {
        [$admin, $student, $event, $registration, $qrCode] = $this->makeAttendanceContext();

        AttendanceRecord::create([
            'registration_id' => $registration->id,
            'event_id' => $event->id,
            'user_id' => $student->id,
            'checked_in_at' => now()->subMinutes(5),
            'qr_code_reference' => $qrCode->code,
        ]);

        Sanctum::actingAs($admin);

        $this->postJson("/api/events/{$event->id}/attendance/check-in", [
            'qr_code' => $qrCode->code,
        ])->assertStatus(400)
            ->assertJsonPath('message', 'User is already checked in. Please check out first.');
    }

    public function test_check_out_marks_attendance_record_as_checked_out(): void
    {
        [$admin, $student, $event, $registration, $qrCode] = $this->makeAttendanceContext();

        $attendance = AttendanceRecord::create([
            'registration_id' => $registration->id,
            'event_id' => $event->id,
            'user_id' => $student->id,
            'checked_in_at' => now()->subMinutes(30),
            'qr_code_reference' => $qrCode->code,
        ]);

        Sanctum::actingAs($admin);

        $this->postJson("/api/events/{$event->id}/attendance/{$attendance->id}/check-out")
            ->assertOk()
            ->assertJsonPath('message', 'Check-out successful');

        $this->assertNotNull($attendance->fresh()->checked_out_at);
    }

    public function test_verify_qr_returns_registration_data_for_valid_code(): void
    {
        [$admin, $student, $event, $registration, $qrCode] = $this->makeAttendanceContext();

        Sanctum::actingAs($admin);

        $this->postJson("/api/events/{$event->id}/attendance/verify-qr", [
            'qr_code' => $qrCode->code,
        ])->assertOk()
            ->assertJsonPath('valid', true)
            ->assertJsonPath('data.user.email', $student->email)
            ->assertJsonPath('data.registration_number', $registration->registration_number);
    }

    public function test_bulk_check_in_processes_successful_and_failed_codes(): void
    {
        [$admin, $studentOne, $event, $registrationOne, $qrOne] = $this->makeAttendanceContext();
        [, $studentTwo, , $registrationTwo, $qrTwo] = $this->makeAttendanceContext([
            'code' => 'ATD-BULK-002',
        ], 'Bulk Event B', $event);

        $inactiveQr = QRCode::create([
            'registration_id' => $registrationTwo->id,
            'event_id' => $event->id,
            'code' => 'ATD-INACTIVE-003',
            'status' => 'revoked',
            'type' => 'attendance',
            'expires_at' => now()->addHour(),
        ]);

        Sanctum::actingAs($admin);

        $response = $this->postJson("/api/events/{$event->id}/attendance/bulk-check-in", [
            'qr_codes' => [$qrOne->code, $inactiveQr->code, 'MISSING-CODE'],
            'location' => 'Lobby',
        ]);

        $response->assertOk()
            ->assertJsonPath('summary.successful', 1)
            ->assertJsonPath('summary.failed', 2);

        $this->assertDatabaseHas('attendance_records', [
            'registration_id' => $registrationOne->id,
            'user_id' => $studentOne->id,
            'check_in_location' => 'Lobby',
        ]);

        $this->assertDatabaseMissing('attendance_records', [
            'registration_id' => $registrationTwo->id,
            'user_id' => $studentTwo->id,
        ]);
    }

    public function test_get_event_attendance_can_filter_checked_in_records(): void
    {
        [$admin, $student, $event, $registration, $qrCode] = $this->makeAttendanceContext();

        AttendanceRecord::create([
            'registration_id' => $registration->id,
            'event_id' => $event->id,
            'user_id' => $student->id,
            'checked_in_at' => now()->subMinutes(10),
            'checked_out_at' => null,
            'check_in_location' => 'North Wing',
            'qr_code_reference' => $qrCode->code,
        ]);

        Sanctum::actingAs($admin);

        $this->getJson("/api/events/{$event->id}/attendance?status=checked_in")
            ->assertOk()
            ->assertJsonPath('summary.currently_present', 1)
            ->assertJsonPath('pagination.total', 1);
    }

    public function test_get_user_attendance_returns_not_found_when_missing(): void
    {
        [$admin, $student, $event] = $this->makeAttendanceContext();

        Sanctum::actingAs($admin);

        $this->getJson("/api/events/{$event->id}/attendance/user/999999")
            ->assertStatus(404)
            ->assertJsonPath('message', 'No attendance record found');
    }

    public function test_get_user_attendance_returns_record_when_present(): void
    {
        [$admin, $student, $event, $registration, $qrCode] = $this->makeAttendanceContext();

        AttendanceRecord::create([
            'registration_id' => $registration->id,
            'event_id' => $event->id,
            'user_id' => $student->id,
            'checked_in_at' => now()->subMinutes(20),
            'checked_out_at' => now()->subMinutes(5),
            'qr_code_reference' => $qrCode->code,
        ]);

        Sanctum::actingAs($admin);

        $this->getJson("/api/events/{$event->id}/attendance/user/{$student->id}")
            ->assertOk()
            ->assertJsonPath('data.user_id', $student->id);
    }

    public function test_bulk_check_in_marks_duplicate_scan_as_failure(): void
    {
        [$admin, $student, $event, $registration, $qrCode] = $this->makeAttendanceContext();

        AttendanceRecord::create([
            'registration_id' => $registration->id,
            'event_id' => $event->id,
            'user_id' => $student->id,
            'checked_in_at' => now()->subMinutes(5),
            'qr_code_reference' => $qrCode->code,
        ]);

        Sanctum::actingAs($admin);

        $this->postJson("/api/events/{$event->id}/attendance/bulk-check-in", [
            'qr_codes' => [$qrCode->code],
            'location' => 'Lobby',
        ])->assertOk()
            ->assertJsonPath('summary.successful', 0)
            ->assertJsonPath('summary.failed', 1)
            ->assertJsonPath('results.0.reason', 'already_checked_in');
    }

    private function makeAttendanceContext(array $qrOverrides = [], string $title = 'Attendance Event', ?Event $existingEvent = null): array
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);
        $event = $existingEvent ?? Event::create([
            'title' => $title,
            'description' => 'Attendance event description',
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(2),
            'location' => 'Auditorium',
            'max_participants' => 120,
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

        $qrCode = QRCode::create(array_merge([
            'registration_id' => $registration->id,
            'event_id' => $event->id,
            'code' => 'ATD-VALID-001',
            'status' => 'active',
            'type' => 'attendance',
            'expires_at' => now()->addHour(),
        ], $qrOverrides));

        return [$admin, $student, $event, $registration, $qrCode];
    }
}
