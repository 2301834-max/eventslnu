<?php

namespace Database\Seeders;

use App\Models\AttendanceRecord;
use App\Models\Event;
use App\Models\QRCode;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // =====================
        // ADMIN
        // =====================
        $admin = User::firstOrCreate(
            ['email' => 'admin@lnu.edu.ph'],
            [
                'name' => 'LNU Admin',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'student_id' => null,
            ]
        );

        // =====================
        // DEMO USER
        // =====================
        User::firstOrCreate(
            ['email' => 'user@lnu.edu.ph'],
            [
                'name' => 'Demo Student',
                'password' => Hash::make('password123'),
                'role' => 'student',
                'student_id' => '2026-0001',
            ]
        );

        // =====================
        // STUDENTS
        // =====================
        $students = collect(range(1, 10))->map(function ($i) {
            return User::firstOrCreate(
                ['email' => sprintf('student%02d@lnu.edu.ph', $i)],
                [
                    'name' => "Student {$i}",
                    'password' => Hash::make('password123'),
                    'role' => 'student',
                    'student_id' => '2026-' . str_pad($i + 2, 4, '0', STR_PAD_LEFT),
                ]
            );
        })->values();

        // =====================
        // EVENTS
        // =====================
        $featuredEvent = Event::firstOrCreate(
            ['title' => 'Leadership Summit 2026'],
            [
                'description' => 'Flagship leadership program for student organizations.',
                'start_date' => now()->addDays(7)->setTime(9, 0),
                'end_date' => now()->addDays(7)->setTime(17, 0),
                'location' => 'Main Auditorium',
                'max_participants' => 300,
                'status' => 'published',
                'created_by' => $admin->id,
            ]
        );

        $this->seedRegistrationQr($featuredEvent);

        for ($i = 1; $i <= 300; $i++) {

            $event = Event::firstOrCreate(
                ['title' => "Event {$i}"],
                [
                    'description' => "Auto generated event {$i}",
                    'start_date' => now()->addDays($i % 30)->addHours(rand(1, 5)),
                    'end_date' => now()->addDays($i % 30)->addHours(rand(6, 10)),
                    'location' => "Campus Hall " . (($i % 20) + 1),
                    'max_participants' => rand(30, 300),
                    'status' => ['draft', 'published', 'ongoing', 'completed', 'cancelled'][$i % 5],
                    'created_by' => $admin->id,
                ]
            );

            // =====================
            // EVENT QR
            // =====================
            $this->seedRegistrationQr($event);

            // =====================
            // REGISTRATION (FIXED)
            // =====================
            $student = $students[$i % $students->count()];

            $registration = Registration::firstOrCreate(
                [
                    'event_id' => $event->id,
                    'user_id' => $student->id,
                ],
                [
                    'status' => 'approved',
                    'approved_by' => $admin->id,
                    'approved_at' => now(),
                    // ✅ GUARANTEED UNIQUE
                    'registration_number' => 'REG-' . Str::uuid(),
                ]
            );

            // =====================
            // ATTENDANCE QR
            // =====================
            $attendanceCode = "ATD|{$event->id}|{$registration->id}";

            $qr = QRCode::firstOrCreate(
                [
                    'registration_id' => $registration->id,
                    'type' => 'attendance',
                ],
                [
                    'event_id' => $event->id,
                    'code' => $attendanceCode,
                    'status' => 'active',
                    'expires_at' => now()->addDays(7),
                    'qr_image_data' => json_encode([
                        'type' => 'text',
                        'value' => $attendanceCode,
                    ]),
                ]
            );

            // =====================
            // ATTENDANCE RECORD
            // =====================
            AttendanceRecord::firstOrCreate(
                ['registration_id' => $registration->id],
                [
                    'event_id' => $event->id,
                    'user_id' => $student->id,
                    'checked_in_at' => now()->subMinutes(rand(10, 60)),
                    'checked_out_at' => now(),
                    'qr_code_reference' => $qr->code,
                    'check_in_location' => $event->location,
                ]
            );

            // =====================
            // UPDATE ATTENDEES COUNT
            // =====================
            $event->update([
                'current_attendees' =>
                    AttendanceRecord::where('event_id', $event->id)->count(),
            ]);
        }
    }

    // =====================
    // EVENT REGISTRATION QR
    // =====================
    private function seedRegistrationQr(Event $event): void
    {
        $code = "EVTREG|{$event->id}|" . Str::slug($event->title);

        QRCode::firstOrCreate(
            [
                'event_id' => $event->id,
                'registration_id' => null,
                'type' => 'event_registration',
            ],
            [
                'code' => $code,
                'status' => 'active',
                'expires_at' => now()->addDays(7),
                'qr_image_data' => json_encode([
                    'type' => 'text',
                    'value' => $code,
                ]),
            ]
        );
    }
}
