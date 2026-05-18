<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_register_creates_user_and_returns_token(): void
    {
        $response = $this->post('/api/register', [
            'name' => 'Test User',
            'email' => '2301101@lnu.edu.ph',
            'student_id' => '2301101',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'User registered successfully',
                'user' => [
                    'name' => 'Test User',
                    'email' => '2301101@lnu.edu.ph',
                    'student_id' => '2301101',
                ],
            ])
            ->assertJsonStructure([
                'message',
                'token',
                'user' => ['id', 'name', 'email', 'student_id', 'created_at', 'updated_at'],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => '2301101@lnu.edu.ph',
            'student_id' => '2301101',
        ]);
    }

    public function test_api_register_accepts_common_mobile_field_names(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Mobile User',
            'email' => '2301103@LNU.EDU.PH',
            'studentId' => '2301103',
            'password' => 'secret123',
            'confirmPassword' => 'secret123',
        ]);

        $response->assertCreated()
            ->assertJsonPath('user.email', '2301103@lnu.edu.ph')
            ->assertJsonPath('user.student_id', '2301103');

        $this->assertDatabaseHas('users', [
            'email' => '2301103@lnu.edu.ph',
            'student_id' => '2301103',
        ]);
    }

    public function test_api_register_returns_json_validation_errors_without_json_headers(): void
    {
        $response = $this->post('/api/register', [
            'name' => '',
            'email' => 'not-an-email',
            'student_id' => '',
            'password' => '123',
            'password_confirmation' => '456',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Validation failed',
            ])
            ->assertJsonValidationErrors(['name', 'email', 'student_id', 'password']);
    }

    public function test_api_register_rejects_duplicate_email(): void
    {
        User::factory()->create([
            'email' => '2301104@lnu.edu.ph',
        ]);

        $response = $this->post('/api/register', [
            'name' => 'Another User',
            'email' => '2301104@lnu.edu.ph',
            'student_id' => '2301102',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_api_register_rejects_non_institutional_email_and_missing_student_id(): void
    {
        $response = $this->post('/api/register', [
            'name' => 'Another User',
            'email' => 'duplicate@gmail.com',
            'student_id' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'student_id'])
            ->assertJsonPath('errors.email.0', 'Email must use your student number with @lnu.edu.ph, for example 2301360@lnu.edu.ph.');
    }
}
