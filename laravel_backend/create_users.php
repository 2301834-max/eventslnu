<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

User::firstOrCreate(
    ['email' => 'admin@lnusystem.local'],
    ['name' => 'Admin', 'password' => Hash::make('password123'), 'role' => 'admin']
);

User::firstOrCreate(
    ['email' => 'user@lnusystem.local'],
    ['name' => 'Test User', 'password' => Hash::make('password123'), 'role' => 'student']
);

echo "Users created successfully!\n";
