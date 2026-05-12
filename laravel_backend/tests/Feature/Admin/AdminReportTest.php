<?php

namespace Tests\Feature\Admin;

use App\Models\AttendanceRecord;
use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_reports_dashboard(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $student = User::factory()->create([
            'role' => 'student',
        ]);

        $event = Event::create([
            'title' => 'Leadership Summit',
            'description' => 'Annual leadership event',
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'location' => 'Main Hall',
            'max_participants' => 120,
            'status' => 'ongoing',
            'created_by' => $admin->id,
        ]);

        $registration = Registration::create([
            'event_id' => $event->id,
            'user_id' => $student->id,
            'status' => 'approved',
            'approved_by' => $admin->id,
            'approved_at' => now(),
        ]);

        AttendanceRecord::create([
            'registration_id' => $registration->id,
            'event_id' => $event->id,
            'user_id' => $student->id,
            'checked_in_at' => now()->subHours(2),
            'qr_code_reference' => 'QR-REPORT-001',
            'check_in_location' => 'Main Hall Gate',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.reports.index'));

        $response->assertOk()
            ->assertSee('Reports Dashboard')
            ->assertSee('Leadership Summit')
            ->assertSee('Export PDF')
            ->assertSee('Export Excel');
    }

    public function test_admin_can_export_reports_as_excel_and_pdf(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        Event::create([
            'title' => 'Research Congress',
            'description' => 'Research presentations',
            'start_date' => now()->subDays(5),
            'end_date' => now()->subDays(4),
            'location' => 'Auditorium',
            'max_participants' => 80,
            'status' => 'completed',
            'created_by' => $admin->id,
        ]);

        $excelResponse = $this->actingAs($admin)
            ->get(route('admin.reports.export.excel', ['status' => 'completed']));

        $excelResponse->assertOk();
        $excelResponse->assertHeader('content-type', 'application/vnd.ms-excel; charset=UTF-8');
        $excelResponse->assertHeader('content-disposition');
        $excelResponse->assertSee('Research Congress');

        $pdfResponse = $this->actingAs($admin)
            ->get(route('admin.reports.export.pdf', ['status' => 'completed']));

        $pdfResponse->assertOk();
        $pdfResponse->assertHeader('content-type', 'application/pdf');
        $pdfResponse->assertHeader('content-disposition');
        $this->assertStringStartsWith('%PDF-1.4', $pdfResponse->baseResponse->getContent());
    }

    public function test_admin_report_export_accepts_date_range_and_multiple_statuses(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        Event::create([
            'title' => 'Campus Forum',
            'description' => 'Ongoing campus discussion',
            'start_date' => '2026-04-10 09:00:00',
            'end_date' => '2026-04-10 12:00:00',
            'location' => 'Main Hall',
            'max_participants' => 80,
            'status' => 'ongoing',
            'created_by' => $admin->id,
        ]);

        Event::create([
            'title' => 'Cancelled Workshop',
            'description' => 'Workshop cancelled by admin',
            'start_date' => '2026-06-15 09:00:00',
            'end_date' => '2026-06-15 12:00:00',
            'location' => 'Lab 1',
            'max_participants' => 40,
            'status' => 'cancelled',
            'created_by' => $admin->id,
        ]);

        Event::create([
            'title' => 'Outside Completion',
            'description' => 'Completed outside selected date range',
            'start_date' => '2026-11-03 09:00:00',
            'end_date' => '2026-11-03 12:00:00',
            'location' => 'Auditorium',
            'max_participants' => 120,
            'status' => 'completed',
            'created_by' => $admin->id,
        ]);

        Event::create([
            'title' => 'Draft Planning',
            'description' => 'Draft should not be selected',
            'start_date' => '2026-05-20 09:00:00',
            'end_date' => '2026-05-20 12:00:00',
            'location' => 'Room 4',
            'max_participants' => 25,
            'status' => 'draft',
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.reports.export.excel', [
                'date_from' => '2026-04-01',
                'date_to' => '2026-10-31',
                'statuses' => ['ongoing', 'cancelled'],
            ]));

        $response->assertOk()
            ->assertSee('Campus Forum')
            ->assertSee('Cancelled Workshop')
            ->assertDontSee('Outside Completion')
            ->assertDontSee('Draft Planning');
    }
}
