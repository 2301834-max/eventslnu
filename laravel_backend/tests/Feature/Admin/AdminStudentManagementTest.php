<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminStudentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_student_index(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create([
            'role' => 'student',
            'name' => 'Student One',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.students.index'))
            ->assertOk()
            ->assertSee($student->name);
    }

    public function test_admin_can_store_student(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('admin.students.store'), [
            'name' => 'Created Student',
            'email' => '2302101@lnu.edu.ph',
            'student_id' => '2302101',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('admin.students.index'));

        $this->assertDatabaseHas('users', [
            'name' => 'Created Student',
            'email' => '2302101@lnu.edu.ph',
            'student_id' => '2302101',
            'role' => 'student',
        ]);
    }

    public function test_admin_can_store_student_with_valid_password(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.students.store'), [
            'name' => 'Short Password Student',
            'email' => '2302102@lnu.edu.ph',
            'student_id' => '2302102',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])->assertRedirect(route('admin.students.index'));

        $this->assertDatabaseHas('users', [
            'email' => '2302102@lnu.edu.ph',
            'student_id' => '2302102',
        ]);
    }

    public function test_admin_can_view_student_details(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create([
            'role' => 'student',
            'name' => 'Detail Student',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.students.show', $student))
            ->assertOk()
            ->assertSee('Detail Student');
    }

    public function test_non_student_user_returns_not_found_on_student_show(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $otherAdmin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.students.show', $otherAdmin))
            ->assertNotFound();
    }

    public function test_admin_can_update_student_without_changing_password(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create([
            'role' => 'student',
            'password' => Hash::make('password123'),
        ]);

        $this->actingAs($admin)->put(route('admin.students.update', $student), [
            'name' => 'Updated Student',
            'email' => $student->email,
            'student_id' => $student->student_id,
            'password' => '',
            'password_confirmation' => '',
        ])->assertRedirect(route('admin.students.index'));

        $student->refresh();

        $this->assertSame('Updated Student', $student->name);
        $this->assertTrue(Hash::check('password123', $student->password));
    }

    public function test_admin_can_update_student_password(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create([
            'role' => 'student',
            'password' => Hash::make('old-password'),
        ]);

        $this->actingAs($admin)->put(route('admin.students.update', $student), [
            'name' => $student->name,
            'email' => $student->email,
            'student_id' => $student->student_id,
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123',
        ])->assertRedirect(route('admin.students.index'));

        $student->refresh();

        $this->assertTrue(Hash::check('new-password123', $student->password));
    }

    public function test_admin_can_delete_student(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);

        $this->actingAs($admin)
            ->delete(route('admin.students.destroy', $student))
            ->assertRedirect(route('admin.students.index'));

        $this->assertDatabaseMissing('users', [
            'id' => $student->id,
        ]);
    }
}
