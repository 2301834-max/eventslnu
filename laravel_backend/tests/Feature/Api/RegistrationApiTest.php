<?php

namespace Tests\Feature\Api;

use App\Models\Event;
use App\Models\QRCode;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RegistrationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_register_for_published_event(): void
    {
        [$admin, $student, $event] = $this->makePublishedEventContext();

        Sanctum::actingAs($student);

        $response = $this->postJson("/api/events/{$event->id}/registrations", [
            'student_id' => $student->student_id,
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'pending');

        $this->assertDatabaseHas('registrations', [
            'event_id' => $event->id,
            'user_id' => $student->id,
            'status' => 'pending',
        ]);
    }

    public function test_student_cannot_register_for_draft_event(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);
        $event = $this->makeEvent($admin, 'Draft Event', 'draft');

        Sanctum::actingAs($student);

        $this->postJson("/api/events/{$event->id}/registrations", [
            'student_id' => $student->student_id,
        ])
            ->assertStatus(400)
            ->assertJsonPath('message', 'Event is not open for registration');
    }

    public function test_student_cannot_register_twice_while_registration_is_active(): void
    {
        [$admin, $student, $event] = $this->makePublishedEventContext();

        Registration::create([
            'event_id' => $event->id,
            'user_id' => $student->id,
            'status' => 'pending',
        ]);

        Sanctum::actingAs($student);

        $this->postJson("/api/events/{$event->id}/registrations", [
            'student_id' => $student->student_id,
        ])
            ->assertStatus(400)
            ->assertJsonPath('message', 'You are already registered for this event');
    }

    public function test_student_can_reregister_when_previous_registration_was_rejected(): void
    {
        [$admin, $student, $event] = $this->makePublishedEventContext();

        $registration = Registration::create([
            'event_id' => $event->id,
            'user_id' => $student->id,
            'status' => 'rejected',
            'approved_by' => $admin->id,
            'remarks' => 'Missing requirements',
        ]);

        Sanctum::actingAs($student);

        $response = $this->postJson("/api/events/{$event->id}/registrations", [
            'student_id' => $student->student_id,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.id', $registration->id)
            ->assertJsonPath('data.status', 'pending');

        $registration->refresh();

        $this->assertSame('pending', $registration->status);
        $this->assertNull($registration->approved_at);
        $this->assertNull($registration->approved_by);
    }

    public function test_admin_can_approve_pending_registration_and_generate_qr_code(): void
    {
        [$admin, $student, $event] = $this->makePublishedEventContext();

        $registration = Registration::create([
            'event_id' => $event->id,
            'user_id' => $student->id,
            'status' => 'pending',
        ]);

        Sanctum::actingAs($admin);

        $response = $this->postJson("/api/events/{$event->id}/registrations/{$registration->id}/approve", [
            'remarks' => 'Approved for participation',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.status', 'approved')
            ->assertJsonPath('qr_code.id', QRCode::first()->id);

        $this->assertDatabaseHas('qr_codes', [
            'registration_id' => $registration->id,
            'event_id' => $event->id,
            'status' => 'active',
        ]);
    }

    public function test_admin_can_reject_pending_registration_with_remarks(): void
    {
        [$admin, $student, $event] = $this->makePublishedEventContext();

        $registration = Registration::create([
            'event_id' => $event->id,
            'user_id' => $student->id,
            'status' => 'pending',
        ]);

        Sanctum::actingAs($admin);

        $response = $this->postJson("/api/events/{$event->id}/registrations/{$registration->id}/reject", [
            'remarks' => 'Requirements not complete',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.status', 'rejected')
            ->assertJsonPath('data.remarks', 'Requirements not complete');
    }

    public function test_cancelling_registration_revokes_existing_qr_code(): void
    {
        [$admin, $student, $event] = $this->makePublishedEventContext();

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
            'code' => 'ATD-CANCEL-001',
            'status' => 'active',
            'type' => 'attendance',
            'expires_at' => now()->addDay(),
        ]);

        Sanctum::actingAs($student);

        $response = $this->postJson("/api/events/{$event->id}/registrations/{$registration->id}/cancel");

        $response->assertOk()
            ->assertJsonPath('data.status', 'cancelled');

        $registration->refresh();
        $qrCode->refresh();

        $this->assertSame('cancelled', $registration->status);
        $this->assertSame('revoked', $qrCode->status);
    }

    public function test_registration_me_returns_authenticated_users_registration(): void
    {
        [$admin, $student, $event] = $this->makePublishedEventContext();

        $registration = Registration::create([
            'event_id' => $event->id,
            'user_id' => $student->id,
            'status' => 'approved',
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        Sanctum::actingAs($student);

        $this->getJson("/api/events/{$event->id}/registrations/me")
            ->assertOk()
            ->assertJsonPath('data.id', $registration->id)
            ->assertJsonPath('data.user_id', $student->id);
    }

    public function test_bulk_approve_only_approves_pending_registrations_for_the_event(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $studentA = User::factory()->create(['role' => 'student']);
        $studentB = User::factory()->create(['role' => 'student']);
        $studentC = User::factory()->create(['role' => 'student']);
        $event = $this->makeEvent($admin, 'Bulk Approval Event', 'published');
        $otherEvent = $this->makeEvent($admin, 'Other Bulk Event', 'published');

        $pendingOne = Registration::create([
            'event_id' => $event->id,
            'user_id' => $studentA->id,
            'status' => 'pending',
        ]);

        $pendingTwo = Registration::create([
            'event_id' => $event->id,
            'user_id' => $studentB->id,
            'status' => 'pending',
        ]);

        $otherEventRegistration = Registration::create([
            'event_id' => $otherEvent->id,
            'user_id' => $studentC->id,
            'status' => 'pending',
        ]);

        Sanctum::actingAs($admin);

        $response = $this->postJson("/api/events/{$event->id}/registrations/bulk-approve", [
            'registration_ids' => [
                $pendingOne->id,
                $pendingTwo->id,
                $otherEventRegistration->id,
            ],
            'remarks' => 'Approved in bulk',
        ]);

        $response->assertOk()
            ->assertJsonPath('approved_count', 2);

        $this->assertDatabaseHas('registrations', [
            'id' => $pendingOne->id,
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('registrations', [
            'id' => $pendingTwo->id,
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('registrations', [
            'id' => $otherEventRegistration->id,
            'status' => 'pending',
        ]);
    }

    public function test_student_cannot_register_with_mismatched_student_id(): void
    {
        [$admin, $student, $event] = $this->makePublishedEventContext();

        Sanctum::actingAs($student);

        $this->postJson("/api/events/{$event->id}/registrations", [
            'student_id' => 'WRONG-2026',
        ])->assertStatus(422)
            ->assertJsonPath('message', 'The provided student ID does not match your account.');
    }

    public function test_student_cannot_register_for_closed_event(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);
        $event = Event::create([
            'title' => 'Closed Event',
            'description' => 'Closed event description',
            'start_date' => now()->subDays(2),
            'end_date' => now()->subDay(),
            'location' => 'Main Hall',
            'max_participants' => 100,
            'status' => 'published',
            'created_by' => $admin->id,
        ]);

        Sanctum::actingAs($student);

        $this->postJson("/api/events/{$event->id}/registrations", [
            'student_id' => $student->student_id,
        ])->assertStatus(400)
            ->assertJsonPath('message', 'Event registration is already closed.');
    }

    private function makePublishedEventContext(): array
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);
        $event = $this->makeEvent($admin, 'Registration Event', 'published');

        return [$admin, $student, $event];
    }

    private function makeEvent(User $admin, string $title, string $status): Event
    {
        return Event::create([
            'title' => $title,
            'description' => $title.' description',
            'start_date' => now()->addDays(3),
            'end_date' => now()->addDays(3)->addHours(4),
            'location' => 'Main Hall',
            'max_participants' => 100,
            'status' => $status,
            'created_by' => $admin->id,
        ]);
    }
}
