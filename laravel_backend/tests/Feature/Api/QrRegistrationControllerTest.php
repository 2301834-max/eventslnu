<?php

namespace Tests\Feature\Api;

use App\Models\Event;
use App\Models\QRCode;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class QrRegistrationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_scan_registers_event_and_generates_attendance_qr(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);

        $event = Event::create([
            'title' => 'Innovation Forum',
            'description' => 'Campus innovation forum',
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(2),
            'location' => 'Innovation Hall',
            'max_participants' => 120,
            'status' => 'published',
            'created_by' => $admin->id,
        ]);

        QRCode::create([
            'registration_id' => null,
            'event_id' => $event->id,
            'code' => 'EVTREG|'.$event->id.'|abc123',
            'status' => 'active',
            'type' => 'event_registration',
            'expires_at' => now()->addDays(7),
        ]);

        Sanctum::actingAs($student);

        $response = $this->postJson('/api/qr/register', [
            'qr_code' => 'EVTREG|'.$event->id.'|abc123',
        ]);

        $response->assertCreated()
            ->assertJson([
                'success' => true,
                'already_registered' => false,
            ]);

        $registration = Registration::where('event_id', $event->id)
            ->where('user_id', $student->id)
            ->firstOrFail();

        $this->assertSame('approved', $registration->status);
        $this->assertDatabaseHas('qr_codes', [
            'registration_id' => $registration->id,
            'event_id' => $event->id,
            'type' => 'attendance',
            'status' => 'active',
        ]);
    }

    public function test_student_scan_returns_already_registered_and_recovers_missing_attendance_qr(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);

        $event = Event::create([
            'title' => 'Leadership Congress',
            'description' => 'Leadership gathering',
            'start_date' => now()->addDays(3),
            'end_date' => now()->addDays(4),
            'location' => 'Grand Theater',
            'max_participants' => 150,
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

        QRCode::create([
            'registration_id' => null,
            'event_id' => $event->id,
            'code' => 'EVTREG|'.$event->id.'|xyz789',
            'status' => 'active',
            'type' => 'event_registration',
            'expires_at' => now()->addDays(7),
        ]);

        Sanctum::actingAs($student);

        $response = $this->postJson('/api/qr/register', [
            'qr_code' => 'EVTREG|'.$event->id.'|xyz789',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'already_registered' => true,
            ]);

        $this->assertDatabaseHas('qr_codes', [
            'registration_id' => $registration->id,
            'event_id' => $event->id,
            'type' => 'attendance',
            'status' => 'active',
        ]);
    }

    public function test_student_scan_instantly_approves_existing_pending_registration(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);

        $event = Event::create([
            'title' => 'Campus Assembly',
            'description' => 'Pending registration recovery',
            'start_date' => now()->addDays(2),
            'end_date' => now()->addDays(2)->addHours(4),
            'location' => 'Main Gym',
            'max_participants' => 250,
            'status' => 'published',
            'created_by' => $admin->id,
        ]);

        $registration = Registration::create([
            'event_id' => $event->id,
            'user_id' => $student->id,
            'status' => 'pending',
        ]);

        QRCode::create([
            'registration_id' => null,
            'event_id' => $event->id,
            'code' => 'EVTREG|'.$event->id.'|pending001',
            'status' => 'active',
            'type' => 'event_registration',
            'expires_at' => now()->addDays(7),
        ]);

        Sanctum::actingAs($student);

        $this->postJson('/api/qr/register', [
            'qr_code' => 'EVTREG|'.$event->id.'|pending001',
        ])->assertOk()
            ->assertJson([
                'success' => true,
                'already_registered' => false,
            ])
            ->assertJsonPath('data.status', 'approved');

        $registration->refresh();

        $this->assertSame('approved', $registration->status);
        $this->assertNotNull($registration->approved_at);
        $this->assertSame($admin->id, $registration->approved_by);
        $this->assertDatabaseHas('qr_codes', [
            'registration_id' => $registration->id,
            'event_id' => $event->id,
            'type' => 'attendance',
            'status' => 'active',
        ]);
    }

    public function test_student_scan_rejects_full_event_registration_qr(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $studentA = User::factory()->create(['role' => 'student']);
        $studentB = User::factory()->create(['role' => 'student']);

        $event = Event::create([
            'title' => 'Limited Forum',
            'description' => 'Capacity one only',
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(2),
            'location' => 'Mini Hall',
            'max_participants' => 1,
            'status' => 'published',
            'created_by' => $admin->id,
        ]);

        Registration::create([
            'event_id' => $event->id,
            'user_id' => $studentA->id,
            'status' => 'approved',
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        QRCode::create([
            'registration_id' => null,
            'event_id' => $event->id,
            'code' => 'EVTREG|'.$event->id.'|full001',
            'status' => 'active',
            'type' => 'event_registration',
            'expires_at' => now()->addDays(7),
        ]);

        Sanctum::actingAs($studentB);

        $this->postJson('/api/qr/register', [
            'qr_code' => 'EVTREG|'.$event->id.'|full001',
        ])->assertStatus(400)
            ->assertJsonPath('message', 'Event registration is full.');
    }

    public function test_student_scan_rejects_draft_event_registration_qr(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);

        $event = Event::create([
            'title' => 'Draft Forum',
            'description' => 'Draft event',
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(2),
            'location' => 'Closed Hall',
            'max_participants' => 20,
            'status' => 'draft',
            'created_by' => $admin->id,
        ]);

        QRCode::create([
            'registration_id' => null,
            'event_id' => $event->id,
            'code' => 'EVTREG|'.$event->id.'|draft001',
            'status' => 'active',
            'type' => 'event_registration',
            'expires_at' => now()->addDays(7),
        ]);

        Sanctum::actingAs($student);

        $this->postJson('/api/qr/register', [
            'qr_code' => 'EVTREG|'.$event->id.'|draft001',
        ])->assertStatus(400)
            ->assertJsonPath('message', 'Event is not open for registration.');
    }

    public function test_student_scan_rejects_qr_when_event_is_missing(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);
        $event = Event::create([
            'title' => 'Deleted Event',
            'description' => 'Soft deleted event',
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(2),
            'location' => 'Removed Hall',
            'max_participants' => 20,
            'status' => 'published',
            'created_by' => $admin->id,
        ]);

        QRCode::create([
            'registration_id' => null,
            'event_id' => $event->id,
            'code' => 'EVTREG|'.$event->id.'|missing001',
            'status' => 'active',
            'type' => 'event_registration',
            'expires_at' => now()->addDays(7),
        ]);

        $event->delete();

        Sanctum::actingAs($student);

        $this->postJson('/api/qr/register', [
            'qr_code' => 'EVTREG|'.$event->id.'|missing001',
        ])->assertStatus(404)
            ->assertJsonPath('message', 'Event not found.');
    }
}
