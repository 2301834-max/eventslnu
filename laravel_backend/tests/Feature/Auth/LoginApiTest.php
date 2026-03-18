<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_login_returns_token_for_valid_credentials(): void
    {
        User::factory()->create([
            'name' => 'Login User',
            'email' => 'login@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/api/login', [
            'email' => 'login@example.com',
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJson([
                'message' => 'Login successful',
                'user' => [
                    'name' => 'Login User',
                    'email' => 'login@example.com',
                ],
            ])
            ->assertJsonStructure([
                'message',
                'token',
                'user' => ['id', 'name', 'email', 'created_at', 'updated_at'],
            ]);
    }

    public function test_api_login_returns_json_validation_errors_without_json_headers(): void
    {
        $response = $this->post('/api/login', [
            'email' => 'not-an-email',
            'password' => '123',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Validation failed',
            ])
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_api_login_rejects_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/api/login', [
            'email' => 'user@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Invalid credentials',
            ]);
    }
}
