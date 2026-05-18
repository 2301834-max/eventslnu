<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Keep a predictable admin account available for local login.
        User::updateOrCreate(
            ['email' => 'admin@lnusystem.local'],
            [
                'name' => 'LNU Administrator',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'student_id' => null,
                'username' => 'admin',
                'organization_type' => 'University Office',
                'organization_name' => 'Leyte Normal University',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'user@lnusystem.local'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password123'),
                'role' => 'student',
                'student_id' => 'LOCAL-0001',
                'is_active' => true,
            ]
        );
    }
}
