<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Student Dashboard') - LNU Smart Events</title>
    <style>
        :root {
            color-scheme: light;
            --brand-950: #061636;
            --brand-900: #09245a;
            --brand-700: #0b4aa2;
            --accent-500: #d99a00;
            --surface: #ffffff;
            --page: #f5f8fc;
            --line: #dce6f2;
            --muted: #66758c;
            --text: #111d33;
            --success: #047857;
            --warning: #b45309;
            --danger: #be123c;
            --info: #1d4ed8;
            --shadow: 0 18px 45px rgba(8, 29, 80, 0.10);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background:
                radial-gradient(circle at top left, rgba(217, 154, 0, 0.10), transparent 24rem),
                linear-gradient(180deg, #f8fbff 0%, var(--page) 48%, #eef4fb 100%);
            color: var(--text);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            letter-spacing: 0;
        }

        a {
            color: inherit;
        }

        .portal-shell {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 17.5rem minmax(0, 1fr);
        }

        .portal-sidebar {
            position: sticky;
            top: 0;
            height: 100vh;
            display: flex;
            flex-direction: column;
            background: linear-gradient(180deg, var(--brand-950), var(--brand-900));
            color: #fff;
            border-right: 1px solid rgba(255,255,255,0.10);
        }

        .portal-brand {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            min-height: 5.5rem;
            padding: 1.1rem 1.25rem;
            border-bottom: 1px solid rgba(255,255,255,0.10);
        }

        .portal-brand-mark {
            display: grid;
            place-items: center;
            width: 3rem;
            height: 3rem;
            border-radius: 1rem;
            background: rgba(255,255,255,0.10);
            border: 1px solid rgba(255,255,255,0.16);
            color: #ffe08a;
            font-weight: 800;
        }

        .portal-brand-title {
            font-size: 0.95rem;
            font-weight: 800;
            line-height: 1.2;
        }

        .portal-brand-subtitle {
            margin-top: 0.18rem;
            color: rgba(226, 237, 255, 0.78);
            font-size: 0.78rem;
            font-weight: 600;
        }

        .portal-nav {
            flex: 1;
            padding: 1rem;
            display: grid;
            align-content: start;
            gap: 0.45rem;
        }

        .portal-nav a {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            min-height: 3rem;
            padding: 0.75rem 0.85rem;
            border-radius: 0.9rem;
            text-decoration: none;
            color: rgba(239, 246, 255, 0.82);
            font-size: 0.92rem;
            font-weight: 750;
            transition: background 160ms ease, color 160ms ease, transform 160ms ease;
        }

        .portal-nav a:hover {
            background: rgba(255,255,255,0.10);
            color: #fff;
            transform: translateX(2px);
        }

        .portal-nav a.active {
            background: #fff;
            color: var(--brand-900);
            box-shadow: 0 14px 30px rgba(0,0,0,0.16);
        }

        .portal-nav-icon {
            display: grid;
            place-items: center;
            width: 2.1rem;
            height: 2.1rem;
            border-radius: 0.75rem;
            background: rgba(255,255,255,0.10);
            border: 1px solid rgba(255,255,255,0.10);
        }

        .portal-nav a.active .portal-nav-icon {
            background: #fff4c7;
            border-color: #ffe08a;
        }

        .portal-sidebar-footer {
            padding: 1rem;
            border-top: 1px solid rgba(255,255,255,0.10);
        }

        .portal-workspace {
            border-radius: 1rem;
            background: rgba(255,255,255,0.10);
            border: 1px solid rgba(255,255,255,0.12);
            padding: 1rem;
        }

        .portal-workspace span {
            display: block;
            color: #ffe08a;
            font-size: 0.72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.18em;
        }

        .portal-workspace strong {
            display: block;
            margin-top: 0.45rem;
            font-size: 0.9rem;
        }

        .portal-main {
            min-width: 0;
            display: flex;
            flex-direction: column;
        }

        .portal-topbar {
            position: sticky;
            top: 0;
            z-index: 20;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            min-height: 5.5rem;
            padding: 1rem 2rem;
            background: rgba(255,255,255,0.90);
            border-bottom: 1px solid var(--line);
            backdrop-filter: blur(18px);
        }

        .portal-kicker {
            color: var(--muted);
            font-size: 0.78rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.16em;
        }

        .portal-title {
            margin-top: 0.25rem;
            font-size: 1.15rem;
            font-weight: 850;
        }

        .portal-account {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            border: 1px solid var(--line);
            background: #fff;
            border-radius: 1.1rem;
            padding: 0.55rem 0.75rem;
            box-shadow: 0 8px 24px rgba(8, 29, 80, 0.06);
        }

        .portal-avatar {
            display: grid;
            place-items: center;
            width: 2.6rem;
            height: 2.6rem;
            border-radius: 0.9rem;
            background: #fff4c7;
            color: var(--brand-900);
            font-weight: 850;
        }

        .portal-account-name {
            font-size: 0.88rem;
            font-weight: 800;
        }

        .portal-account-role {
            color: var(--muted);
            font-size: 0.76rem;
            font-weight: 650;
        }

        .portal-logout {
            border: 1px solid #f3cc6b;
            background: #fff;
            border-radius: 0.8rem;
            padding: 0.65rem 0.9rem;
            color: var(--brand-900);
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 800;
        }

        .portal-content {
            width: min(100%, 82rem);
            margin: 0 auto;
            padding: 2rem;
        }

        .page-title {
            margin: 0 0 1.5rem;
            font-size: 1.8rem;
            font-weight: 850;
            letter-spacing: 0;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .stat-card,
        .table-container,
        .panel {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 1rem;
            box-shadow: 0 8px 24px rgba(8, 29, 80, 0.06);
        }

        .stat-card {
            padding: 1.25rem;
            border-top: 0;
            transition: transform 160ms ease, box-shadow 160ms ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .stat-label {
            color: var(--muted);
            font-size: 0.78rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.12em;
        }

        .stat-value {
            margin-top: 0.6rem;
            color: var(--brand-950);
            font-size: 2rem;
            font-weight: 850;
        }

        .stat-card.blue { border-left: 4px solid var(--info); }
        .stat-card.green { border-left: 4px solid var(--success); }
        .stat-card.orange { border-left: 4px solid var(--warning); }
        .stat-card.red { border-left: 4px solid var(--danger); }

        .table-container {
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #f8fafc;
            color: var(--muted);
            padding: 0.95rem 1rem;
            text-align: left;
            font-size: 0.72rem;
            font-weight: 850;
            text-transform: uppercase;
            letter-spacing: 0.14em;
            border-bottom: 1px solid var(--line);
        }

        td {
            padding: 1rem;
            border-bottom: 1px solid #edf2f7;
            color: #25324a;
            font-size: 0.92rem;
        }

        tr:hover {
            background: #f8fbff;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            min-height: 1.7rem;
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 800;
        }

        .badge-success { background: #dcfce7; color: #166534; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-danger { background: #ffe4e6; color: #9f1239; }
        .badge-info { background: #dbeafe; color: #1d4ed8; }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 2.35rem;
            padding: 0.55rem 0.9rem;
            border: 1px solid transparent;
            border-radius: 0.75rem;
            cursor: pointer;
            text-decoration: none;
            transition: background 160ms ease, border-color 160ms ease, transform 160ms ease;
            font-size: 0.85rem;
            font-weight: 800;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .btn-primary { background: var(--brand-700); color: white; }
        .btn-secondary { background: white; color: var(--brand-900); border-color: var(--line); }
        .btn-success { background: var(--success); color: white; }
        .btn-danger { background: #fff1f2; color: var(--danger); border-color: #fecdd3; }

        input,
        textarea,
        select {
            border: 1px solid var(--line);
            border-radius: 0.75rem;
            color: var(--text);
            font: inherit;
            outline: none;
        }

        input:focus,
        textarea:focus,
        select:focus {
            border-color: #93b7f5;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.12);
        }

        .modal {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 1000;
            align-items: center;
            justify-content: center;
            background: rgba(6, 22, 54, 0.55);
            backdrop-filter: blur(8px);
        }

        .modal.show {
            display: flex;
        }

        .modal-content {
            width: min(90vw, 34rem);
            border-radius: 1rem;
            background: #fff;
            padding: 1.5rem;
            box-shadow: var(--shadow);
        }

        .alert {
            margin-bottom: 1rem;
            border-radius: 0.9rem;
            padding: 0.9rem 1rem;
            font-weight: 700;
        }

        .alert-success {
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
        }

        .alert-error {
            background: #fff1f2;
            color: #be123c;
            border: 1px solid #fecdd3;
        }

        @media (max-width: 980px) {
            .portal-shell {
                grid-template-columns: 1fr;
            }

            .portal-sidebar {
                position: static;
                height: auto;
            }

            .portal-nav {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .portal-sidebar-footer {
                display: none;
            }

            .portal-topbar {
                padding: 1rem;
            }

            .portal-content {
                padding: 1rem;
            }

            .stats-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 640px) {
            .portal-nav {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .portal-account-copy {
                display: none;
            }
        }
    </style>
    @stack('head')
</head>
<body>
    @php
        $studentNavItems = [
            ['label' => 'Dashboard', 'route' => route('dashboard'), 'active' => request()->routeIs('dashboard'), 'icon' => 'M4 5h7v7H4z M13 5h7v4h-7z M13 11h7v8h-7z M4 14h7v5H4z'],
            ['label' => 'Events', 'route' => route('dashboard.events'), 'active' => request()->routeIs('dashboard.events') || request()->routeIs('dashboard.event-detail'), 'icon' => 'M8 2v4 M16 2v4 M3 10h18 M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z'],
            ['label' => 'Registrations', 'route' => route('dashboard.registrations'), 'active' => request()->routeIs('dashboard.registrations'), 'icon' => 'M9 5h6 M9 3h6v4H9z M5 5h14v17H5z M9 15l2 2 4-5'],
            ['label' => 'Attendance', 'route' => route('dashboard.attendance'), 'active' => request()->routeIs('dashboard.attendance'), 'icon' => 'M12 6v6l4 2 M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z'],
            ['label' => 'Reports', 'route' => route('dashboard.reports'), 'active' => request()->routeIs('dashboard.reports'), 'icon' => 'M3 3v18h18 M8 17V9 M13 17V5 M18 17v-6'],
        ];
    @endphp

    <div class="portal-shell">
        <aside class="portal-sidebar">
            <div class="portal-brand">
                <div class="portal-brand-mark">L</div>
                <div>
                    <div class="portal-brand-title">LNU Smart Events</div>
                    <div class="portal-brand-subtitle">Student Portal</div>
                </div>
            </div>

            <nav class="portal-nav" aria-label="Student navigation">
                @foreach($studentNavItems as $item)
                    <a href="{{ $item['route'] }}" class="{{ $item['active'] ? 'active' : '' }}">
                        <span class="portal-nav-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                @foreach(explode(' ', $item['icon']) as $path)
                                @endforeach
                                <path d="{{ $item['icon'] }}"/>
                            </svg>
                        </span>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>

            <div class="portal-sidebar-footer">
                <div class="portal-workspace">
                    <span>Workspace</span>
                    <strong>Leyte Normal University</strong>
                </div>
            </div>
        </aside>

        <div class="portal-main">
            <header class="portal-topbar">
                <div>
                    <div class="portal-kicker">Student workspace</div>
                    <div class="portal-title">@yield('title', 'Dashboard')</div>
                </div>
                <div class="portal-account">
                    <div class="portal-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'S', 0, 1)) }}</div>
                    <div class="portal-account-copy">
                        <div class="portal-account-name">{{ auth()->user()->name ?? 'Student' }}</div>
                        <div class="portal-account-role">{{ auth()->user()->student_id ?? 'Student account' }}</div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="portal-logout">Logout</button>
                    </form>
                </div>
            </header>

            <main class="portal-content">
                @yield('content')
            </main>
        </div>
    </div>

    <script>
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

                const response = await fetch(`${this.baseURL}${endpoint}`, options);
                const result = await response.json();
                if (!response.ok) throw new Error(result.message || 'Request failed');
                return result;
            },

            async get(endpoint) { return this.request('GET', endpoint); },
            async post(endpoint, data) { return this.request('POST', endpoint, data); },
            async put(endpoint, data) { return this.request('PUT', endpoint, data); },
            async delete(endpoint) { return this.request('DELETE', endpoint); }
        };

        function showAlert(message, type = 'success') {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type}`;
            alertDiv.textContent = message;

            const container = document.querySelector('.portal-content');
            container.insertBefore(alertDiv, container.firstChild);

            setTimeout(() => alertDiv.remove(), 5000);
        }

        function formatDate(dateString) {
            return new Date(dateString).toLocaleString();
        }

        function getStatusBadge(status) {
            const badges = {
                approved: 'success',
                pending: 'warning',
                rejected: 'danger',
                draft: 'info',
                published: 'info',
                ongoing: 'success',
                completed: 'success',
                cancelled: 'danger'
            };
            const badgeClass = badges[status] || 'info';
            return `<span class="badge badge-${badgeClass}">${status.charAt(0).toUpperCase() + status.slice(1)}</span>`;
        }

        function initializeToken() {
            const sessionToken = @json(session('api_token') ?? null);

            if (sessionToken) {
                localStorage.setItem('api_token', sessionToken);
                API.token = sessionToken;
                localStorage.setItem('token', sessionToken);
            } else {
                const token = localStorage.getItem('api_token') || localStorage.getItem('token');
                if (token) {
                    API.token = token;
                    localStorage.setItem('api_token', token);
                    localStorage.setItem('token', token);
                }
            }
        }

        document.addEventListener('DOMContentLoaded', initializeToken);
    </script>

    @stack('scripts')
</body>
</html>
