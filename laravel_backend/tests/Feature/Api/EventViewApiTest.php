<?php

namespace Tests\Feature\Api;

use App\Models\AttendanceRecord;
use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EventViewApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_event_api_returns_event_details_and_statistics(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);
        $pendingStudent = User::factory()->create(['role' => 'student']);

        Sanctum::actingAs($student);

        $event = Event::create([
            'title' => 'Campus Leadership Summit',
            'organization' => 'Student Council',
            'description' => 'Leadership workshop for students.',
            'start_date' => now()->addDays(10),
            'end_date' => now()->addDays(10)->addHours(4),
            'location' => 'Main Auditorium',
            'max_participants' => 200,
            'status' => 'published',
            'created_by' => $admin->id,
        ]);

        $approvedRegistration = Registration::create([
            'event_id' => $event->id,
            'user_id' => $student->id,
            'status' => 'approved',
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        Registration::create([
            'event_id' => $event->id,
            'user_id' => $pendingStudent->id,
            'status' => 'pending',
        ]);

        AttendanceRecord::create([
            'registration_id' => $approvedRegistration->id,
            'event_id' => $event->id,
            'user_id' => $student->id,
            'checked_in_at' => now(),
            'qr_code_reference' => 'QR-LEADERSHIP-001',
            'check_in_location' => 'Main Auditorium',
        ]);

        $response = $this->getJson("/api/events/{$event->id}");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $event->id,
                    'title' => 'Campus Leadership Summit',
                    'organization' => 'Student Council',
                    'description' => 'Leadership workshop for students.',
                    'location' => 'Main Auditorium',
                    'max_participants' => 200,
                ],
                'statistics' => [
                    'total_registrations' => 2,
                    'approved_registrations' => 1,
                    'pending_registrations' => 1,
                    'total_attended' => 1,
                    'is_registration_full' => false,
                ],
            ])
            ->assertJsonPath('statistics.attendance_rate', '100%');
    }

    public function test_show_event_api_marks_event_as_full_when_capacity_is_reached(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);

        Sanctum::actingAs($student);

        $event = Event::create([
            'title' => 'Limited Event',
            'organization' => 'Room 101 Committee',
            'description' => 'Capacity is already full.',
            'start_date' => now()->addDays(5),
            'end_date' => now()->addDays(5)->addHours(2),
            'location' => 'Room 101',
            'max_participants' => 1,
            'status' => 'published',
            'created_by' => $admin->id,
        ]);

        Registration::create([
            'event_id' => $event->id,
            'user_id' => $student->id,
            'status' => 'approved',
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        $response = $this->getJson("/api/events/{$event->id}");

        $response->assertOk()
            ->assertJsonPath('statistics.is_registration_full', true);
    }
}
