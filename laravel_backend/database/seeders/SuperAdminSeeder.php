<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['username' => 'superadmin'],
            [
                'name' => 'Super Administrator',
                'email' => 'superadmin@lnusystem.local',
                'password' => Hash::make('superadmin123'),
                'role' => 'super_admin',
                'student_id' => null,
                'organization_type' => 'System',
                'organization_name' => 'LNU Smart Events',
                'is_active' => true,
            ]
        );
    }
}
