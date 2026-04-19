<?php

namespace Tests\Feature\Database;

use App\Models\AttendanceRecord;
use App\Models\Event;
use App\Models\QRCode;
use App\Models\Registration;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoDataSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_demo_ready_records(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertGreaterThanOrEqual(1, User::where('role', 'admin')->count());
        $this->assertGreaterThanOrEqual(10, User::where('role', 'student')->count());
        $this->assertGreaterThanOrEqual(10, Event::count());
        $this->assertGreaterThan(0, Registration::count());
        $this->assertGreaterThan(0, QRCode::count());
        $this->assertGreaterThan(0, AttendanceRecord::count());

        $this->assertDatabaseHas('users', ['email' => 'admin@lnu.edu.ph']);
        $this->assertDatabaseHas('users', ['email' => 'student01@lnu.edu.ph']);
        $this->assertDatabaseHas('events', ['title' => 'Leadership Summit 2026']);
        $this->assertDatabaseHas('qr_codes', ['type' => 'event_registration']);
    }
}
