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

class StatisticsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_event_stats_returns_registration_and_attendance_summary(): void
    {
        [$admin, $event, $approvedRegistration] = $this->makeStatisticsContext();

        Sanctum::actingAs($admin);

        $this->getJson("/api/events/{$event->id}/statistics")
            ->assertOk()
            ->assertJsonPath('registration_stats.total', 3)
            ->assertJsonPath('registration_stats.approved', 2)
            ->assertJsonPath('registration_stats.pending', 1)
            ->assertJsonPath('attendance_stats.total_attended', 1)
            ->assertJsonPath('event_info.available_slots', 48);
    }

    public function test_hourly_attendance_groups_records_by_hour(): void
    {
        [$admin, $event, $approvedRegistration] = $this->makeStatisticsContext();

        AttendanceRecord::create([
            'registration_id' => $approvedRegistration->id,
            'event_id' => $event->id,
            'user_id' => $approvedRegistration->user_id,
            'checked_in_at' => now()->subHours(2)->startOfHour()->addMinutes(5),
            'checked_out_at' => now()->subHours(2)->startOfHour()->addHour(),
            'qr_code_reference' => 'STAT-HOURLY-002',
        ]);

        Sanctum::actingAs($admin);

        $response = $this->getJson("/api/events/{$event->id}/statistics/hourly-attendance");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('total_records', 2);

        $this->assertCount(2, $response->json('data'));
    }

    public function test_daily_attendance_groups_records_by_day(): void
    {
        [$admin, $event, $approvedRegistration] = $this->makeStatisticsContext();

        AttendanceRecord::create([
            'registration_id' => $approvedRegistration->id,
            'event_id' => $event->id,
            'user_id' => $approvedRegistration->user_id,
            'checked_in_at' => now()->subDay()->startOfDay()->addHours(9),
            'checked_out_at' => now()->subDay()->startOfDay()->addHours(11),
            'qr_code_reference' => 'STAT-DAILY-002',
        ]);

        Sanctum::actingAs($admin);

        $response = $this->getJson("/api/events/{$event->id}/statistics/daily-attendance");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('total_unique_attendees', 2);

        $this->assertCount(2, $response->json('data'));
    }

    public function test_location_stats_groups_records_by_check_in_location(): void
    {
        [$admin, $event, $approvedRegistration] = $this->makeStatisticsContext();

        AttendanceRecord::create([
            'registration_id' => $approvedRegistration->id,
            'event_id' => $event->id,
            'user_id' => $approvedRegistration->user_id,
            'checked_in_at' => now()->subDay(),
            'checked_out_at' => now()->subDay()->addHour(),
            'check_in_location' => 'Library Gate',
            'qr_code_reference' => 'STAT-LOC-002',
        ]);

        Sanctum::actingAs($admin);

        $response = $this->getJson("/api/events/{$event->id}/statistics/location-stats");

        $response->assertOk()
            ->assertJsonPath('total_with_location', 2);
    }

    public function test_user_attendance_patterns_returns_user_level_rows(): void
    {
        [$admin, $event] = $this->makeStatisticsContext();

        Sanctum::actingAs($admin);

        $response = $this->getJson("/api/events/{$event->id}/statistics/user-patterns");

        $response->assertOk()
            ->assertJsonPath('total_attendance_records', 1)
            ->assertJsonPath('data.0.location', 'Main Gate');
    }

    public function test_registration_attendance_comparison_returns_attended_and_not_attended_counts(): void
    {
        [$admin, $event] = $this->makeStatisticsContext();

        Sanctum::actingAs($admin);

        $this->getJson("/api/events/{$event->id}/statistics/comparison")
            ->assertOk()
            ->assertJsonPath('summary.total_approved', 2)
            ->assertJsonPath('summary.attended', 1)
            ->assertJsonPath('summary.not_attended', 2);
    }

    public function test_realtime_metrics_returns_live_counts(): void
    {
        [$admin, $event, $approvedRegistration] = $this->makeStatisticsContext('ongoing');

        AttendanceRecord::create([
            'registration_id' => $approvedRegistration->id,
            'event_id' => $event->id,
            'user_id' => $approvedRegistration->user_id,
            'checked_in_at' => now()->subMinutes(30),
            'checked_out_at' => null,
            'check_in_location' => 'Live Hall',
            'qr_code_reference' => 'STAT-LIVE-001',
        ]);

        Sanctum::actingAs($admin);

        $this->getJson("/api/events/{$event->id}/statistics/realtime")
            ->assertOk()
            ->assertJsonPath('is_ongoing', true)
            ->assertJsonPath('metrics.currently_present', 1)
            ->assertJsonPath('metrics.total_attended_so_far', 2);
    }

    public function test_no_show_analysis_returns_approved_registrations_without_attendance(): void
    {
        [$admin, $event] = $this->makeStatisticsContext();

        Sanctum::actingAs($admin);

        $this->getJson("/api/events/{$event->id}/statistics/no-shows")
            ->assertOk()
            ->assertJsonPath('summary.total_no_shows', 1)
            ->assertJsonPath('summary.no_show_rate', '50%');
    }

    private function makeStatisticsContext(string $eventStatus = 'published'): array
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $attendee = User::factory()->create(['role' => 'student', 'name' => 'Attendee Student']);
        $noShowStudent = User::factory()->create(['role' => 'student', 'name' => 'No Show Student']);
        $pendingStudent = User::factory()->create(['role' => 'student', 'name' => 'Pending Student']);

        $event = Event::create([
            'title' => 'Statistics Event',
            'description' => 'Statistics event description',
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'location' => 'Campus Gym',
            'max_participants' => 50,
            'status' => $eventStatus,
            'created_by' => $admin->id,
        ]);

        $approvedRegistration = Registration::create([
            'event_id' => $event->id,
            'user_id' => $attendee->id,
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

        QRCode::create([
            'registration_id' => $approvedRegistration->id,
            'event_id' => $event->id,
            'code' => 'STAT-QR-001',
            'status' => 'scanned',
            'type' => 'attendance',
            'expires_at' => now()->addDay(),
            'scanned_at' => now()->subHour(),
        ]);

        AttendanceRecord::create([
            'registration_id' => $approvedRegistration->id,
            'event_id' => $event->id,
            'user_id' => $attendee->id,
            'checked_in_at' => now()->subHours(3),
            'checked_out_at' => now()->subHours(2),
            'check_in_location' => 'Main Gate',
            'qr_code_reference' => 'STAT-QR-001',
        ]);

        return [$admin, $event, $approvedRegistration];
    }
}
