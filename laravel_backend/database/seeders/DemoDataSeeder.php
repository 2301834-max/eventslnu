<?php

namespace Database\Seeders;

use App\Models\AttendanceRecord;
use App\Models\Event;
use App\Models\QRCode;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@lnu.edu.ph'],
            [
                'name' => 'LNU Admin',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'student_id' => null,
            ]
        );

        User::updateOrCreate(
            ['email' => 'user@lnu.edu.ph'],
            [
                'name' => 'Demo Student',
                'password' => Hash::make('password123'),
                'role' => 'student',
                'student_id' => '2026-0001',
            ]
        );

        $students = collect(range(1, 10))
            ->map(function (int $number) {
                return User::updateOrCreate(
                    ['email' => sprintf('student%02d@lnu.edu.ph', $number)],
                    [
                        'name' => sprintf('Student Demo %02d', $number),
                        'password' => Hash::make('password123'),
                        'role' => 'student',
                        'student_id' => sprintf('2026-%04d', $number + 1),
                    ]
                );
            })
            ->prepend(User::where('email', 'user@lnu.edu.ph')->firstOrFail())
            ->values();

        $events = collect($this->eventDefinitions())
            ->map(function (array $definition) use ($admin) {
                return Event::updateOrCreate(
                    ['title' => $definition['title']],
                    $definition + ['created_by' => $admin->id]
                );
            });

        foreach ($events as $index => $event) {
            $this->seedRegistrationQr($event);
            $this->seedRegistrationsForEvent($event, $students, $admin, $index);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function eventDefinitions(): array
    {
        return [
            [
                'title' => 'Leadership Summit 2026',
                'description' => 'A campus-wide summit for student leaders and organization officers.',
                'start_date' => now()->addDays(2)->setTime(9, 0),
                'end_date' => now()->addDays(2)->setTime(16, 0),
                'location' => 'Main Auditorium',
                'max_participants' => 150,
                'status' => 'published',
            ],
            [
                'title' => 'Research Colloquium',
                'description' => 'Presentation of undergraduate and graduate research work.',
                'start_date' => now()->addDays(4)->setTime(8, 30),
                'end_date' => now()->addDays(4)->setTime(15, 30),
                'location' => 'Science Hall',
                'max_participants' => 120,
                'status' => 'published',
            ],
            [
                'title' => 'Innovation Bootcamp',
                'description' => 'Hands-on workshop for product ideas and student innovation teams.',
                'start_date' => now()->subHour(),
                'end_date' => now()->addHours(5),
                'location' => 'Innovation Lab',
                'max_participants' => 80,
                'status' => 'ongoing',
            ],
            [
                'title' => 'Career Fair Expo',
                'description' => 'Industry networking and recruitment opportunities for students.',
                'start_date' => now()->subDays(5)->setTime(9, 0),
                'end_date' => now()->subDays(5)->setTime(17, 0),
                'location' => 'University Gym',
                'max_participants' => 200,
                'status' => 'completed',
            ],
            [
                'title' => 'Campus Wellness Week',
                'description' => 'A series of wellness talks, clinics, and fitness sessions.',
                'start_date' => now()->subDays(9)->setTime(8, 0),
                'end_date' => now()->subDays(8)->setTime(17, 0),
                'location' => 'Student Center',
                'max_participants' => 180,
                'status' => 'completed',
            ],
            [
                'title' => 'Student Government Planning Day',
                'description' => 'Internal planning session for next term initiatives.',
                'start_date' => now()->addDays(10)->setTime(10, 0),
                'end_date' => now()->addDays(10)->setTime(14, 0),
                'location' => 'Conference Room A',
                'max_participants' => 40,
                'status' => 'draft',
            ],
            [
                'title' => 'Community Outreach Caravan',
                'description' => 'Volunteer coordination and deployment for outreach work.',
                'start_date' => now()->addDays(12)->setTime(7, 0),
                'end_date' => now()->addDays(12)->setTime(18, 0),
                'location' => 'South Gate Assembly Area',
                'max_participants' => 90,
                'status' => 'cancelled',
            ],
            [
                'title' => 'Tech Talk Series',
                'description' => 'Speaker sessions on software engineering, AI, and startups.',
                'start_date' => now()->addDays(6)->setTime(13, 0),
                'end_date' => now()->addDays(6)->setTime(17, 30),
                'location' => 'IT Lecture Hall',
                'max_participants' => 140,
                'status' => 'published',
            ],
            [
                'title' => 'Alumni Homecoming Forum',
                'description' => 'A moderated forum connecting current students with alumni mentors.',
                'start_date' => now()->subHours(3),
                'end_date' => now()->addHours(2),
                'location' => 'Grand Theater',
                'max_participants' => 160,
                'status' => 'ongoing',
            ],
            [
                'title' => 'Environmental Awareness Drive',
                'description' => 'Talks and activities focused on sustainability and campus action.',
                'start_date' => now()->subDays(14)->setTime(9, 0),
                'end_date' => now()->subDays(14)->setTime(15, 0),
                'location' => 'Open Grounds',
                'max_participants' => 110,
                'status' => 'completed',
            ],
        ];
    }

    private function seedRegistrationQr(Event $event): void
    {
        $registrationQrCode = 'EVTREG|' . $event->id . '|demo-' . Str::slug($event->title);
        $status = $event->status === 'cancelled' ? 'revoked' : 'active';
        $expiresAt = $event->status === 'completed'
            ? $event->end_date->copy()->subDay()
            : $event->end_date->copy()->addDays(7);

        QRCode::updateOrCreate(
            [
                'event_id' => $event->id,
                'registration_id' => null,
                'type' => 'event_registration',
            ],
            [
                'code' => $registrationQrCode,
                'status' => $status,
                'expires_at' => $expiresAt,
                'qr_image_data' => json_encode([
                    'type' => 'text',
                    'value' => $registrationQrCode,
                ]),
            ]
        );
    }

    private function seedRegistrationsForEvent(Event $event, Collection $students, User $admin, int $eventIndex): void
    {
        $matrix = match ($event->status) {
            'draft' => [],
            'cancelled' => [
                ['offset' => 0, 'status' => 'cancelled', 'attended' => false],
                ['offset' => 1, 'status' => 'rejected', 'attended' => false],
            ],
            'completed' => [
                ['offset' => 0, 'status' => 'approved', 'attended' => true],
                ['offset' => 1, 'status' => 'approved', 'attended' => true],
                ['offset' => 2, 'status' => 'approved', 'attended' => false],
                ['offset' => 3, 'status' => 'rejected', 'attended' => false],
            ],
            'ongoing' => [
                ['offset' => 0, 'status' => 'approved', 'attended' => true],
                ['offset' => 1, 'status' => 'approved', 'attended' => true],
                ['offset' => 2, 'status' => 'pending', 'attended' => false],
                ['offset' => 3, 'status' => 'approved', 'attended' => false],
            ],
            default => [
                ['offset' => 0, 'status' => 'approved', 'attended' => false],
                ['offset' => 1, 'status' => 'pending', 'attended' => false],
                ['offset' => 2, 'status' => 'approved', 'attended' => false],
                ['offset' => 3, 'status' => 'rejected', 'attended' => false],
            ],
        };

        foreach ($matrix as $row) {
            $student = $students->get(($eventIndex + $row['offset']) % $students->count());
            $status = $row['status'];
            $isApproved = $status === 'approved';

            $registration = Registration::firstOrNew([
                'event_id' => $event->id,
                'user_id' => $student->id,
            ]);

            $registration->status = $status;
            $registration->remarks = $this->remarksForStatus($status);
            $registration->approved_at = $isApproved ? $event->start_date->copy()->subDay() : null;
            $registration->approved_by = in_array($status, ['approved', 'rejected'], true) ? $admin->id : null;
            $registration->save();

            if (!$isApproved) {
                QRCode::where('registration_id', $registration->id)
                    ->where('type', 'attendance')
                    ->delete();
                AttendanceRecord::where('registration_id', $registration->id)->delete();
                continue;
            }

            $attendanceQr = $this->seedAttendanceQr($event, $registration, (bool) $row['attended']);

            if ($row['attended']) {
                AttendanceRecord::updateOrCreate(
                    ['registration_id' => $registration->id],
                    [
                        'event_id' => $event->id,
                        'user_id' => $registration->user_id,
                        'checked_in_at' => $event->start_date->copy()->addMinutes(20),
                        'checked_out_at' => $event->status === 'completed'
                            ? $event->end_date->copy()->subMinutes(10)
                            : null,
                        'qr_code_reference' => $attendanceQr->code,
                        'check_in_location' => $event->location,
                    ]
                );
            } else {
                AttendanceRecord::where('registration_id', $registration->id)->delete();
            }
        }

        $event->update([
            'current_attendees' => AttendanceRecord::where('event_id', $event->id)->count(),
        ]);
    }

    private function seedAttendanceQr(Event $event, Registration $registration, bool $attended): QRCode
    {
        $attendanceCode = 'ATD|' . $event->id . '|' . $registration->id;

        return QRCode::updateOrCreate(
            [
                'registration_id' => $registration->id,
                'type' => 'attendance',
            ],
            [
                'event_id' => $event->id,
                'code' => $attendanceCode,
                'status' => $attended ? 'scanned' : 'active',
                'expires_at' => $event->end_date->copy()->addDays(7),
                'scanned_at' => $attended ? $event->start_date->copy()->addMinutes(20) : null,
                'qr_image_data' => json_encode([
                    'type' => 'text',
                    'value' => $attendanceCode,
                ]),
            ]
        );
    }

    private function remarksForStatus(string $status): ?string
    {
        return match ($status) {
            'approved' => 'Approved for demo flow.',
            'pending' => 'Waiting for admin review.',
            'rejected' => 'Rejected for demo variety.',
            'cancelled' => 'Registration cancelled after event cancellation.',
            default => null,
        };
    }
}
