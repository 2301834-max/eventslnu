<?php

namespace Tests\Feature\Api;

use App\Models\Event;
use App\Models\QRCode;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class QRCodeGenerationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_generate_qr_code_for_approved_registration(): void
    {
        [$admin, $event, $registration] = $this->makeApprovedRegistrationContext();

        Sanctum::actingAs($admin);

        $response = $this->postJson("/api/events/{$event->id}/qr/registrations/{$registration->id}", [
            'ttl_minutes' => 15,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.registration_id', $registration->id)
            ->assertJsonPath('data.event_id', $event->id)
            ->assertJsonPath('data.ttl_minutes', 15);

        $this->assertDatabaseHas('qr_codes', [
            'registration_id' => $registration->id,
            'event_id' => $event->id,
            'status' => 'active',
        ]);
    }

    public function test_cannot_generate_qr_for_non_approved_registration(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);
        $event = $this->makeEvent($admin, 'QR Non Approved Event');
        $registration = Registration::create([
            'event_id' => $event->id,
            'user_id' => $student->id,
            'status' => 'pending',
        ]);

        Sanctum::actingAs($admin);

        $this->postJson("/api/events/{$event->id}/qr/registrations/{$registration->id}")
            ->assertStatus(400)
            ->assertJsonPath('message', 'QR codes can only be generated for approved registrations.');
    }

    public function test_cannot_generate_qr_for_registration_from_another_event(): void
    {
        [$admin, $event, $registration] = $this->makeApprovedRegistrationContext();
        $otherEvent = $this->makeEvent($admin, 'Other QR Event');

        Sanctum::actingAs($admin);

        $this->postJson("/api/events/{$otherEvent->id}/qr/registrations/{$registration->id}")
            ->assertStatus(404)
            ->assertJsonPath('message', 'Registration does not belong to this event.');
    }

    public function test_generating_new_qr_revokes_existing_active_qr(): void
    {
        [$admin, $event, $registration] = $this->makeApprovedRegistrationContext();

        QRCode::create([
            'registration_id' => $registration->id,
            'event_id' => $event->id,
            'code' => 'OLD-QR-CODE',
            'status' => 'active',
            'type' => 'attendance',
            'expires_at' => now()->addMinutes(5),
        ]);

        Sanctum::actingAs($admin);

        $this->postJson("/api/events/{$event->id}/qr/registrations/{$registration->id}")
            ->assertCreated();

        $this->assertDatabaseHas('qr_codes', [
            'code' => 'OLD-QR-CODE',
            'status' => 'revoked',
        ]);

        $this->assertSame(2, QRCode::count());
    }

    private function makeApprovedRegistrationContext(): array
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);
        $event = $this->makeEvent($admin, 'QR Generation Event');

        $registration = Registration::create([
            'event_id' => $event->id,
            'user_id' => $student->id,
            'status' => 'approved',
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        return [$admin, $event, $registration];
    }

    private function makeEvent(User $admin, string $title): Event
    {
        return Event::create([
            'title' => $title,
            'description' => $title.' description',
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(2),
            'location' => 'QR Hall',
            'max_participants' => 80,
            'status' => 'published',
            'created_by' => $admin->id,
        ]);
    }
}
