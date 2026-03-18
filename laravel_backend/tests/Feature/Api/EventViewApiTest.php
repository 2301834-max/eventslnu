<?php

namespace Tests\Feature\Api;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EventViewApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_view_event_api_returns_full_event_details_and_register_next_action(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $event = Event::create([
            'title' => 'Campus Leadership Summit',
            'description' => 'Leadership workshop for students.',
            'start_date' => now()->addDays(10),
            'end_date' => now()->addDays(10)->addHours(4),
            'location' => 'Main Auditorium',
            'max_participants' => 200,
            'status' => 'published',
            'created_by' => $user->id,
        ]);

        $response = $this->getJson("/api/events/{$event->id}/view");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $event->id,
                    'title' => 'Campus Leadership Summit',
                    'venue' => 'Main Auditorium',
                    'description' => 'Leadership workshop for students.',
                    'max_participants' => 200,
                ],
                'next_action' => [
                    'can_register' => true,
                    'method' => 'POST',
                ],
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'title',
                    'date',
                    'venue',
                    'description',
                    'capacity',
                    'poster',
                    'registration_deadline',
                    'max_participants',
                    'status',
                    'available_slots',
                ],
                'next_action' => [
                    'can_register',
                    'endpoint',
                    'method',
                ],
            ]);
    }

    public function test_view_event_api_sets_can_register_false_for_draft_event(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $event = Event::create([
            'title' => 'Draft Event',
            'description' => 'Not open yet.',
            'start_date' => now()->addDays(5),
            'end_date' => now()->addDays(5)->addHours(2),
            'location' => 'Room 101',
            'max_participants' => 50,
            'status' => 'draft',
            'created_by' => $user->id,
        ]);

        $response = $this->getJson("/api/events/{$event->id}/view");

        $response->assertOk()
            ->assertJsonPath('next_action.can_register', false);
    }
}
