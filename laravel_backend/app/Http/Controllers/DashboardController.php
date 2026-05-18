<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard
     */
    public function index(): View
    {
        $totalEvents = Event::count();
        $upcomingEvents = Event::where('start_date', '>', now())->count();
        $ongoingEvents = Event::where('status', 'ongoing')->count();
        $completedEvents = Event::where('status', 'completed')->count();
        $studentVisibleEvents = Event::whereNotIn('status', ['draft', 'cancelled']);
        $studentTotalEvents = (clone $studentVisibleEvents)->count();
        $studentAvailableEvents = (clone $studentVisibleEvents)
            ->whereIn('status', ['published', 'ongoing'])
            ->where('end_date', '>=', now())
            ->count();
        $studentUpcomingEvents = (clone $studentVisibleEvents)
            ->whereIn('status', ['published', 'ongoing'])
            ->where('start_date', '>', now())
            ->count();
        $studentOngoingEvents = (clone $studentVisibleEvents)->where('status', 'ongoing')->count();
        $studentCompletedEvents = (clone $studentVisibleEvents)->where('status', 'completed')->count();
        $registeredStudents = User::where('role', 'student')->count();

        $totalRegistrations = Registration::count();
        $pendingRegistrations = Registration::where('status', 'pending')->count();
        $approvedRegistrations = Registration::where('status', 'approved')->count();

        $totalAttendance = AttendanceRecord::count();
        $recentEvents = Event::latest()->take(5)->get();
        $studentRecentEvents = Event::whereNotIn('status', ['draft', 'cancelled'])
            ->latest()
            ->take(5)
            ->get();

        // Get API token from session if available
        $apiToken = session('api_token');

        // Check if user is admin
        if (auth()->check() && auth()->user()->isAdmin()) {
            return view('admin.dashboard', [
                'totalEvents' => $totalEvents,
                'upcomingEvents' => $upcomingEvents,
                'ongoingEvents' => $ongoingEvents,
                'completedEvents' => $completedEvents,
                'registeredStudents' => $registeredStudents,
                'totalRegistrations' => $totalRegistrations,
                'pendingRegistrations' => $pendingRegistrations,
                'approvedRegistrations' => $approvedRegistrations,
                'totalAttendance' => $totalAttendance,
                'recentEvents' => $recentEvents,
                'apiToken' => $apiToken,
            ]);
        }

        // Student dashboard
        return view('dashboard.index', [
            'totalEvents' => $studentTotalEvents,
            'availableEvents' => $studentAvailableEvents,
            'upcomingEvents' => $studentUpcomingEvents,
            'ongoingEvents' => $studentOngoingEvents,
            'completedEvents' => $studentCompletedEvents,
            'registeredStudents' => $registeredStudents,
            'totalRegistrations' => $totalRegistrations,
            'pendingRegistrations' => $pendingRegistrations,
            'approvedRegistrations' => $approvedRegistrations,
            'totalAttendance' => $totalAttendance,
            'recentEvents' => $studentRecentEvents,
            'apiToken' => $apiToken,
        ]);
    }

    /**
     * Manage events page
     */
    public function events(): View
    {
        $events = Event::latest()->paginate(10);

        return view('dashboard.events', ['events' => $events]);
    }

    /**
     * Event details page
     */
    public function eventDetail(Event $event): View
    {
        return view('dashboard.event-detail', ['event' => $event]);
    }

    /**
     * Registrations page
     */
    public function registrations(): View
    {
        $registrations = Registration::with('event', 'user')->latest()->paginate(15);

        return view('dashboard.registrations', ['registrations' => $registrations]);
    }

    /**
     * Attendance tracking page
     */
    public function attendance(): View
    {
        $attendanceRecords = AttendanceRecord::with('user', 'event')->latest()->paginate(15);

        return view('dashboard.attendance', ['attendanceRecords' => $attendanceRecords]);
    }

    /**
     * Reports page
     */
    public function reports(): View
    {
        $events = Event::where('status', 'completed')->latest()->get();

        return view('dashboard.reports', ['events' => $events]);
    }
}
