<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProfileApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_fetch_profile_data(): void
    {
        $student = User::factory()->create([
            'name' => 'Profile Student',
            'email' => 'profile.student@lnu.edu.ph',
            'student_id' => '2026-3301',
            'role' => 'student',
        ]);

        Sanctum::actingAs($student);

        $this->getJson('/api/profile')
            ->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Profile Student',
                    'email' => 'profile.student@lnu.edu.ph',
                    'student_id' => '2026-3301',
                    'role' => 'student',
                ],
            ]);
    }
}
