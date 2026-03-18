@extends('dashboard.layout')

@section('title', 'Dashboard')

@section('content')
<div class="page-title">Admin Dashboard</div>

<div class="stats-grid">
    <div class="stat-card blue">
        <div class="stat-label">Total Events</div>
        <div class="stat-value">{{ $totalEvents }}</div>
    </div>
    <div class="stat-card green">
        <div class="stat-label">Upcoming Events</div>
        <div class="stat-value">{{ $upcomingEvents }}</div>
    </div>
    <div class="stat-card orange">
        <div class="stat-label">Ongoing Events</div>
        <div class="stat-value">{{ $ongoingEvents }}</div>
    </div>
    <div class="stat-card red">
        <div class="stat-label">Completed Events</div>
        <div class="stat-value">{{ $completedEvents }}</div>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card blue">
        <div class="stat-label">Total Registrations</div>
        <div class="stat-value">{{ $totalRegistrations }}</div>
    </div>
    <div class="stat-card orange">
        <div class="stat-label">Pending Approvals</div>
        <div class="stat-value">{{ $pendingRegistrations }}</div>
    </div>
    <div class="stat-card green">
        <div class="stat-label">Approved Registrations</div>
        <div class="stat-value">{{ $approvedRegistrations }}</div>
    </div>
    <div class="stat-card blue">
        <div class="stat-label">Total Attendance Records</div>
        <div class="stat-value">{{ $totalAttendance }}</div>
    </div>
</div>

<div style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    <h3 style="margin-bottom: 1.5rem; color: #333;">Recent Events</h3>
    
    @if($recentEvents->count() > 0)
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Event Title</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Start Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentEvents as $event)
                        <tr>
                            <td><strong>{{ $event->title }}</strong></td>
                            <td>{{ $event->location }}</td>
                            <td>{!! getStatusBadge($event->status) !!}</td>
                            <td>{{ $event->start_date->format('M d, Y H:i') }}</td>
                            <td>
                                <a href="{{ route('dashboard.event-detail', $event) }}" class="btn btn-primary">View Details</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p style="color: #999;">No events found.</p>
    @endif
</div>

@push('scripts')
<script>
    function getStatusBadge(status) {
        const badges = {
            'approved': 'success',
            'pending': 'warning',
            'rejected': 'danger',
            'draft': 'info',
            'published': 'info',
            'ongoing': 'success',
            'completed': 'success',
            'cancelled': 'danger'
        };
        const badgeClass = badges[status] || 'info';
        return `<span class="badge badge-${badgeClass}">${status.charAt(0).toUpperCase() + status.slice(1)}</span>`;
    }
</script>
@endpush
@endsection
