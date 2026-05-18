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

    public function test_is_super_admin_returns_true_only_for_super_admin_role(): void
    {
        $superAdmin = new User(['role' => 'super_admin']);
        $admin = new User(['role' => 'admin']);

        $this->assertTrue($superAdmin->isSuperAdmin());
        $this->assertFalse($admin->isSuperAdmin());
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
        $this->assertFalse($user->isSuperAdmin());
        $this->assertFalse($user->isStudent());
    }

    public function test_institutional_email_requires_numeric_lnu_email(): void
    {
        $this->assertTrue(User::isInstitutionalEmail('2301360@lnu.edu.ph'));
        $this->assertTrue(User::isInstitutionalEmail('2301360@LNU.EDU.PH'));

        $this->assertFalse(User::isInstitutionalEmail('student@lnu.edu.ph'));
        $this->assertFalse(User::isInstitutionalEmail('2301360@gmail.com'));
        $this->assertFalse(User::isInstitutionalEmail(null));
    }

    public function test_has_institutional_email_uses_email_attribute(): void
    {
        $student = new User(['email' => '2301360@lnu.edu.ph']);
        $invalid = new User(['email' => 'student@lnu.edu.ph']);

        $this->assertTrue($student->hasInstitutionalEmail());
        $this->assertFalse($invalid->hasInstitutionalEmail());
    }
}
