<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class WebAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_web_registration_creates_user_and_redirects_to_dashboard(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Web Register User',
            'email' => 'web-register@lnu.edu.ph',
            'student_id' => '2026-1201',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'web-register@lnu.edu.ph',
            'student_id' => '2026-1201',
        ]);
    }

    public function test_login_page_redirects_to_admin_login(): void
    {
        $this->get(route('login'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_legacy_login_route_redirects_admin_to_admin_dashboard_and_sets_api_token(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'legacy-admin@gmail.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post(route('login'), [
            'email' => $admin->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHas('api_token');
        $this->assertAuthenticatedAs($admin);
    }

    public function test_admin_login_redirects_admin_to_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin.personal@gmail.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post(route('admin.login'), [
            'email' => $admin->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHas('api_token');
        $this->assertAuthenticatedAs($admin);
    }

    public function test_legacy_login_route_rejects_student_account(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'password' => Hash::make('password123'),
        ]);

        $this->from(route('login'))
            ->post(route('login'), [
                'email' => $student->email,
                'password' => 'password123',
            ])
            ->assertRedirect(route('admin.login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_admin_login_rejects_student_account(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'password' => Hash::make('password123'),
        ]);

        $this->from(route('admin.login'))
            ->post(route('admin.login'), [
                'email' => $student->email,
                'password' => 'password123',
            ])
            ->assertRedirect(route('admin.login'))
            ->assertSessionHasErrors('email');
    }

    public function test_admin_login_rejects_invalid_credentials(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin.personal@gmail.com',
            'password' => Hash::make('password123'),
        ]);

        $this->from(route('admin.login'))
            ->post(route('admin.login'), [
                'email' => $admin->email,
                'password' => 'wrong-password',
            ])
            ->assertRedirect(route('admin.login'))
            ->assertSessionHasErrors('email');
    }

    public function test_authenticated_user_can_logout_from_web_session(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect('/');
        $this->assertGuest();
    }
}
