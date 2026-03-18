@extends('dashboard.layout')

@section('title', 'Reports')

@section('content')
<div class="page-title">Reports</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
    <!-- Report Generation Section -->
    <div style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <h3 style="margin-bottom: 1.5rem; color: #333;">Generate Report</h3>
        
        <div style="margin-bottom: 1.5rem;">
            <label for="reportType">Report Type *</label>
            <select id="reportType" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px;">
                <option value="">Select a report type</option>
                <option value="attendance">Attendance Report (CSV)</option>
                <option value="registrations">Registrations Report (CSV)</option>
                <option value="no-shows">No-Shows Report (CSV)</option>
                <option value="location">Location Analytics (CSV)</option>
                <option value="time-analysis">Time Analysis (CSV)</option>
                <option value="summary">Summary Report (JSON)</option>
            </select>
        </div>
        
        <div style="margin-bottom: 1.5rem;">
            <label for="reportEvent">Event *</label>
            <select id="reportEvent" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px;">
                <option value="">All Events</option>
            </select>
        </div>
        
        <div style="margin-bottom: 1.5rem;">
            <label for="reportStatus">Status Filter</label>
            <select id="reportStatus" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px;">
                <option value="">All Statuses</option>
                <option value="approved">Approved Only</option>
                <option value="pending">Pending Only</option>
                <option value="rejected">Rejected Only</option>
            </select>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
            <div>
                <label for="reportStartDate">From Date</label>
                <input type="date" id="reportStartDate" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px;">
            </div>
            <div>
                <label for="reportEndDate">To Date</label>
                <input type="date" id="reportEndDate" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px;">
            </div>
        </div>
        
        <button class="btn btn-success" onclick="generateReport()" style="width: 100%;">Generate & Download</button>
    </div>
    
    <!-- Quick Access Section -->
    <div style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <h3 style="margin-bottom: 1.5rem; color: #333;">Quick Reports</h3>
        
        <button class="btn btn-primary" style="width: 100%; margin-bottom: 1rem; padding: 1rem;" onclick="quickReport('attendance')">
            <strong>Attendance Summary</strong><br>
            <span style="font-size: 0.9rem;">All attendance records</span>
        </button>
        
        <button class="btn btn-primary" style="width: 100%; margin-bottom: 1rem; padding: 1rem;" onclick="quickReport('registrations')">
            <strong>Registration Summary</strong><br>
            <span style="font-size: 0.9rem;">All approved registrations</span>
        </button>
        
        <button class="btn btn-primary" style="width: 100%; margin-bottom: 1rem; padding: 1rem;" onclick="quickReport('no-shows')">
            <strong>No-Shows Report</strong><br>
            <span style="font-size: 0.9rem;">Users who didn't attend</span>
        </button>
        
        <button class="btn btn-primary" style="width: 100%; padding: 1rem;" onclick="quickReport('summary')">
            <strong>Event Summary</strong><br>
            <span style="font-size: 0.9rem;">Overall statistics</span>
        </button>
    </div>
</div>

<!-- Reports Table -->
<div style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    <h3 style="margin-bottom: 1.5rem; color: #333;">Report Preview</h3>
    <div id="reportPreview">
        <p style="text-align: center; color: #999;">Select a report type to preview</p>
    </div>
</div>

@push('scripts')
<script>
    const API_BASE_URL = '/api';
    const token = localStorage.getItem('token');
    
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
            const select = document.getElementById('reportEvent');
            
            events.forEach(event => {
                const option = document.createElement('option');
                option.value = event.id;
                option.textContent = event.title;
                select.appendChild(option);
            });
        })
        .catch(error => console.error('Error loading events:', error));
    }
    
    function generateReport() {
        const reportType = document.getElementById('reportType').value;
        if (!reportType) {
            showAlert('Please select a report type', 'warning');
            return;
        }
        
        const eventId = document.getElementById('reportEvent').value;
        const status = document.getElementById('reportStatus').value;
        const startDate = document.getElementById('reportStartDate').value;
        const endDate = document.getElementById('reportEndDate').value;
        
        let endpoint = `/reports/${reportType}`;
        const params = new URLSearchParams();
        
        if (eventId) params.append('event_id', eventId);
        if (status) params.append('status', status);
        if (startDate) params.append('start_date', startDate);
        if (endDate) params.append('end_date', endDate);
        
        if (params.toString()) endpoint += '?' + params.toString();
        
        // Show preview first
        fetch(API_BASE_URL + endpoint, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (reportType === 'summary') {
                previewSummaryReport(data.data || data);
            } else {
                previewTableReport(data.data || data);
            }
            
            // Download the file
            downloadReport(reportType, params);
        })
        .catch(error => showAlert('Error generating report: ' + error.message, 'error'));
    }
    
    function quickReport(reportType) {
        document.getElementById('reportType').value = reportType;
        
        const endpoint = `/reports/${reportType}`;
        
        fetch(API_BASE_URL + endpoint, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (reportType === 'summary') {
                previewSummaryReport(data.data || data);
            } else {
                previewTableReport(data.data || data);
            }
        })
        .catch(error => showAlert('Error loading report: ' + error.message, 'error'));
    }
    
    function previewTableReport(data) {
        const container = document.getElementById('reportPreview');
        
        if (!Array.isArray(data) || data.length === 0) {
            container.innerHTML = '<p style="text-align: center; color: #999;">No data available</p>';
            return;
        }
        
        const keys = Object.keys(data[0]);
        let html = '<table style="width: 100%; font-size: 0.9rem;"><thead><tr>';
        
        keys.forEach(key => {
            html += `<th>${key}</th>`;
        });
        
        html += '</tr></thead><tbody>';
        
        data.slice(0, 10).forEach(row => {
            html += '<tr>';
            keys.forEach(key => {
                html += `<td>${row[key] || '-'}</td>`;
            });
            html += '</tr>';
        });
        
        html += '</tbody></table>';
        
        if (data.length > 10) {
            html += `<p style="text-align: center; color: #999; margin-top: 1rem;">Showing 10 of ${data.length} records</p>`;
        }
        
        container.innerHTML = html;
    }
    
    function previewSummaryReport(data) {
        const container = document.getElementById('reportPreview');
        
        let html = '<div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">';
        
        Object.entries(data).forEach(([key, value]) => {
            html += `
                <div style="background: #f5f5f5; padding: 1rem; border-radius: 4px;">
                    <p style="color: #666; font-size: 0.9rem; margin-bottom: 0.5rem;">${key}</p>
                    <p style="font-size: 1.5rem; font-weight: bold; color: #007bff;">${value}</p>
                </div>
            `;
        });
        
        html += '</div>';
        container.innerHTML = html;
    }
    
    function downloadReport(reportType, params) {
        let endpoint = `/reports/${reportType}`;
        
        if (params.toString()) endpoint += '?' + params.toString();
        
        const url = API_BASE_URL + endpoint;
        
        fetch(url, {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': reportType === 'summary' ? 'application/json' : 'text/csv'
            }
        })
        .then(response => response.blob())
        .then(blob => {
            const downloadUrl = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = downloadUrl;
            
            const timestamp = new Date().toISOString().split('T')[0];
            const extension = reportType === 'summary' ? 'json' : 'csv';
            a.download = `${reportType}-report-${timestamp}.${extension}`;
            
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(downloadUrl);
            document.body.removeChild(a);
            
            showAlert('Report downloaded successfully', 'success');
        })
        .catch(error => showAlert('Error downloading report: ' + error.message, 'error'));
    }
    
    // Load events on page load
    loadEvents();
</script>
@endpush
@endsection
