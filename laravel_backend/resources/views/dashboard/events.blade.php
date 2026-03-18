@extends('dashboard.layout')

@section('title', 'Events Management')

@section('content')
<div class="page-title">Events Management</div>

<div style="display: flex; gap: 1rem; margin-bottom: 2rem; align-items: center;">
    <button class="btn btn-success" onclick="showCreateEventModal()">+ New Event</button>
    <input type="text" id="searchInput" placeholder="Search events..." style="padding: 0.75rem 1rem; border: 1px solid #ddd; border-radius: 4px; flex: 1; max-width: 400px;">
</div>

<div style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    <div id="eventsTableContainer">
        <p style="text-align: center; color: #999;">Loading events...</p>
    </div>
</div>

<!-- Create Event Modal -->
<div id="createEventModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 2rem; border-radius: 8px; width: 90%; max-width: 500px;">
        <h3 style="margin-bottom: 1.5rem;">Create New Event</h3>
        <form id="createEventForm">
            <div style="margin-bottom: 1rem;">
                <label for="eventTitle">Event Title *</label>
                <input type="text" id="eventTitle" name="title" required style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px;">
            </div>
            <div style="margin-bottom: 1rem;">
                <label for="eventDescription">Description</label>
                <textarea id="eventDescription" name="description" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; min-height: 100px;"></textarea>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div>
                    <label for="eventLocation">Location *</label>
                    <input type="text" id="eventLocation" name="location" required style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px;">
                </div>
                <div>
                    <label for="eventCapacity">Capacity *</label>
                    <input type="number" id="eventCapacity" name="capacity" required min="1" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px;">
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div>
                    <label for="eventStartDate">Start Date *</label>
                    <input type="datetime-local" id="eventStartDate" name="start_date" required style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px;">
                </div>
                <div>
                    <label for="eventEndDate">End Date *</label>
                    <input type="datetime-local" id="eventEndDate" name="end_date" required style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px;">
                </div>
            </div>
            <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                <button type="button" class="btn btn-secondary" onclick="closeCreateEventModal()">Cancel</button>
                <button type="submit" class="btn btn-success">Create Event</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    const API_BASE_URL = '/api';
    
    function loadEvents() {
        const searchTerm = document.getElementById('searchInput').value;
        const url = `${API_BASE_URL}/events${searchTerm ? '?search=' + encodeURIComponent(searchTerm) : ''}`;
        
        fetch(url, {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            const events = data.data || data;
            const container = document.getElementById('eventsTableContainer');
            
            if (!Array.isArray(events) || events.length === 0) {
                container.innerHTML = '<p style="text-align: center; color: #999;">No events found.</p>';
                return;
            }
            
            let html = '<table><thead><tr><th>Title</th><th>Location</th><th>Capacity</th><th>Start Date</th><th>Status</th><th>Actions</th></tr></thead><tbody>';
            
            events.forEach(event => {
                const startDate = new Date(event.start_date).toLocaleString();
                const statusColor = event.status === 'draft' ? 'info' : event.status === 'published' ? 'success' : 'warning';
                
                html += `<tr>
                    <td><strong>${event.title}</strong></td>
                    <td>${event.location}</td>
                    <td>${event.capacity}</td>
                    <td>${startDate}</td>
                    <td><span class="badge badge-${statusColor}">${event.status}</span></td>
                    <td>
                        <a href="/dashboard/events/${event.id}" style="color: #007bff; text-decoration: none; margin-right: 1rem;">View</a>
                        <button onclick="editEvent(${event.id})" class="btn btn-sm btn-primary">Edit</button>
                        <button onclick="deleteEvent(${event.id})" class="btn btn-sm btn-danger">Delete</button>
                    </td>
                </tr>`;
            });
            
            html += '</tbody></table>';
            container.innerHTML = html;
        })
        .catch(error => {
            console.error('Error loading events:', error);
            showAlert('Error loading events: ' + error.message, 'error');
        });
    }
    
    function showCreateEventModal() {
        document.getElementById('createEventModal').style.display = 'flex';
    }
    
    function closeCreateEventModal() {
        document.getElementById('createEventModal').style.display = 'none';
        document.getElementById('createEventForm').reset();
    }
    
    function deleteEvent(eventId) {
        if (!confirm('Are you sure you want to delete this event?')) return;
        
        fetch(`${API_BASE_URL}/events/${eventId}`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (response.ok) {
                showAlert('Event deleted successfully', 'success');
                loadEvents();
            } else {
                showAlert('Error deleting event', 'error');
            }
        })
        .catch(error => showAlert('Error: ' + error.message, 'error'));
    }
    
    document.getElementById('createEventForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            title: document.getElementById('eventTitle').value,
            description: document.getElementById('eventDescription').value,
            location: document.getElementById('eventLocation').value,
            capacity: parseInt(document.getElementById('eventCapacity').value),
            start_date: new Date(document.getElementById('eventStartDate').value).toISOString(),
            end_date: new Date(document.getElementById('eventEndDate').value).toISOString()
        };
        
        fetch(`${API_BASE_URL}/events`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(formData)
        })
        .then(response => {
            if (response.ok) {
                showAlert('Event created successfully', 'success');
                closeCreateEventModal();
                loadEvents();
            } else {
                return response.json().then(data => {
                    throw new Error(data.message || 'Error creating event');
                });
            }
        })
        .catch(error => showAlert('Error: ' + error.message, 'error'));
    });
    
    document.getElementById('searchInput').addEventListener('keyup', function() {
        loadEvents();
    });
    
    // Load events on page load
    loadEvents();
</script>
@endpush
@endsection
