@extends('dashboard.layout')

@section('title', 'Attendance Tracking')

@section('content')
<div class="page-title">Attendance Tracking</div>

<div style="display: flex; gap: 1rem; margin-bottom: 2rem; align-items: center;">
    <select id="eventFilter" style="padding: 0.75rem 1rem; border: 1px solid #ddd; border-radius: 4px; flex: 1; max-width: 300px;">
        <option value="">All Events</option>
    </select>
    <input type="text" id="qrCodeInput" placeholder="Scan QR Code..." style="padding: 0.75rem 1rem; border: 2px solid #007bff; border-radius: 4px; flex: 1; max-width: 300px; font-weight: bold;">
    <input type="text" id="searchInput" placeholder="Search attendees..." style="padding: 0.75rem 1rem; border: 1px solid #ddd; border-radius: 4px; flex: 1; max-width: 300px;">
</div>

<div style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 2rem;">
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 2rem;">
        <div style="background: #f5f5f5; padding: 1rem; border-radius: 4px; text-align: center;">
            <p style="color: #666; font-size: 0.9rem;">Total Checked In</p>
            <p style="font-size: 2rem; font-weight: bold; color: #007bff;" id="totalCheckedIn">0</p>
        </div>
        <div style="background: #f5f5f5; padding: 1rem; border-radius: 4px; text-align: center;">
            <p style="color: #666; font-size: 0.9rem;">Checked Out</p>
            <p style="font-size: 2rem; font-weight: bold; color: #28a745;" id="totalCheckedOut">0</p>
        </div>
        <div style="background: #f5f5f5; padding: 1rem; border-radius: 4px; text-align: center;">
            <p style="color: #666; font-size: 0.9rem;">Still Inside</p>
            <p style="font-size: 2rem; font-weight: bold; color: #ffc107;" id="stillInside">0</p>
        </div>
        <div style="background: #f5f5f5; padding: 1rem; border-radius: 4px; text-align: center;">
            <p style="color: #666; font-size: 0.9rem;">No-Shows</p>
            <p style="font-size: 2rem; font-weight: bold; color: #dc3545;" id="noShows">0</p>
        </div>
    </div>
    
    <div id="attendanceTableContainer">
        <p style="text-align: center; color: #999;">Loading attendance records...</p>
    </div>
</div>

@push('scripts')
<script>
    const API_BASE_URL = '/api';
    const token = localStorage.getItem('token');
    let currentEventId = null;
    
    function loadEvents() {
        fetch(`${API_BASE_URL}/events`, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            const events = data.data || data;
            const select = document.getElementById('eventFilter');
            
            events.forEach(event => {
                const option = document.createElement('option');
                option.value = event.id;
                option.textContent = `${event.title} (${event.status})`;
                select.appendChild(option);
            });
        })
        .catch(error => console.error('Error loading events:', error));
    }
    
    function loadAttendance() {
        const eventId = document.getElementById('eventFilter').value || currentEventId;
        const search = document.getElementById('searchInput').value;
        
        let url = `${API_BASE_URL}/attendance`;
        const params = new URLSearchParams();
        if (eventId) params.append('event_id', eventId);
        if (search) params.append('search', search);
        
        if (params.toString()) url += '?' + params.toString();
        
        fetch(url, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            const records = data.data || data;
            renderAttendance(records);
            updateStats(records);
        })
        .catch(error => {
            console.error('Error loading attendance:', error);
            showAlert('Error loading attendance: ' + error.message, 'error');
        });
    }
    
    function updateStats(records) {
        const totalCheckedIn = records.filter(r => r.checked_in_at).length;
        const totalCheckedOut = records.filter(r => r.checked_out_at).length;
        const stillInside = totalCheckedIn - totalCheckedOut;
        const noShows = records.filter(r => !r.checked_in_at).length;
        
        document.getElementById('totalCheckedIn').textContent = totalCheckedIn;
        document.getElementById('totalCheckedOut').textContent = totalCheckedOut;
        document.getElementById('stillInside').textContent = stillInside;
        document.getElementById('noShows').textContent = noShows;
    }
    
    function renderAttendance(records) {
        const container = document.getElementById('attendanceTableContainer');
        
        if (!Array.isArray(records) || records.length === 0) {
            container.innerHTML = '<p style="text-align: center; color: #999;">No attendance records found.</p>';
            return;
        }
        
        let html = '<table><thead><tr><th>Registration #</th><th>User Name</th><th>Email</th><th>Event</th><th>Check-In</th><th>Check-Out</th><th>Duration</th><th>Status</th><th>Actions</th></tr></thead><tbody>';
        
        records.forEach(record => {
            const checkIn = record.checked_in_at ? new Date(record.checked_in_at).toLocaleString() : '-';
            const checkOut = record.checked_out_at ? new Date(record.checked_out_at).toLocaleString() : '-';
            const duration = record.duration_minutes ? Math.round(record.duration_minutes) + ' min' : '-';
            
            let status = 'No-Show';
            let statusColor = 'danger';
            if (record.checked_in_at) {
                status = record.checked_out_at ? 'Checked Out' : 'Inside';
                statusColor = record.checked_out_at ? 'success' : 'warning';
            }
            
            html += `<tr>
                <td><strong>${record.registration_number || '#' + record.id}</strong></td>
                <td>${record.user_name || 'N/A'}</td>
                <td>${record.user_email || 'N/A'}</td>
                <td>${record.event_title || 'N/A'}</td>
                <td>${checkIn}</td>
                <td>${checkOut}</td>
                <td>${duration}</td>
                <td><span class="badge badge-${statusColor}">${status}</span></td>
                <td>
                    ${!record.checked_in_at ? `
                        <button class="btn btn-sm btn-success" onclick="manualCheckIn(${record.id})">Check In</button>
                    ` : !record.checked_out_at ? `
                        <button class="btn btn-sm btn-warning" onclick="manualCheckOut(${record.id})">Check Out</button>
                    ` : `
                        <button class="btn btn-sm btn-primary" onclick="viewAttendance(${record.id})">View</button>
                    `}
                </td>
            </tr>`;
        });
        
        html += '</tbody></table>';
        container.innerHTML = html;
    }
    
    function handleQRCode(qrValue) {
        if (!qrValue || qrValue.length < 2) return;
        
        let endpoint = `/attendance/verify-qr`;
        const params = new URLSearchParams();
        params.append('qr_code', qrValue);
        
        const eventId = document.getElementById('eventFilter').value;
        if (eventId) params.append('event_id', eventId);
        
        if (params.toString()) endpoint += '?' + params.toString();
        
        fetch(API_BASE_URL + endpoint, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ qr_code: qrValue })
        })
        .then(response => {
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            return response.json();
        })
        .then(data => {
            const message = data.message || 'QR code processed';
            showAlert(message, 'success');
            document.getElementById('qrCodeInput').value = '';
            loadAttendance();
        })
        .catch(error => {
            showAlert('Error: ' + error.message, 'error');
            document.getElementById('qrCodeInput').value = '';
        });
    }
    
    function manualCheckIn(recordId) {
        fetch(`${API_BASE_URL}/attendance/${recordId}/check-in`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (response.ok) {
                showAlert('Checked in successfully', 'success');
                loadAttendance();
            } else {
                showAlert('Error checking in', 'error');
            }
        })
        .catch(error => showAlert('Error: ' + error.message, 'error'));
    }
    
    function manualCheckOut(recordId) {
        fetch(`${API_BASE_URL}/attendance/${recordId}/check-out`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (response.ok) {
                showAlert('Checked out successfully', 'success');
                loadAttendance();
            } else {
                showAlert('Error checking out', 'error');
            }
        })
        .catch(error => showAlert('Error: ' + error.message, 'error'));
    }
    
    // Event listeners
    document.getElementById('eventFilter').addEventListener('change', loadAttendance);
    document.getElementById('searchInput').addEventListener('keyup', loadAttendance);
    
    document.getElementById('qrCodeInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            handleQRCode(this.value);
        }
    });
    
    // Load on page load
    loadEvents();
    loadAttendance();
    
    // Refresh every 30 seconds
    setInterval(loadAttendance, 30000);
</script>
@endpush
@endsection
