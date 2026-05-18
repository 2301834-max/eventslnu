<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function test_is_admin_returns_true_only_for_admin_role(): void
    {
        $admin = new User(['role' => 'admin']);
        $student = new User(['role' => 'student']);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($student->isAdmin());
    }

    public function test_is_student_returns_true_only_for_student_role(): void
    {
        $student = new User(['role' => 'student']);
        $admin = new User(['role' => 'admin']);

        $this->assertTrue($student->isStudent());
        $this->assertFalse($admin->isStudent());
    }

    public function test_password_and_remember_token_are_hidden_in_array_output(): void
    {
        $user = new User([
            'name' => 'Hidden Fields User',
            'email' => 'hidden@example.com',
            'password' => 'secret',
            'remember_token' => 'token',
        ]);

        $payload = $user->toArray();

        $this->assertArrayNotHasKey('password', $payload);
        $this->assertArrayNotHasKey('remember_token', $payload);
    }

    public function test_role_helpers_return_false_when_role_is_missing(): void
    {
        $user = new User;

        $this->assertFalse($user->isAdmin());
        $this->assertFalse($user->isStudent());
    }
}
