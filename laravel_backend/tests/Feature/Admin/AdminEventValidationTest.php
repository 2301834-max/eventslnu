<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminEventValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_cannot_create_event_with_past_start_date(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.events.store'), [
            'title' => 'Past Event',
            'description' => 'This event should fail validation.',
            'start_date' => now()->subDay()->format('Y-m-d H:i:s'),
            'end_date' => now()->addDay()->format('Y-m-d H:i:s'),
            'location' => 'Legacy Hall',
            'max_participants' => 50,
            'status' => 'published',
        ]);

        $response->assertSessionHasErrors('start_date');
    }
}
