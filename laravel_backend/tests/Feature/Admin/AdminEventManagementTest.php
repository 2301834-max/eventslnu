<?php

namespace Tests\Feature\Admin;

use App\Models\Event;
use App\Models\QRCode;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminEventManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_event_index_with_summary(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Event::create($this->eventData($admin, 'Upcoming Event', 'published', now()->addDay(), now()->addDays(2)));
        Event::create($this->eventData($admin, 'Completed Event', 'completed', now()->subDays(2), now()->subDay()));

        $this->actingAs($admin)
            ->get(route('admin.events.index'))
            ->assertOk()
            ->assertSee('Upcoming Event')
            ->assertSee('Completed Event');
    }

    public function test_admin_can_view_event_details_with_registration_qr_section(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);
        $event = Event::create($this->eventData($admin, 'Showcase Event', 'published'));

        Registration::create([
            'event_id' => $event->id,
            'user_id' => $student->id,
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.events.show', $event))
            ->assertOk()
            ->assertSee('Event Registration QR')
            ->assertSee('Showcase Event');
    }

    public function test_event_index_only_shows_edit_and_delete_for_draft_and_published_events(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $draft = Event::create($this->eventData($admin, 'Draft Action Event', 'draft'));
        $published = Event::create($this->eventData($admin, 'Published Action Event', 'published'));
        $ongoing = Event::create($this->eventData($admin, 'Ongoing View Only Event', 'ongoing'));
        $completed = Event::create($this->eventData($admin, 'Completed View Only Event', 'completed'));
        $cancelled = Event::create($this->eventData($admin, 'Cancelled View Only Event', 'cancelled'));

        $response = $this->actingAs($admin)
            ->get(route('admin.events.index'))
            ->assertOk();

        $response
            ->assertSee(route('admin.events.edit', $draft), false)
            ->assertSee('<form method="POST" action="' . route('admin.events.destroy', $draft) . '"', false)
            ->assertSee(route('admin.events.edit', $published), false)
            ->assertSee('<form method="POST" action="' . route('admin.events.destroy', $published) . '"', false)
            ->assertDontSee(route('admin.events.edit', $ongoing), false)
            ->assertDontSee('<form method="POST" action="' . route('admin.events.destroy', $ongoing) . '"', false)
            ->assertDontSee(route('admin.events.edit', $completed), false)
            ->assertDontSee('<form method="POST" action="' . route('admin.events.destroy', $completed) . '"', false)
            ->assertDontSee(route('admin.events.edit', $cancelled), false)
            ->assertDontSee('<form method="POST" action="' . route('admin.events.destroy', $cancelled) . '"', false);
    }

    public function test_admin_cannot_edit_update_or_delete_view_only_event_statuses(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        foreach (['ongoing', 'completed', 'cancelled'] as $status) {
            $event = Event::create($this->eventData($admin, ucfirst($status) . ' Locked Event', $status));

            $this->actingAs($admin)
                ->get(route('admin.events.edit', $event))
                ->assertRedirect(route('admin.events.show', $event));

            $this->actingAs($admin)
                ->put(route('admin.events.update', $event), array_merge(
                    $this->eventData($admin, 'Updated ' . $status, 'published'),
                    [
                        'start_date' => now()->addDays(4)->format('Y-m-d H:i:s'),
                        'end_date' => now()->addDays(5)->format('Y-m-d H:i:s'),
                    ]
                ))
                ->assertRedirect(route('admin.events.show', $event));

            $event->refresh();
            $this->assertSame(ucfirst($status) . ' Locked Event', $event->title);
            $this->assertSame($status, $event->status);

            $this->actingAs($admin)
                ->delete(route('admin.events.destroy', $event))
                ->assertRedirect(route('admin.events.show', $event));

            $this->assertNotSoftDeleted('events', [
                'id' => $event->id,
            ]);
        }
    }

    public function test_admin_can_approve_registration_from_web_panel(): void
    {
        [$admin, $registration] = $this->makeRegistrationContext('pending');

        $this->actingAs($admin)
            ->put(route('admin.registrations.approve', $registration))
            ->assertRedirect();

        $registration->refresh();

        $this->assertSame('approved', $registration->status);
        $this->assertSame($admin->id, $registration->approved_by);
    }

    public function test_admin_can_reject_registration_from_web_panel(): void
    {
        [$admin, $registration] = $this->makeRegistrationContext('pending');

        $this->actingAs($admin)
            ->put(route('admin.registrations.reject', $registration))
            ->assertRedirect();

        $registration->refresh();

        $this->assertSame('rejected', $registration->status);
        $this->assertSame($admin->id, $registration->approved_by);
    }

    public function test_admin_can_delete_registration_from_web_panel(): void
    {
        [$admin, $registration] = $this->makeRegistrationContext('pending');

        $this->actingAs($admin)
            ->delete(route('admin.registrations.destroy', $registration))
            ->assertRedirect();

        $this->assertSoftDeleted('registrations', [
            'id' => $registration->id,
        ]);
    }

    public function test_admin_can_generate_event_registration_qr_and_revoke_previous_one(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $event = Event::create($this->eventData($admin, 'QR Event', 'published'));

        QRCode::create([
            'registration_id' => null,
            'event_id' => $event->id,
            'code' => 'EVTREG|'.$event->id.'|old-code',
            'status' => 'active',
            'type' => 'event_registration',
            'expires_at' => now()->addDay(),
        ]);

        $this->actingAs($admin)
            ->post(route('admin.events.registration-qr.generate', $event))
            ->assertRedirect(route('admin.events.show', $event));

        $this->assertDatabaseHas('qr_codes', [
            'event_id' => $event->id,
            'type' => 'event_registration',
            'status' => 'revoked',
            'code' => 'EVTREG|'.$event->id.'|old-code',
        ]);

        $newQr = QRCode::where('event_id', $event->id)
            ->where('type', 'event_registration')
            ->where('status', 'active')
            ->first();

        $this->assertNotNull($newQr);
        $this->assertStringStartsWith('EVTREG|'.$event->id.'|', $newQr->code);
    }

    private function makeRegistrationContext(string $status): array
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);
        $event = Event::create($this->eventData($admin, 'Registration Admin Event', 'published'));

        $registration = Registration::create([
            'event_id' => $event->id,
            'user_id' => $student->id,
            'status' => $status,
        ]);

        return [$admin, $registration];
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
            'organization' => 'Student Affairs Office',
            'description' => $title . ' description',
            'start_date' => $start ?? now()->addDays(2),
            'end_date' => $end ?? now()->addDays(3),
            'location' => 'Admin Event Hall',
            'max_participants' => 100,
            'status' => $status,
            'created_by' => $admin->id,
        ];
    }
}
