<?php

namespace Tests\Unit\Models;

use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_number_is_generated_when_registration_is_created(): void
    {
        [$admin, $student, $event] = $this->makeContext();

        $registration = Registration::create([
            'event_id' => $event->id,
            'user_id' => $student->id,
            'status' => 'pending',
        ]);

        $this->assertStringStartsWith('REG-', $registration->registration_number);
        $this->assertNotEmpty($registration->registration_number);
    }

    public function test_approve_sets_status_admin_and_timestamp(): void
    {
        [$admin, $student, $event] = $this->makeContext();

        $registration = Registration::create([
            'event_id' => $event->id,
            'user_id' => $student->id,
            'status' => 'pending',
        ]);

        $registration->approve($admin->id, 'Approved in unit test');

        $registration->refresh();

        $this->assertSame('approved', $registration->status);
        $this->assertSame($admin->id, $registration->approved_by);
        $this->assertSame('Approved in unit test', $registration->remarks);
        $this->assertNotNull($registration->approved_at);
    }

    public function test_reject_sets_status_admin_and_remarks(): void
    {
        [$admin, $student, $event] = $this->makeContext();

        $registration = Registration::create([
            'event_id' => $event->id,
            'user_id' => $student->id,
            'status' => 'pending',
        ]);

        $registration->reject($admin->id, 'Rejected in unit test');

        $registration->refresh();

        $this->assertSame('rejected', $registration->status);
        $this->assertSame($admin->id, $registration->approved_by);
        $this->assertSame('Rejected in unit test', $registration->remarks);
    }

    public function test_pending_scope_returns_only_pending_registrations(): void
    {
        [$admin, $student, $event] = $this->makeContext();
        $anotherStudent = User::factory()->create(['role' => 'student']);

        Registration::create([
            'event_id' => $event->id,
            'user_id' => $student->id,
            'status' => 'pending',
        ]);

        Registration::create([
            'event_id' => $event->id,
            'user_id' => $anotherStudent->id,
            'status' => 'approved',
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        $this->assertSame(1, Registration::pending()->count());
    }

    private function makeContext(): array
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);
        $event = Event::create([
            'title' => 'Registration Unit Event',
            'description' => 'Unit event description',
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(2),
            'location' => 'Unit Hall',
            'max_participants' => 60,
            'status' => 'published',
            'created_by' => $admin->id,
        ]);

        return [$admin, $student, $event];
    }
}
