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
        // Create admin user
        User::firstOrCreate(
            ['email' => 'admin@lnusystem.local'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );

        // Create test user
        User::firstOrCreate(
            ['email' => 'user@lnusystem.local'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password123'),
                'role' => 'student',
            ]
        );
    }
}
