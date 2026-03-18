@extends('dashboard.layout')

@section('title', 'Registrations Management')

@section('content')
<div class="page-title">Registrations Management</div>

<div style="display: flex; gap: 1rem; margin-bottom: 2rem; align-items: center;">
    <select id="statusFilter" style="padding: 0.75rem 1rem; border: 1px solid #ddd; border-radius: 4px;">
        <option value="">All Statuses</option>
        <option value="pending">Pending</option>
        <option value="approved">Approved</option>
        <option value="rejected">Rejected</option>
    </select>
    <select id="eventFilter" style="padding: 0.75rem 1rem; border: 1px solid #ddd; border-radius: 4px; flex: 1; max-width: 400px;">
        <option value="">All Events</option>
    </select>
    <input type="text" id="searchInput" placeholder="Search registrations..." style="padding: 0.75rem 1rem; border: 1px solid #ddd; border-radius: 4px; flex: 1; max-width: 300px;">
</div>

<div style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 2rem;">
    <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem;">
        <button class="btn btn-success" id="bulkApproveBtn" onclick="bulkApprove()" style="display: none;">Approve Selected</button>
        <button class="btn btn-danger" id="bulkRejectBtn" onclick="bulkReject()" style="display: none;">Reject Selected</button>
    </div>
    
    <div id="registrationsTableContainer">
        <p style="text-align: center; color: #999;">Loading registrations...</p>
    </div>
</div>

@push('scripts')
<script>
    const API_BASE_URL = '/api';
    const token = localStorage.getItem('token');
    let allRegistrations = [];
    let selectedRegistrations = [];
    
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
                option.textContent = event.title;
                select.appendChild(option);
            });
        })
        .catch(error => console.error('Error loading events:', error));
    }
    
    function loadRegistrations() {
        const status = document.getElementById('statusFilter').value;
        const eventId = document.getElementById('eventFilter').value;
        const search = document.getElementById('searchInput').value;
        
        let url = `${API_BASE_URL}/registrations`;
        const params = new URLSearchParams();
        if (status) params.append('status', status);
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
            allRegistrations = data.data || data;
            renderRegistrations();
        })
        .catch(error => {
            console.error('Error loading registrations:', error);
            showAlert('Error loading registrations: ' + error.message, 'error');
        });
    }
    
    function renderRegistrations() {
        const container = document.getElementById('registrationsTableContainer');
        
        if (!Array.isArray(allRegistrations) || allRegistrations.length === 0) {
            container.innerHTML = '<p style="text-align: center; color: #999;">No registrations found.</p>';
            updateBulkActionButtons();
            return;
        }
        
        let html = '<table><thead><tr><th style="width: 30px;"><input type="checkbox" id="selectAll" onchange="toggleSelectAll()"></th><th>Registration #</th><th>User Name</th><th>Email</th><th>Event</th><th>Status</th><th>Reg. Date</th><th>Actions</th></tr></thead><tbody>';
        
        allRegistrations.forEach(registration => {
            const isSelected = selectedRegistrations.includes(registration.id);
            const statusColor = registration.status === 'pending' ? 'warning' : registration.status === 'approved' ? 'success' : 'danger';
            const regDate = new Date(registration.created_at).toLocaleDateString();
            
            html += `<tr>
                <td><input type="checkbox" value="${registration.id}" onchange="toggleSelection(this)" ${isSelected ? 'checked' : ''}></td>
                <td><strong>${registration.registration_number || '#' + registration.id}</strong></td>
                <td>${registration.user_name || registration.user}</td>
                <td>${registration.user_email || 'N/A'}</td>
                <td>${registration.event_title || registration.event}</td>
                <td><span class="badge badge-${statusColor}">${registration.status}</span></td>
                <td>${regDate}</td>
                <td>
                    ${registration.status === 'pending' ? `
                        <button onclick="approveRegistration(${registration.id})" class="btn btn-sm btn-success">Approve</button>
                        <button onclick="rejectRegistration(${registration.id})" class="btn btn-sm btn-danger">Reject</button>
                    ` : `
                        <button onclick="viewRegistration(${registration.id})" class="btn btn-sm btn-primary">View</button>
                    `}
                </td>
            </tr>`;
        });
        
        html += '</tbody></table>';
        container.innerHTML = html;
        updateBulkActionButtons();
    }
    
    function toggleSelection(checkbox) {
        const regId = parseInt(checkbox.value);
        if (checkbox.checked) {
            if (!selectedRegistrations.includes(regId)) {
                selectedRegistrations.push(regId);
            }
        } else {
            selectedRegistrations = selectedRegistrations.filter(id => id !== regId);
        }
        updateBulkActionButtons();
    }
    
    function toggleSelectAll() {
        if (document.getElementById('selectAll').checked) {
            selectedRegistrations = allRegistrations.map(r => r.id);
        } else {
            selectedRegistrations = [];
        }
        renderRegistrations();
    }
    
    function updateBulkActionButtons() {
        const hasSelection = selectedRegistrations.length > 0;
        document.getElementById('bulkApproveBtn').style.display = hasSelection ? 'block' : 'none';
        document.getElementById('bulkRejectBtn').style.display = hasSelection ? 'block' : 'none';
    }
    
    function approveRegistration(registrationId) {
        fetch(`${API_BASE_URL}/registrations/${registrationId}/approve`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ remarks: 'Approved' })
        })
        .then(response => {
            if (response.ok) {
                showAlert('Registration approved', 'success');
                loadRegistrations();
            } else {
                showAlert('Error approving registration', 'error');
            }
        })
        .catch(error => showAlert('Error: ' + error.message, 'error'));
    }
    
    function rejectRegistration(registrationId) {
        const remarks = prompt('Enter rejection remarks:');
        if (!remarks) return;
        
        fetch(`${API_BASE_URL}/registrations/${registrationId}/reject`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ remarks: remarks })
        })
        .then(response => {
            if (response.ok) {
                showAlert('Registration rejected', 'success');
                loadRegistrations();
            } else {
                showAlert('Error rejecting registration', 'error');
            }
        })
        .catch(error => showAlert('Error: ' + error.message, 'error'));
    }
    
    function bulkApprove() {
        if (selectedRegistrations.length === 0) {
            showAlert('No registrations selected', 'warning');
            return;
        }
        
        if (!confirm(`Approve ${selectedRegistrations.length} registration(s)?`)) return;
        
        fetch(`${API_BASE_URL}/registrations/bulk-approve`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                registration_ids: selectedRegistrations,
                remarks: 'Bulk approved'
            })
        })
        .then(response => {
            if (response.ok) {
                showAlert('Registrations approved successfully', 'success');
                selectedRegistrations = [];
                loadRegistrations();
            } else {
                showAlert('Error approving registrations', 'error');
            }
        })
        .catch(error => showAlert('Error: ' + error.message, 'error'));
    }
    
    function bulkReject() {
        if (selectedRegistrations.length === 0) {
            showAlert('No registrations selected', 'warning');
            return;
        }
        
        const remarks = prompt('Enter rejection remarks:');
        if (!remarks) return;
        
        fetch(`${API_BASE_URL}/registrations/bulk-reject`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                registration_ids: selectedRegistrations,
                remarks: remarks
            })
        })
        .then(response => {
            if (response.ok) {
                showAlert('Registrations rejected successfully', 'success');
                selectedRegistrations = [];
                loadRegistrations();
            } else {
                showAlert('Error rejecting registrations', 'error');
            }
        })
        .catch(error => showAlert('Error: ' + error.message, 'error'));
    }
    
    // Event listeners for filters
    document.getElementById('statusFilter').addEventListener('change', loadRegistrations);
    document.getElementById('eventFilter').addEventListener('change', loadRegistrations);
    document.getElementById('searchInput').addEventListener('keyup', loadRegistrations);
    
    // Load on page load
    loadEvents();
    loadRegistrations();
</script>
@endpush
@endsection
