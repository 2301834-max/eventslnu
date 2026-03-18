<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') - LNU Event Management</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            color: #333;
        }

        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 1rem 2rem;
            color: white;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: bold;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: white;
            padding: 2rem 0;
            box-shadow: 2px 0 10px rgba(0,0,0,0.05);
            position: fixed;
            height: calc(100vh - 60px);
            overflow-y: auto;
            top: 60px;
        }

        .main-content {
            flex: 1;
            margin-left: 250px;
            margin-top: 60px;
            padding: 2rem;
        }

        .sidebar-menu {
            list-style: none;
        }

        .sidebar-menu li {
            margin: 0;
        }

        .sidebar-menu a {
            display: block;
            padding: 1rem 1.5rem;
            color: #666;
            text-decoration: none;
            border-left: 4px solid transparent;
            transition: all 0.3s ease;
        }

        .sidebar-menu a:hover {
            background-color: #f5f7fa;
            color: #667eea;
            border-left-color: #667eea;
        }

        .sidebar-menu a.active {
            background-color: #f5f7fa;
            color: #667eea;
            border-left-color: #667eea;
            font-weight: 600;
        }

        .page-title {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 2rem;
            color: #333;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .stat-label {
            color: #999;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            font-weight: 600;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
        }

        .stat-card.blue { border-top: 4px solid #667eea; }
        .stat-card.green { border-top: 4px solid #48bb78; }
        .stat-card.orange { border-top: 4px solid #f6ad55; }
        .stat-card.red { border-top: 4px solid #fc8181; }

        .table-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background-color: #f5f7fa;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: #666;
            border-bottom: 2px solid #e2e8f0;
        }

        td {
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
        }

        tr:hover {
            background-color: #f9fafb;
        }

        .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .badge-success {
            background-color: #c6f6d5;
            color: #22543d;
        }

        .badge-warning {
            background-color: #feebc8;
            color: #7c2d12;
        }

        .badge-danger {
            background-color: #fed7d7;
            color: #742a2a;
        }

        .badge-info {
            background-color: #bee3f8;
            color: #2c5282;
        }

        .btn {
            display: inline-block;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .btn-primary {
            background-color: #667eea;
            color: white;
        }

        .btn-primary:hover {
            background-color: #5568d3;
        }

        .btn-secondary {
            background-color: #e2e8f0;
            color: #333;
        }

        .btn-secondary:hover {
            background-color: #cbd5e0;
        }

        .btn-success {
            background-color: #48bb78;
            color: white;
        }

        .btn-success:hover {
            background-color: #38a169;
        }

        .btn-danger {
            background-color: #fc8181;
            color: white;
        }

        .btn-danger:hover {
            background-color: #f56565;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            font-size: 1rem;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal.show {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 8px;
            padding: 2rem;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }

        .modal-header {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #999;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 2rem;
        }

        .pagination a,
        .pagination span {
            padding: 0.5rem 0.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            text-decoration: none;
            color: #667eea;
        }

        .pagination a:hover {
            background-color: #667eea;
            color: white;
        }

        .pagination span.active {
            background-color: #667eea;
            color: white;
            border-color: #667eea;
        }

        .alert {
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1.5rem;
        }

        .alert-success {
            background-color: #c6f6d5;
            color: #22543d;
            border-left: 4px solid #48bb78;
        }

        .alert-error {
            background-color: #fed7d7;
            color: #742a2a;
            border-left: 4px solid #fc8181;
        }

        .loader {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 0;
                overflow: hidden;
            }

            .main-content {
                margin-left: 0;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="navbar-brand">📊 LNU Event Management Admin</div>
    </div>

    <div class="container">
        <div class="sidebar">
            <ul class="sidebar-menu">
                <li><a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">📈 Dashboard</a></li>
                <li><a href="{{ route('dashboard.events') }}" class="{{ request()->routeIs('dashboard.events') ? 'active' : '' }}">🎯 Events</a></li>
                <li><a href="{{ route('dashboard.registrations') }}" class="{{ request()->routeIs('dashboard.registrations') ? 'active' : '' }}">📝 Registrations</a></li>
                <li><a href="{{ route('dashboard.attendance') }}" class="{{ request()->routeIs('dashboard.attendance') ? 'active' : '' }}">✅ Attendance</a></li>
                <li><a href="{{ route('dashboard.reports') }}" class="{{ request()->routeIs('dashboard.reports') ? 'active' : '' }}">📊 Reports</a></li>
            </ul>
        </div>

        <div class="main-content">
            @yield('content')
        </div>
    </div>

    <script>
        // API Helper
        const API = {
            token: localStorage.getItem('api_token') || '',
            baseURL: '/api',

            async request(method, endpoint, data = null) {
                const options = {
                    method,
                    headers: {
                        'Authorization': `Bearer ${this.token}`,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    }
                };

                if (data) options.body = JSON.stringify(data);

                try {
                    const response = await fetch(`${this.baseURL}${endpoint}`, options);
                    const result = await response.json();
                    if (!response.ok) throw new Error(result.message);
                    return result;
                } catch (error) {
                    showAlert(error.message, 'error');
                    throw error;
                }
            },

            async get(endpoint) { return this.request('GET', endpoint); },
            async post(endpoint, data) { return this.request('POST', endpoint, data); },
            async put(endpoint, data) { return this.request('PUT', endpoint, data); },
            async delete(endpoint) { return this.request('DELETE', endpoint); }
        };

        // Utility functions
        function showAlert(message, type = 'success') {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type}`;
            alertDiv.textContent = message;
            
            const container = document.querySelector('.main-content');
            container.insertBefore(alertDiv, container.firstChild);

            setTimeout(() => alertDiv.remove(), 5000);
        }

        function formatDate(dateString) {
            return new Date(dateString).toLocaleString();
        }

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

        // Initialize API token from session or localStorage
        function initializeToken() {
            // First check if we have a token from the session (after login)
            const sessionToken = @json(session('api_token') ?? null);
            
            if (sessionToken) {
                localStorage.setItem('api_token', sessionToken);
                API.token = sessionToken;
            } else {
                // Otherwise check localStorage
                const token = localStorage.getItem('api_token');
                if (token) {
                    API.token = token;
                }
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', initializeToken);
    </script>

    @stack('scripts')
</body>
</html>
