<?php

namespace Tests\Feature\Dashboard;

use App\Models\AttendanceRecord;
use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_admin_dashboard_view(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Event::create($this->eventData($admin, 'Admin Recent Event', 'published'));

        $this->withSession(['api_token' => 'admin-session-token'])
            ->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Admin Dashboard')
            ->assertSee('Admin Recent Event');
    }

    public function test_student_sees_student_dashboard_view(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);
        Event::create($this->eventData($admin, 'Student Recent Event', 'published'));

        $this->actingAs($student)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Recent Events')
            ->assertSee('Student Recent Event');
    }

    public function test_authenticated_user_can_view_dashboard_events_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);
        Event::create($this->eventData($admin, 'Events Page Event', 'published'));

        $this->actingAs($student)
            ->get(route('dashboard.events'))
            ->assertOk()
            ->assertSee('Events Management');
    }

    public function test_authenticated_user_can_view_dashboard_event_detail_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);
        $event = Event::create($this->eventData($admin, 'Detail Page Event', 'published'));

        $this->actingAs($student)
            ->get(route('dashboard.event-detail', $event))
            ->assertOk()
            ->assertSee('Detail Page Event')
            ->assertSee('Event Details');
    }

    public function test_authenticated_user_can_view_dashboard_registrations_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);
        $event = Event::create($this->eventData($admin, 'Registration Page Event', 'published'));

        Registration::create([
            'event_id' => $event->id,
            'user_id' => $student->id,
            'status' => 'pending',
        ]);

        $this->actingAs($student)
            ->get(route('dashboard.registrations'))
            ->assertOk()
            ->assertSee('Registrations Management');
    }

    public function test_authenticated_user_can_view_dashboard_attendance_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);
        $event = Event::create($this->eventData($admin, 'Attendance Page Event', 'published'));
        $registration = Registration::create([
            'event_id' => $event->id,
            'user_id' => $student->id,
            'status' => 'approved',
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        AttendanceRecord::create([
            'registration_id' => $registration->id,
            'event_id' => $event->id,
            'user_id' => $student->id,
            'checked_in_at' => now()->subHour(),
            'checked_out_at' => now()->subMinutes(15),
            'qr_code_reference' => 'DASH-ATT-001',
        ]);

        $this->actingAs($student)
            ->get(route('dashboard.attendance'))
            ->assertOk()
            ->assertSee('Attendance Tracking');
    }

    public function test_authenticated_user_can_view_dashboard_reports_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);
        Event::create($this->eventData($admin, 'Completed Report Event', 'completed'));

        $this->actingAs($student)
            ->get(route('dashboard.reports'))
            ->assertOk()
            ->assertSee('Generate Report')
            ->assertSee('Quick Reports');
    }

    private function eventData(User $admin, string $title, string $status): array
    {
        return [
            'title' => $title,
            'description' => $title . ' description',
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(2),
            'location' => 'Dashboard Hall',
            'max_participants' => 50,
            'status' => $status,
            'created_by' => $admin->id,
        ];
    }
}
