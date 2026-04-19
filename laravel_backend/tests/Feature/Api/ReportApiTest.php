<?php

namespace Tests\Feature\Api;

use App\Models\AttendanceRecord;
use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReportApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_attendance_csv_export_contains_attendance_rows(): void
    {
        [$admin, $event, $attendance] = $this->makeReportContext();

        Sanctum::actingAs($admin);

        $response = $this->get("/api/events/{$event->id}/reports/attendance/csv");

        $response->assertOk();

        $content = $response->streamedContent();

        $this->assertStringContainsString('Attendance Report Student', $content);
        $this->assertStringContainsString('Checked Out', $content);
    }

    public function test_attendance_csv_export_honors_date_filters(): void
    {
        [$admin, $event] = $this->makeReportContext();

        Sanctum::actingAs($admin);

        $response = $this->get("/api/events/{$event->id}/reports/attendance/csv?from_date=" . now()->subHours(4)->toDateTimeString());

        $content = $response->streamedContent();

        $this->assertStringContainsString('Attendance Report Student', $content);
        $this->assertStringNotContainsString('Old Attendance Student', $content);
    }

    public function test_registrations_csv_export_honors_status_filter(): void
    {
        [$admin, $event] = $this->makeReportContext();

        Sanctum::actingAs($admin);

        $response = $this->get("/api/events/{$event->id}/reports/registrations/csv?status=approved");
        $content = $response->streamedContent();

        $this->assertStringContainsString('Approved Student', $content);
        $this->assertStringNotContainsString('Pending Student', $content);
    }

    public function test_summary_export_returns_event_totals(): void
    {
        [$admin, $event] = $this->makeReportContext();

        Sanctum::actingAs($admin);

        $this->getJson("/api/events/{$event->id}/reports/summary")
            ->assertOk()
            ->assertJsonPath('data.registration_summary.total_registrations', 4)
            ->assertJsonPath('data.attendance_summary.total_attended', 2)
            ->assertJsonPath('data.attendance_summary.no_show_count', 1);
    }

    public function test_location_breakdown_export_groups_locations(): void
    {
        [$admin, $event] = $this->makeReportContext();

        Sanctum::actingAs($admin);

        $response = $this->get("/api/events/{$event->id}/reports/location/csv");
        $content = $response->streamedContent();

        $this->assertStringContainsString('South Gate', $content);
        $this->assertStringContainsString('North Gate', $content);
    }

    public function test_no_show_report_contains_only_approved_students_without_attendance(): void
    {
        [$admin, $event] = $this->makeReportContext();

        Sanctum::actingAs($admin);

        $response = $this->get("/api/events/{$event->id}/reports/no-shows/csv");
        $content = $response->streamedContent();

        $this->assertStringContainsString('Approved Student', $content);
        $this->assertStringNotContainsString('Attendance Report Student', $content);
    }

    public function test_time_analysis_export_contains_formatted_duration(): void
    {
        [$admin, $event] = $this->makeReportContext();

        Sanctum::actingAs($admin);

        $response = $this->get("/api/events/{$event->id}/reports/time-analysis/csv");
        $content = $response->streamedContent();

        $this->assertStringContainsString('1h 0min', $content);
        $this->assertStringContainsString('2h 0min', $content);
    }

    private function makeReportContext(): array
    {
        $admin = User::factory()->create(['role' => 'admin', 'name' => 'Admin Reporter']);
        $recentStudent = User::factory()->create(['role' => 'student', 'name' => 'Attendance Report Student']);
        $oldStudent = User::factory()->create(['role' => 'student', 'name' => 'Old Attendance Student']);
        $noShowStudent = User::factory()->create(['role' => 'student', 'name' => 'Approved Student']);
        $pendingStudent = User::factory()->create(['role' => 'student', 'name' => 'Pending Student']);

        $event = Event::create([
            'title' => 'Report Export Event',
            'description' => 'Report export description',
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'location' => 'Convention Center',
            'max_participants' => 60,
            'status' => 'completed',
            'created_by' => $admin->id,
        ]);

        $recentRegistration = Registration::create([
            'event_id' => $event->id,
            'user_id' => $recentStudent->id,
            'status' => 'approved',
            'approved_by' => $admin->id,
            'approved_at' => now()->subDays(2),
        ]);

        $oldRegistration = Registration::create([
            'event_id' => $event->id,
            'user_id' => $oldStudent->id,
            'status' => 'approved',
            'approved_by' => $admin->id,
            'approved_at' => now()->subDays(2),
        ]);

        Registration::create([
            'event_id' => $event->id,
            'user_id' => $noShowStudent->id,
            'status' => 'approved',
            'approved_by' => $admin->id,
            'approved_at' => now()->subDays(2),
        ]);

        Registration::create([
            'event_id' => $event->id,
            'user_id' => $pendingStudent->id,
            'status' => 'pending',
        ]);

        $recentAttendance = AttendanceRecord::create([
            'registration_id' => $recentRegistration->id,
            'event_id' => $event->id,
            'user_id' => $recentStudent->id,
            'checked_in_at' => now()->subHours(3),
            'checked_out_at' => now()->subHours(2),
            'check_in_location' => 'South Gate',
            'qr_code_reference' => 'REPORT-ATT-001',
        ]);

        AttendanceRecord::create([
            'registration_id' => $oldRegistration->id,
            'event_id' => $event->id,
            'user_id' => $oldStudent->id,
            'checked_in_at' => now()->subDay()->subHours(2),
            'checked_out_at' => now()->subDay(),
            'check_in_location' => 'North Gate',
            'qr_code_reference' => 'REPORT-ATT-002',
        ]);

        return [$admin, $event, $recentAttendance];
    }
}
