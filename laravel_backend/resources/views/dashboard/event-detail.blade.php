@extends('dashboard.layout')

@section('title', $event->title)

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div class="page-title">{{ $event->title }}</div>
    <a href="{{ route('dashboard.events') }}" class="btn btn-secondary">← Back to Events</a>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
    <!-- Main Content -->
    <div>
        <!-- Event Details Card -->
        <div style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 2rem;">
            <h3 style="margin-bottom: 1.5rem; color: #333;">Event Details</h3>
            <img src="{{ $event->event_image_url }}" alt="{{ $event->title }} poster" style="width: 100%; height: 260px; object-fit: cover; border-radius: 8px; margin-bottom: 1.5rem;">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                <div>
                    <p style="font-size: 0.9rem; color: #666; margin-bottom: 0.5rem;">Hosted By</p>
                    <p style="font-size: 1.1rem; font-weight: 500; color: #333;">{{ $event->organization ?? 'Not specified' }}</p>
                </div>
                <div>
                    <p style="font-size: 0.9rem; color: #666; margin-bottom: 0.5rem;">Location</p>
                    <p style="font-size: 1.1rem; font-weight: 500; color: #333;">{{ $event->location }}</p>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-top: 1.5rem;">
                <div>
                    <p style="font-size: 0.9rem; color: #666; margin-bottom: 0.5rem;">Capacity</p>
                    <p style="font-size: 1.1rem; font-weight: 500; color: #333;">{{ $event->max_participants }} attendees</p>
                </div>
                <div>
                    <p style="font-size: 0.9rem; color: #666; margin-bottom: 0.5rem;">Start Date</p>
                    <p style="font-size: 1.1rem; font-weight: 500; color: #333;">{{ $event->start_date->format('M d, Y H:i') }}</p>
                </div>
                <div>
                    <p style="font-size: 0.9rem; color: #666; margin-bottom: 0.5rem;">End Date</p>
                    <p style="font-size: 1.1rem; font-weight: 500; color: #333;">{{ $event->end_date->format('M d, Y H:i') }}</p>
                </div>
            </div>
            
            @if($event->description)
                <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #eee;">
                    <p style="font-size: 0.9rem; color: #666; margin-bottom: 0.5rem;">Description</p>
                    <p style="color: #555;">{{ $event->description }}</p>
                </div>
            @endif
        </div>
        
        <!-- Statistics Grid -->
        <div class="stats-grid">
            <div class="stat-card blue">
                <div class="stat-label">Total Registrations</div>
                <div class="stat-value" id="totalRegs">{{ $event->registrations_count ?? 0 }}</div>
            </div>
            <div class="stat-card orange">
                <div class="stat-label">Pending Approvals</div>
                <div class="stat-value" id="pendingRegs">{{ $event->registrations()->where('status', 'pending')->count() }}</div>
            </div>
            <div class="stat-card green">
                <div class="stat-label">Approved</div>
                <div class="stat-value" id="approvedRegs">{{ $event->registrations()->where('status', 'approved')->count() }}</div>
            </div>
            <div class="stat-card blue">
                <div class="stat-label">Attendance</div>
                <div class="stat-value" id="attendance">{{ $event->attendance_count ?? 0 }}</div>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-top: 2rem; display: flex; gap: 1rem; flex-wrap: wrap;">
            @if($event->status === 'draft')
                <button class="btn btn-success" onclick="publishEvent()">Publish Event</button>
            @endif
            
            @if($event->status === 'published')
                <button class="btn btn-success" onclick="startEvent()">Start Event</button>
            @endif
            
            @if($event->status === 'ongoing')
                <button class="btn btn-primary" onclick="endEvent()">End Event</button>
            @endif
            
            @if($event->status !== 'completed' && $event->status !== 'cancelled')
                <button class="btn btn-danger" onclick="cancelEvent()">Cancel Event</button>
            @endif
            
            <a href="{{ route('dashboard.registrations') }}" class="btn btn-primary">View Registrations</a>
            <a href="{{ route('dashboard.attendance') }}" class="btn btn-primary">View Attendance</a>
        </div>
    </div>
    
    <!-- Sidebar -->
    <div>
        <!-- Status Card -->
        <div style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 2rem;">
            <p style="font-size: 0.9rem; color: #666; margin-bottom: 0.5rem;">Current Status</p>
            @php
                $badgeClass = match($event->status) {
                    'draft' => 'secondary',
                    'published' => 'info',
                    'ongoing' => 'success',
                    'completed' => 'success',
                    'cancelled' => 'danger',
                    default => 'secondary'
                };
            @endphp
            <div style="font-size: 1.5rem; font-weight: bold; padding: 1rem 0;">
                <span class="badge badge-{{ $badgeClass }}" style="font-size: 1rem; padding: 0.5rem 1rem;">
                    {{ ucfirst($event->status) }}
                </span>
            </div>
        </div>
        
        <!-- Created By -->
        <div style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 2rem;">
            <p style="font-size: 0.9rem; color: #666; margin-bottom: 0.5rem;">Created By</p>
            <p style="font-size: 1rem; font-weight: 500; color: #333;">{{ $event->creator->name ?? 'Admin' }}</p>
            <p style="font-size: 0.9rem; color: #999; margin-top: 0.5rem;">{{ $event->created_at->format('M d, Y') }}</p>
        </div>
        
        <!-- Quick Stats -->
        <div style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h4 style="margin-bottom: 1rem; color: #333;">Quick Stats</h4>
            <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                <span style="color: #666;">Attendance Rate:</span>
                <strong id="attendanceRate">0%</strong>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span style="color: #666;">Capacity Used:</span>
                <strong id="capacityUsed">0%</strong>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const eventId = {{ $event->id }};
    const API_BASE_URL = '/api';
    const token = localStorage.getItem('token');
    
    function apiRequest(method, endpoint) {
        return fetch(`${API_BASE_URL}${endpoint}`, {
            method: method,
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            return response.json();
        });
    }
    
    function publishEvent() {
        apiRequest('POST', `/events/${eventId}/publish`)
            .then(() => {
                showAlert('Event published successfully', 'success');
                setTimeout(() => location.reload(), 1000);
            })
            .catch(err => showAlert('Error: ' + err.message, 'error'));
    }
    
    function startEvent() {
        apiRequest('POST', `/events/${eventId}/start`)
            .then(() => {
                showAlert('Event started successfully', 'success');
                setTimeout(() => location.reload(), 1000);
            })
            .catch(err => showAlert('Error: ' + err.message, 'error'));
    }
    
    function endEvent() {
        apiRequest('POST', `/events/${eventId}/end`)
            .then(() => {
                showAlert('Event ended successfully', 'success');
                setTimeout(() => location.reload(), 1000);
            })
            .catch(err => showAlert('Error: ' + err.message, 'error'));
    }
    
    function cancelEvent() {
        if (!confirm('Are you sure you want to cancel this event?')) return;
        
        apiRequest('POST', `/events/${eventId}/cancel`)
            .then(() => {
                showAlert('Event cancelled successfully', 'success');
                setTimeout(() => location.reload(), 1000);
            })
            .catch(err => showAlert('Error: ' + err.message, 'error'));
    }
    
    function updateStats() {
        // Get event statistics
        apiRequest('GET', `/events/${eventId}/statistics`)
            .then(data => {
                const stats = data.data || data;
                document.getElementById('totalRegs').textContent = stats.total_registrations || 0;
                document.getElementById('pendingRegs').textContent = stats.pending_registrations || 0;
                document.getElementById('approvedRegs').textContent = stats.approved_registrations || 0;
                document.getElementById('attendance').textContent = stats.total_attended || 0;
                
                const attendanceRate = stats.total_registrations > 0 
                    ? Math.round((stats.total_attended / stats.total_registrations) * 100)
                    : 0;
                document.getElementById('attendanceRate').textContent = attendanceRate + '%';
                
                const capacityUsed = {{ $event->max_participants }} > 0
                    ? Math.round((stats.total_registrations / {{ $event->max_participants }}) * 100)
                    : 0;
                document.getElementById('capacityUsed').textContent = capacityUsed + '%';
            })
            .catch(err => console.log('Could not load statistics'));
    }
    
    // Update stats on load
    updateStats();
    
    // Update stats every 30 seconds
    setInterval(updateStats, 30000);
</script>
@endpush
@endsection
