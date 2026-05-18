<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class GeneratedEventsSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first()
            ?? User::create([
                'name' => 'LNU Administrator',
                'email' => 'admin@lnusystem.local',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'student_id' => null,
            ]);

        $organizations = [
            'DIGITS',
            'Student Council',
            'Student Affairs Office',
            'LNU Athletics',
            'Engineering Society',
            'College of Arts and Sciences',
            'Campus Ministry',
            'Research and Innovation Office',
        ];

        $locations = [
            'HRDC',
            'Main Auditorium',
            'Campus Hall 1',
            'Campus Hall 2',
            'Library AVR',
            'Gymnasium',
            'Student Center',
            'ICT Laboratory',
        ];

        for ($i = 1; $i <= 200; $i++) {
            $start = now()
                ->addDays(($i % 60) + 1)
                ->setTime(8 + ($i % 9), ($i * 5) % 60);

            Event::updateOrCreate(
                ['title' => sprintf('Generated Event %03d', $i)],
                [
                    'organization' => $organizations[$i % count($organizations)],
                    'description' => sprintf('Generated demo event record %03d for local testing.', $i),
                    'start_date' => $start,
                    'end_date' => (clone $start)->addHours(2 + ($i % 4)),
                    'location' => $locations[$i % count($locations)],
                    'max_participants' => 50 + (($i * 25) % 450),
                    'status' => 'published',
                    'event_image' => null,
                    'created_by' => $admin->id,
                    'current_attendees' => 0,
                ]
            );
        }
    }
}
