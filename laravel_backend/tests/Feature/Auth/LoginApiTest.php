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
            'email' => 'login@lnu.edu.ph',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/api/login', [
            'email' => 'login@lnu.edu.ph',
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJson([
                'message' => 'Login successful',
                'user' => [
                    'name' => 'Login User',
                    'email' => 'login@lnu.edu.ph',
                ],
            ])
            ->assertJsonStructure([
                'message',
                'token',
                'user' => ['id', 'name', 'email', 'student_id', 'created_at', 'updated_at'],
            ]);
    }

    public function test_api_login_returns_json_validation_errors_for_missing_or_short_credentials(): void
    {
        $response = $this->post('/api/login', [
            'email' => '',
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
            'email' => 'user@lnu.edu.ph',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/api/login', [
            'email' => 'user@lnu.edu.ph',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Invalid credentials',
            ]);
    }

    public function test_api_login_rejects_non_institutional_email_addresses(): void
    {
        $response = $this->post('/api/login', [
            'email' => 'user@gmail.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email'])
            ->assertJsonPath('errors.email.0', 'Please use your institutional email ending in @lnu.edu.ph.');
    }
}
