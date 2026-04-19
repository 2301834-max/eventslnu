<?php

namespace Tests\Feature\Api;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EventManagementApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_event_index_can_filter_by_status_and_search(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        Event::create([
            'title' => 'Published Leadership Forum',
            'description' => 'Published event',
            'start_date' => now()->addDays(4),
            'end_date' => now()->addDays(4)->addHours(4),
            'location' => 'Leadership Hall',
            'max_participants' => 100,
            'status' => 'published',
            'created_by' => $admin->id,
        ]);

        Event::create([
            'title' => 'Draft Workshop',
            'description' => 'Draft event',
            'start_date' => now()->addDays(5),
            'end_date' => now()->addDays(5)->addHours(2),
            'location' => 'Workshop Room',
            'max_participants' => 50,
            'status' => 'draft',
            'created_by' => $admin->id,
        ]);

        $response = $this->getJson('/api/events?status=published&search=Leadership');

        $response->assertOk()
            ->assertJsonPath('pagination.total', 1)
            ->assertJsonPath('data.0.title', 'Published Leadership Forum');
    }

    public function test_can_create_event_via_api_and_it_defaults_to_draft(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/events', [
            'title' => 'API Created Event',
            'description' => 'Event created in test',
            'start_date' => now()->addDays(3)->toDateTimeString(),
            'end_date' => now()->addDays(3)->addHours(3)->toDateTimeString(),
            'location' => 'Creation Hall',
            'max_participants' => 120,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.status', 'draft')
            ->assertJsonPath('data.created_by', $admin->id);
    }

    public function test_publish_changes_draft_event_to_published(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $event = $this->makeEvent($admin, 'Publishable Event', 'draft');

        Sanctum::actingAs($admin);

        $this->postJson("/api/events/{$event->id}/publish")
            ->assertOk()
            ->assertJsonPath('data.status', 'published');
    }

    public function test_cannot_publish_non_draft_event(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $event = $this->makeEvent($admin, 'Already Published Event', 'published');

        Sanctum::actingAs($admin);

        $this->postJson("/api/events/{$event->id}/publish")
            ->assertStatus(400)
            ->assertJsonPath('message', 'Only draft events can be published');
    }

    public function test_start_rejects_completed_event(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $event = $this->makeEvent($admin, 'Completed Event', 'completed');

        Sanctum::actingAs($admin);

        $this->postJson("/api/events/{$event->id}/start")
            ->assertStatus(400)
            ->assertJsonPath('message', 'Event cannot be started');
    }

    public function test_cancel_changes_event_status_to_cancelled(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $event = $this->makeEvent($admin, 'Cancelable Event', 'published');

        Sanctum::actingAs($admin);

        $this->postJson("/api/events/{$event->id}/cancel", [
            'reason' => 'Venue unavailable',
        ])->assertOk()
            ->assertJsonPath('data.status', 'cancelled');
    }

    public function test_event_index_search_does_not_leak_results_outside_status_filter(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        Event::create([
            'title' => 'Published Summit',
            'description' => 'published event',
            'start_date' => now()->addDays(5),
            'end_date' => now()->addDays(5)->addHours(2),
            'location' => 'Alpha Center',
            'max_participants' => 70,
            'status' => 'published',
            'created_by' => $admin->id,
        ]);

        Event::create([
            'title' => 'Draft Location Match',
            'description' => 'draft event',
            'start_date' => now()->addDays(6),
            'end_date' => now()->addDays(6)->addHours(2),
            'location' => 'Alpha Center',
            'max_participants' => 70,
            'status' => 'draft',
            'created_by' => $admin->id,
        ]);

        $response = $this->getJson('/api/events?status=published&search=Alpha');

        $response->assertOk()
            ->assertJsonPath('pagination.total', 1)
            ->assertJsonPath('data.0.status', 'published');
    }

    public function test_can_update_event_via_api(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $event = $this->makeEvent($admin, 'Updatable Event', 'draft');

        Sanctum::actingAs($admin);

        $this->putJson("/api/events/{$event->id}", [
            'title' => 'Updated Event Title',
            'location' => 'Updated Hall',
            'status' => 'published',
        ])->assertOk()
            ->assertJsonPath('data.title', 'Updated Event Title')
            ->assertJsonPath('data.location', 'Updated Hall')
            ->assertJsonPath('data.status', 'published');
    }

    public function test_can_end_event_via_api(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $event = $this->makeEvent($admin, 'Ending Event', 'ongoing');

        Sanctum::actingAs($admin);

        $this->postJson("/api/events/{$event->id}/end")
            ->assertOk()
            ->assertJsonPath('data.status', 'completed');
    }

    public function test_can_delete_event_via_api(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $event = $this->makeEvent($admin, 'Delete Event', 'draft');

        Sanctum::actingAs($admin);

        $this->deleteJson("/api/events/{$event->id}")
            ->assertOk()
            ->assertJsonPath('message', 'Event deleted successfully');

        $this->assertSoftDeleted('events', [
            'id' => $event->id,
        ]);
    }

    private function makeEvent(User $admin, string $title, string $status): Event
    {
        return Event::create([
            'title' => $title,
            'description' => $title . ' description',
            'start_date' => now()->addDays(2),
            'end_date' => now()->addDays(2)->addHours(4),
            'location' => 'API Event Hall',
            'max_participants' => 100,
            'status' => $status,
            'created_by' => $admin->id,
        ]);
    }
}
