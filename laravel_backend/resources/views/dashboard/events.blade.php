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
                <label for="eventOrganization">Organization *</label>
                <input type="text" id="eventOrganization" name="organization" required style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px;">
            </div>
            <div style="margin-bottom: 1rem;">
                <label for="eventDescription">Description</label>
                <textarea id="eventDescription" name="description" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; min-height: 100px;"></textarea>
            </div>
            <div style="margin-bottom: 1rem;">
                <label for="eventImage">Event Poster</label>
                <img id="eventImagePreview" src="{{ asset('images/event-placeholder.svg') }}" alt="Event poster preview" style="display: block; width: 100%; height: 160px; object-fit: cover; border-radius: 6px; border: 1px solid #eee; margin-bottom: 0.75rem;">
                <input type="file" id="eventImage" name="event_image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" style="width: 100%; padding: 0.75rem; border: 1px dashed #ddd; border-radius: 4px;">
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
            
            let html = '<table><thead><tr><th>Poster</th><th>Title</th><th>Organization</th><th>Location</th><th>Capacity</th><th>Start Date</th><th>Status</th><th>Actions</th></tr></thead><tbody>';
            
            events.forEach(event => {
                const startDate = new Date(event.start_date).toLocaleString();
                const statusColor = event.status === 'draft' ? 'info' : event.status === 'published' ? 'success' : 'warning';
                
                html += `<tr>
                    <td><img src="${event.event_image_url}" alt="${event.title} poster" style="width: 72px; height: 48px; object-fit: cover; border-radius: 6px;"></td>
                    <td><strong>${event.title}</strong></td>
                    <td>${event.organization || 'Not specified'}</td>
                    <td>${event.location}</td>
                    <td>${event.max_participants}</td>
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
        document.getElementById('eventImagePreview').src = '{{ asset('images/event-placeholder.svg') }}';
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
        
        const formData = new FormData();
        formData.append('title', document.getElementById('eventTitle').value);
        formData.append('organization', document.getElementById('eventOrganization').value);
        formData.append('description', document.getElementById('eventDescription').value);
        formData.append('location', document.getElementById('eventLocation').value);
        formData.append('max_participants', parseInt(document.getElementById('eventCapacity').value));
        formData.append('start_date', new Date(document.getElementById('eventStartDate').value).toISOString());
        formData.append('end_date', new Date(document.getElementById('eventEndDate').value).toISOString());

        const poster = document.getElementById('eventImage').files[0];
        if (poster) {
            formData.append('event_image', poster);
        }
        
        fetch(`${API_BASE_URL}/events`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`,
                'Accept': 'application/json'
            },
            body: formData
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

    document.getElementById('eventImage').addEventListener('change', function() {
        const file = this.files[0];
        const preview = document.getElementById('eventImagePreview');

        if (!file) {
            preview.src = '{{ asset('images/event-placeholder.svg') }}';
            return;
        }

        preview.src = URL.createObjectURL(file);
        preview.onload = () => URL.revokeObjectURL(preview.src);
    });
    
    // Load events on page load
    loadEvents();
</script>
@endpush
@endsection
