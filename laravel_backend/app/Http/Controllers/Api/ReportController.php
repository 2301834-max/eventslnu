<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Export attendance report as CSV
     */
    public function exportAttendanceCSV(Event $event, Request $request)
    {
        $fileName = 'attendance_'.$event->id.'_'.now()->format('Y-m-d_His').'.csv';

        $query = AttendanceRecord::where('event_id', $event->id)
            ->with('user', 'registration')
            ->orderBy('checked_in_at', 'asc');

        // Apply filters
        if ($request->has('from_date')) {
            $query->where('checked_in_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->where('checked_in_at', '<=', $request->to_date);
        }

        $records = $query->get();

        return response()->stream(function () use ($records, $event) {
            $handle = fopen('php://output', 'w');

            // Header
            fputcsv($handle, [
                'Event',
                'Registration Number',
                'User Name',
                'User Email',
                'Check-In Time',
                'Check-Out Time',
                'Duration (minutes)',
                'Location',
                'Attendance Status',
            ]);

            // Data rows
            foreach ($records as $record) {
                fputcsv($handle, [
                    $event->title,
                    $record->registration->registration_number,
                    $record->user->name,
                    $record->user->email,
                    $record->checked_in_at?->format('Y-m-d H:i:s'),
                    $record->checked_out_at?->format('Y-m-d H:i:s'),
                    $record->getDurationInMinutes() ?? 'N/A',
                    $record->check_in_location ?? 'N/A',
                    $record->checked_out_at ? 'Checked Out' : 'Checked In',
                ]);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ]);
    }

    /**
     * Export registrations report as CSV
     */
    public function exportRegistrationsCSV(Event $event, Request $request)
    {
        $fileName = 'registrations_'.$event->id.'_'.now()->format('Y-m-d_His').'.csv';

        $query = $event->registrations()
            ->with('user', 'approver')
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $registrations = $query->get();

        return response()->stream(function () use ($registrations, $event) {
            $handle = fopen('php://output', 'w');

            // Header
            fputcsv($handle, [
                'Event',
                'Registration Number',
                'User Name',
                'User Email',
                'Status',
                'Registration Date',
                'Approved Date',
                'Approved By',
                'Remarks',
            ]);

            // Data rows
            foreach ($registrations as $reg) {
                fputcsv($handle, [
                    $event->title,
                    $reg->registration_number,
                    $reg->user->name,
                    $reg->user->email,
                    ucfirst($reg->status),
                    $reg->created_at->format('Y-m-d H:i:s'),
                    $reg->approved_at?->format('Y-m-d H:i:s') ?? 'N/A',
                    $reg->approver?->name ?? 'N/A',
                    $reg->remarks ?? 'N/A',
                ]);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ]);
    }

    /**
     * Export event summary report as JSON
     */
    public function exportEventSummary(Event $event): JsonResponse
    {
        $totalRegistrations = $event->registrations()->count();
        $approvedRegistrations = $event->getApprovedRegistrationsCount();
        $totalAttended = $event->getAttendanceCount();
        $attendanceRate = $event->getAttendanceRate();

        $report = [
            'report_type' => 'Event Summary Report',
            'generated_at' => now(),
            'event' => [
                'id' => $event->id,
                'title' => $event->title,
                'description' => $event->description,
                'location' => $event->location,
                'start_date' => $event->start_date,
                'end_date' => $event->end_date,
                'status' => $event->status,
                'creator' => $event->creator->name,
            ],
            'registration_summary' => [
                'total_registrations' => $totalRegistrations,
                'approved' => $approvedRegistrations,
                'pending' => $event->registrations()->pending()->count(),
                'rejected' => $event->registrations()->rejected()->count(),
                'cancelled' => $event->registrations()->where('status', 'cancelled')->count(),
            ],
            'attendance_summary' => [
                'total_attended' => $totalAttended,
                'attendance_rate' => $attendanceRate.'%',
                'currently_present' => $event->attendanceRecords()
                    ->whereNull('checked_out_at')
                    ->count(),
                'no_show_count' => $approvedRegistrations - $totalAttended,
            ],
            'event_details' => [
                'max_capacity' => $event->max_participants,
                'is_registration_full' => $event->isRegistrationFull(),
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $report,
        ]);
    }

    /**
     * Export attendance breakdown by location
     */
    public function exportLocationBreakdown(Event $event)
    {
        $fileName = 'location_breakdown_'.$event->id.'_'.now()->format('Y-m-d_His').'.csv';

        $locationData = AttendanceRecord::where('event_id', $event->id)
            ->with('user')
            ->orderBy('check_in_location', 'asc')
            ->get()
            ->groupBy('check_in_location');

        return response()->stream(function () use ($locationData, $event) {
            $handle = fopen('php://output', 'w');

            // Header
            fputcsv($handle, [
                'Event',
                'Location',
                'Count',
                'Percentage',
            ]);

            $totalCount = $locationData->sum(fn ($group) => $group->count());

            // Data rows
            foreach ($locationData as $location => $records) {
                $count = $records->count();
                $percentage = $totalCount > 0 ? round(($count / $totalCount) * 100, 2) : 0;

                fputcsv($handle, [
                    $event->title,
                    $location ?? 'Not Specified',
                    $count,
                    $percentage.'%',
                ]);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ]);
    }

    /**
     * Export no-show report
     */
    public function exportNoShowReport(Event $event)
    {
        $fileName = 'no_show_report_'.$event->id.'_'.now()->format('Y-m-d_His').'.csv';

        $noShows = $event->registrations()
            ->where('status', 'approved')
            ->doesntHave('attendanceRecord')
            ->with('user', 'approver')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->stream(function () use ($noShows, $event) {
            $handle = fopen('php://output', 'w');

            // Header
            fputcsv($handle, [
                'Event',
                'User Name',
                'User Email',
                'Registration Number',
                'Registration Date',
                'Approved Date',
                'Approved By',
            ]);

            // Data rows
            foreach ($noShows as $reg) {
                fputcsv($handle, [
                    $event->title,
                    $reg->user->name,
                    $reg->user->email,
                    $reg->registration_number,
                    $reg->created_at->format('Y-m-d H:i:s'),
                    $reg->approved_at?->format('Y-m-d H:i:s') ?? 'N/A',
                    $reg->approver?->name ?? 'N/A',
                ]);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ]);
    }

    /**
     * Export time analysis report
     */
    public function exportTimeAnalysis(Event $event)
    {
        $fileName = 'time_analysis_'.$event->id.'_'.now()->format('Y-m-d_His').'.csv';

        $records = AttendanceRecord::where('event_id', $event->id)
            ->whereNotNull('checked_out_at')
            ->with('user', 'registration')
            ->orderBy('checked_in_at', 'asc')
            ->get();

        return response()->stream(function () use ($records, $event) {
            $handle = fopen('php://output', 'w');

            // Header
            fputcsv($handle, [
                'Event',
                'User Name',
                'User Email',
                'Check-In Date',
                'Check-In Time',
                'Check-Out Time',
                'Duration (minutes)',
                'Duration (formatted)',
            ]);

            // Data rows
            foreach ($records as $record) {
                $minutes = $record->getDurationInMinutes() ?? 0;
                $hours = intval($minutes / 60);
                $mins = intval($minutes % 60);
                $formatted = sprintf('%dh %dmin', $hours, $mins);

                fputcsv($handle, [
                    $event->title,
                    $record->user->name,
                    $record->user->email,
                    $record->checked_in_at->format('Y-m-d'),
                    $record->checked_in_at->format('H:i:s'),
                    $record->checked_out_at->format('H:i:s'),
                    $minutes,
                    $formatted,
                ]);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ]);
    }
}
