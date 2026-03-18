<?php

namespace App\Http\Controllers\Api;

use App\Models\Event;
use App\Models\AttendanceRecord;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class StatisticsController extends Controller
{
    /**
     * Get comprehensive event statistics
     */
    public function eventStats(Event $event): JsonResponse
    {
        $totalRegistrations = $event->registrations()->count();
        $approvedRegistrations = $event->getApprovedRegistrationsCount();
        $pendingRegistrations = $event->registrations()->pending()->count();
        $rejectedRegistrations = $event->registrations()->rejected()->count();
        $totalAttended = $event->getAttendanceCount();
        $attendanceRate = $event->getAttendanceRate();

        // Time spent statistics
        $attendanceData = $event->attendanceRecords()
            ->whereNotNull('checked_out_at')
            ->where('checked_in_at', '<', now())
            ->get();

        $avgTimeSpent = 0;
        if ($attendanceData->count() > 0) {
            $totalMinutes = $attendanceData->sum(function ($record) {
                return $record->getDurationInMinutes() ?? 0;
            });
            $avgTimeSpent = $attendanceData->count() > 0 ? round($totalMinutes / $attendanceData->count(), 2) : 0;
        }

        // Peak check-in time
        $peakTime = AttendanceRecord::where('event_id', $event->id)
            ->selectRaw("DATE_FORMAT(checked_in_at, '%H:00') as hour, COUNT(*) as count")
            ->groupBy('hour')
            ->orderBy('count', 'desc')
            ->first();

        return response()->json([
            'success' => true,
            'event' => $event->only(['id', 'title', 'location', 'start_date', 'end_date', 'status']),
            'registration_stats' => [
                'total' => $totalRegistrations,
                'approved' => $approvedRegistrations,
                'pending' => $pendingRegistrations,
                'rejected' => $rejectedRegistrations,
                'cancelled' => $event->registrations()->where('status', 'cancelled')->count(),
                'rejection_rate' => $totalRegistrations > 0 
                    ? round(($rejectedRegistrations / $totalRegistrations) * 100, 2) . '%'
                    : '0%',
            ],
            'attendance_stats' => [
                'total_attended' => $totalAttended,
                'approved_count' => $approvedRegistrations,
                'attendance_rate' => $attendanceRate . '%',
                'average_time_spent_minutes' => $avgTimeSpent,
                'currently_present' => $event->attendanceRecords()
                    ->whereNull('checked_out_at')
                    ->count(),
                'peak_check_in_hour' => $peakTime?->hour ?? 'N/A',
            ],
            'event_info' => [
                'capacity' => $event->max_participants === 0 ? 'Unlimited' : $event->max_participants,
                'is_full' => $event->isRegistrationFull(),
                'available_slots' => $event->max_participants === 0 
                    ? 'Unlimited'
                    : max(0, $event->max_participants - $approvedRegistrations),
            ]
        ]);
    }

    /**
     * Get hourly attendance breakdown
     */
    public function hourlyAttendance(Event $event, Request $request): JsonResponse
    {
        $query = AttendanceRecord::where('event_id', $event->id);

        // Filter by date range
        if ($request->has('from_date')) {
            $query->where('checked_in_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->where('checked_in_at', '<=', $request->to_date);
        }

        $hourlyData = $query
            ->selectRaw("DATE_FORMAT(checked_in_at, '%Y-%m-%d %H:00') as hour, COUNT(*) as count")
            ->groupBy('hour')
            ->orderBy('hour', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $hourlyData,
            'total_records' => $hourlyData->sum('count')
        ]);
    }

    /**
     * Get daily attendance breakdown
     */
    public function dailyAttendance(Event $event, Request $request): JsonResponse
    {
        $query = AttendanceRecord::where('event_id', $event->id);

        // Filter by date range
        if ($request->has('from_date')) {
            $query->where('checked_in_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->where('checked_in_at', '<=', $request->to_date);
        }

        $dailyData = $query
            ->selectRaw("DATE(checked_in_at) as date, COUNT(DISTINCT user_id) as count")
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $dailyData,
            'total_unique_attendees' => $dailyData->sum('count')
        ]);
    }

    /**
     * Get location-based attendance statistics
     */
    public function locationStats(Event $event): JsonResponse
    {
        $locationData = AttendanceRecord::where('event_id', $event->id)
            ->selectRaw("check_in_location, COUNT(*) as count")
            ->whereNotNull('check_in_location')
            ->groupBy('check_in_location')
            ->orderBy('count', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $locationData,
            'total_with_location' => $locationData->sum('count')
        ]);
    }

    /**
     * Get user attendance patterns
     */
    public function userAttendancePatterns(Event $event): JsonResponse
    {
        $patterns = AttendanceRecord::where('event_id', $event->id)
            ->with('user')
            ->with(['registration' => function ($query) {
                $query->select('id', 'user_id', 'status');
            }])
            ->get()
            ->map(function ($record) {
                return [
                    'user_id' => $record->user_id,
                    'user_name' => $record->user->name,
                    'user_email' => $record->user->email,
                    'registration_status' => $record->registration->status,
                    'checked_in_at' => $record->checked_in_at,
                    'checked_out_at' => $record->checked_out_at,
                    'time_spent_minutes' => $record->getDurationInMinutes(),
                    'location' => $record->check_in_location
                ];
            })
            ->sortByDesc('checked_in_at')
            ->values();

        return response()->json([
            'success' => true,
            'data' => $patterns,
            'total_attendance_records' => $patterns->count()
        ]);
    }

    /**
     * Get comparison between registrations and attendance
     */
    public function registrationAttendanceComparison(Event $event): JsonResponse
    {
        $registrations = $event->registrations()
            ->with('user', 'attendanceRecord')
            ->get();

        $attended = 0;
        $notAttended = 0;

        foreach ($registrations as $reg) {
            if ($reg->attendanceRecord) {
                $attended++;
            } else {
                $notAttended++;
            }
        }

        return response()->json([
            'success' => true,
            'summary' => [
                'total_approved' => $event->getApprovedRegistrationsCount(),
                'attended' => $attended,
                'not_attended' => $notAttended,
                'attendance_rate' => $event->getApprovedRegistrationsCount() > 0
                    ? round(($attended / $event->getApprovedRegistrationsCount()) * 100, 2) . '%'
                    : '0%'
            ],
            'details' => $registrations->map(function ($reg) {
                return [
                    'user_id' => $reg->user_id,
                    'user_name' => $reg->user->name,
                    'registration_number' => $reg->registration_number,
                    'registration_status' => $reg->status,
                    'attended' => $reg->attendanceRecord ? true : false,
                    'check_in_time' => $reg->attendanceRecord?->checked_in_at,
                    'check_out_time' => $reg->attendanceRecord?->checked_out_at,
                ];
            })->values()
        ]);
    }

    /**
     * Get real-time event metrics
     */
    public function realtimeMetrics(Event $event): JsonResponse
    {
        $now = now();
        $isOngoing = $event->status === 'ongoing' || 
                     ($event->start_date <= $now && $event->end_date >= $now);

        return response()->json([
            'success' => true,
            'event_id' => $event->id,
            'event_title' => $event->title,
            'is_ongoing' => $isOngoing,
            'metrics' => [
                'currently_present' => $event->attendanceRecords()
                    ->whereNull('checked_out_at')
                    ->count(),
                'total_attended_so_far' => $event->attendanceRecords()->count(),
                'pending_approvals' => $event->registrations()->pending()->count(),
                'check_ins_last_hour' => $event->attendanceRecords()
                    ->where('checked_in_at', '>=', now()->subHour())
                    ->count(),
                'check_ins_last_minute' => $event->attendanceRecords()
                    ->where('checked_in_at', '>=', now()->subMinute())
                    ->count(),
            ],
            'timestamp' => now()
        ]);
    }

    /**
     * Get no-show analysis
     */
    public function noShowAnalysis(Event $event): JsonResponse
    {
        $noShows = $event->registrations()
            ->where('status', 'approved')
            ->doesntHave('attendanceRecord')
            ->with('user', 'approver')
            ->get();

        return response()->json([
            'success' => true,
            'summary' => [
                'total_no_shows' => $noShows->count(),
                'no_show_rate' => $event->getApprovedRegistrationsCount() > 0
                    ? round(($noShows->count() / $event->getApprovedRegistrationsCount()) * 100, 2) . '%'
                    : '0%'
            ],
            'no_show_list' => $noShows->map(function ($reg) {
                return [
                    'user_id' => $reg->user_id,
                    'user_name' => $reg->user->name,
                    'user_email' => $reg->user->email,
                    'registration_number' => $reg->registration_number,
                    'approved_at' => $reg->approved_at,
                    'approved_by' => $reg->approver?->name
                ];
            })->values()
        ]);
    }
}
