<?php

namespace Tests\Unit\Models;

use App\Models\Event;
use App\Models\AttendanceRecord;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventTest extends TestCase
{
    use RefreshDatabase;

    public function test_attendance_rate_returns_zero_when_there_are_no_approved_registrations(): void
    {
        $event = new class extends Event
        {
            public function getApprovedRegistrationsCount()
            {
                return 0;
            }

            public function getAttendanceCount()
            {
                return 4;
            }
        };

        $this->assertSame(0, $event->getAttendanceRate());
    }

    public function test_attendance_rate_is_calculated_from_approved_and_attended_counts(): void
    {
        $event = new class extends Event
        {
            public function getApprovedRegistrationsCount()
            {
                return 8;
            }

            public function getAttendanceCount()
            {
                return 6;
            }
        };

        $this->assertSame(75.0, $event->getAttendanceRate());
    }

    public function test_registration_is_not_full_when_max_participants_is_zero(): void
    {
        $event = new class extends Event
        {
            protected $attributes = [
                'max_participants' => 0,
            ];

            public function getApprovedRegistrationsCount()
            {
                return 999;
            }
        };

        $this->assertFalse($event->isRegistrationFull());
    }

    public function test_registration_is_full_when_approved_registrations_reach_capacity(): void
    {
        $event = new class extends Event
        {
            protected $attributes = [
                'max_participants' => 3,
            ];

            public function getApprovedRegistrationsCount()
            {
                return 3;
            }
        };

        $this->assertTrue($event->isRegistrationFull());
    }

    public function test_published_scope_excludes_draft_events(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Event::create($this->eventData($admin, 'Draft Scope Event', 'draft'));
        Event::create($this->eventData($admin, 'Published Scope Event', 'published'));

        $this->assertSame(1, Event::published()->count());
    }

    public function test_upcoming_scope_returns_only_future_events(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Event::create($this->eventData($admin, 'Past Event', 'completed', now()->subDays(2), now()->subDay()));
        Event::create($this->eventData($admin, 'Future Event', 'published', now()->addDay(), now()->addDays(2)));

        $this->assertSame(1, Event::upcoming()->count());
    }

    public function test_ongoing_scope_returns_only_current_ongoing_events(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Event::create($this->eventData($admin, 'Ongoing Event', 'ongoing', now()->subHour(), now()->addHour()));
        Event::create($this->eventData($admin, 'Published Event', 'published', now()->subHour(), now()->addHour()));

        $this->assertSame(1, Event::ongoing()->count());
    }

    public function test_completed_scope_includes_completed_or_past_events(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Event::create($this->eventData($admin, 'Completed Event', 'completed', now()->subDays(3), now()->subDays(2)));
        Event::create($this->eventData($admin, 'Past Published Event', 'published', now()->subDays(2), now()->subDay()));
        Event::create($this->eventData($admin, 'Future Event', 'published', now()->addDay(), now()->addDays(2)));

        $this->assertSame(2, Event::completed()->count());
    }

    public function test_approved_registration_count_only_counts_approved_rows(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $studentA = User::factory()->create(['role' => 'student']);
        $studentB = User::factory()->create(['role' => 'student']);
        $event = Event::create($this->eventData($admin, 'Approved Count Event', 'published'));

        Registration::create([
            'event_id' => $event->id,
            'user_id' => $studentA->id,
            'status' => 'approved',
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        Registration::create([
            'event_id' => $event->id,
            'user_id' => $studentB->id,
            'status' => 'pending',
        ]);

        $this->assertSame(1, $event->getApprovedRegistrationsCount());
    }

    public function test_attendance_count_uses_distinct_users(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);
        $event = Event::create($this->eventData($admin, 'Attendance Count Event', 'published'));
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
            'checked_in_at' => now()->subHours(2),
            'checked_out_at' => now()->subHour(),
            'qr_code_reference' => 'EVENT-ATT-001',
        ]);

        AttendanceRecord::create([
            'registration_id' => $registration->id,
            'event_id' => $event->id,
            'user_id' => $student->id,
            'checked_in_at' => now()->subMinutes(30),
            'checked_out_at' => now(),
            'qr_code_reference' => 'EVENT-ATT-002',
        ]);

        $this->assertSame(1, $event->getAttendanceCount());
    }

    private function eventData(
        User $admin,
        string $title,
        string $status,
        ?\Illuminate\Support\Carbon $start = null,
        ?\Illuminate\Support\Carbon $end = null
    ): array {
        return [
            'title' => $title,
            'description' => $title . ' description',
            'start_date' => $start ?? now()->addDay(),
            'end_date' => $end ?? now()->addDays(2),
            'location' => 'Unit Test Hall',
            'max_participants' => 100,
            'status' => $status,
            'created_by' => $admin->id,
        ];
    }
}
