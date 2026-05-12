<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') - LNU Smart Events System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#eef5ff',
                            100: '#d8e8ff',
                            200: '#a9cbff',
                            500: '#0056c7',
                            600: '#0047ab',
                            700: '#0a2f7a',
                            800: '#071f5f',
                            900: '#081c4d',
                            950: '#041033',
                        },
                        accent: {
                            50: '#fff9e7',
                            100: '#fff2bf',
                            200: '#ffe58a',
                            400: '#ffcd38',
                            500: '#f6b800',
                            600: '#d49a00',
                        },
                        ink: {
                            950: '#0d1b3d',
                        },
                        mist: {
                            50: '#f7faff',
                            100: '#edf3fb',
                            200: '#d9e5f4',
                        },
                    },
                    boxShadow: {
                        panel: '0 18px 45px rgba(8, 29, 80, 0.10)',
                        glow: '0 22px 50px rgba(0, 71, 171, 0.18)',
                    },
                },
            },
        };
    </script>
    <style>
        .admin-sidebar {
            transition: width 180ms ease, transform 180ms ease;
        }

        .admin-sidebar-tooltip {
            opacity: 0;
            pointer-events: none;
            transform: translateX(-4px);
            transition: opacity 150ms ease, transform 150ms ease;
        }

        body.admin-sidebar-collapsed .admin-sidebar {
            width: 5.75rem;
        }

        body.admin-sidebar-collapsed .admin-sidebar-label,
        body.admin-sidebar-collapsed .admin-sidebar-meta,
        body.admin-sidebar-collapsed .admin-sidebar-brand-copy {
            display: none;
        }

        body.admin-sidebar-collapsed .admin-sidebar-link {
            justify-content: center;
        }

        body.admin-sidebar-collapsed .admin-sidebar-link:hover .admin-sidebar-tooltip {
            opacity: 1;
            transform: translateX(0);
        }
    </style>
    @stack('head')
</head>
<body class="min-h-screen bg-mist-50 text-slate-900 antialiased">
    @php
        $adminNavItems = [
            ['label' => 'Overview', 'route' => route('admin.dashboard'), 'active' => request()->routeIs('admin.dashboard'), 'icon' => 'layout-dashboard'],
            ['label' => 'Events', 'route' => route('admin.events.index'), 'active' => request()->routeIs('admin.events.*') && ! request()->routeIs('admin.events.create'), 'icon' => 'calendar-days'],
            ['label' => 'Students', 'route' => route('admin.students.index'), 'active' => request()->routeIs('admin.students.index') || request()->routeIs('admin.students.show') || request()->routeIs('admin.students.edit'), 'icon' => 'graduation-cap'],
            ['label' => 'Registrations', 'route' => route('admin.registrations'), 'active' => request()->routeIs('admin.registrations'), 'icon' => 'clipboard-check'],
            ['label' => 'Reports', 'route' => route('admin.reports.index'), 'active' => request()->routeIs('admin.reports.*'), 'icon' => 'bar-chart'],
            ['label' => 'Users', 'route' => route('admin.students.create'), 'active' => request()->routeIs('admin.students.create'), 'icon' => 'users'],
        ];
    @endphp

    <div id="adminMobileOverlay" class="fixed inset-0 z-30 hidden bg-slate-950/40 backdrop-blur-sm lg:hidden"></div>

    <div class="flex min-h-screen">
        <aside id="adminSidebar" class="admin-sidebar fixed inset-y-0 left-0 z-40 flex h-screen w-72 -translate-x-full flex-col overflow-hidden border-r border-brand-900/30 bg-gradient-to-b from-brand-900 via-brand-800 to-brand-700 text-white shadow-2xl backdrop-blur lg:sticky lg:top-0 lg:translate-x-0 lg:shadow-none">
            <div class="flex h-20 items-center gap-3 border-b border-white/10 px-5">
                <a href="{{ route('admin.dashboard') }}" class="flex min-w-0 flex-1 items-center gap-3">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-accent-400/50 bg-white p-1 shadow-lg shadow-brand-950/25">
                        <img
                            src="{{ asset('images/lnu-logo.png') }}"
                            alt="Leyte Normal University seal"
                            class="h-full w-full object-contain"
                        >
                    </span>
                    <span class="admin-sidebar-brand-copy min-w-0">
                        <span class="block truncate text-sm font-bold text-white">LNU Smart Events</span>
                        <span class="block truncate text-xs font-medium text-blue-100/80">Admin Console</span>
                    </span>
                </a>
                <button type="button" id="adminSidebarCollapse" class="hidden rounded-xl border border-white/10 p-2 text-blue-100 transition hover:border-accent-300/60 hover:bg-white/10 hover:text-white lg:inline-flex" aria-label="Collapse sidebar">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
                </button>
                <button type="button" id="adminSidebarClose" class="rounded-xl border border-white/10 p-2 text-blue-100 transition hover:bg-white/10 lg:hidden" aria-label="Close navigation">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>
            </div>

            <nav class="flex-1 space-y-2 overflow-hidden px-4 py-5" aria-label="Admin navigation">
                @foreach($adminNavItems as $item)
                    <a
                        href="{{ $item['route'] }}"
                        class="admin-sidebar-link group relative flex items-center gap-3 rounded-2xl px-3 py-3 text-sm font-semibold transition duration-200 {{ $item['active'] ? 'bg-white text-brand-800 shadow-lg shadow-brand-950/20' : 'text-blue-50/85 hover:bg-white/10 hover:text-white' }}"
                        aria-label="{{ $item['label'] }}"
                    >
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl {{ $item['active'] ? 'bg-accent-100 text-brand-800' : 'bg-white/10 text-blue-100 ring-1 ring-white/10 group-hover:bg-accent-400/20 group-hover:text-white' }}">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                @switch($item['icon'])
                                    @case('layout-dashboard')
                                        <path d="M4 5a1 1 0 0 1 1-1h5v7H4z"/><path d="M14 4h5a1 1 0 0 1 1 1v3h-6z"/><path d="M14 12h6v7a1 1 0 0 1-1 1h-5z"/><path d="M4 15h6v5H5a1 1 0 0 1-1-1z"/>
                                        @break
                                    @case('calendar-days')
                                        <path d="M8 2v4"/><path d="M16 2v4"/><path d="M3 10h18"/><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M8 14h.01"/><path d="M12 14h.01"/><path d="M16 14h.01"/><path d="M8 18h.01"/><path d="M12 18h.01"/>
                                        @break
                                    @case('graduation-cap')
                                        <path d="M22 10 12 5 2 10l10 5z"/><path d="M6 12v5c3 2 9 2 12 0v-5"/><path d="M22 10v6"/>
                                        @break
                                    @case('clipboard-check')
                                        <path d="M9 5h6"/><path d="M9 3h6v4H9z"/><path d="M5 5h2"/><path d="M17 5h2"/><rect x="5" y="5" width="14" height="17" rx="2"/><path d="m9 15 2 2 4-5"/>
                                        @break
                                    @case('bar-chart')
                                        <path d="M3 3v18h18"/><path d="M8 17V9"/><path d="M13 17V5"/><path d="M18 17v-6"/>
                                        @break
                                    @case('list-checks')
                                        <path d="m3 7 2 2 4-4"/><path d="M11 7h10"/><path d="m3 17 2 2 4-4"/><path d="M11 17h10"/>
                                        @break
                                    @case('users')
                                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                        @break
                                @endswitch
                            </svg>
                        </span>
                        <span class="admin-sidebar-label truncate">{{ $item['label'] }}</span>
                        <span class="admin-sidebar-tooltip absolute left-full top-1/2 z-50 ml-3 -translate-y-1/2 whitespace-nowrap rounded-xl bg-brand-950 px-3 py-2 text-xs font-semibold text-white shadow-xl">
                            {{ $item['label'] }}
                        </span>
                    </a>
                @endforeach
            </nav>

            <div class="admin-sidebar-meta border-t border-white/10 p-4">
                <div class="rounded-2xl border border-white/10 bg-white/10 p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-accent-100">Workspace</p>
                    <p class="mt-2 text-sm font-bold text-white">Leyte Normal University</p>
                </div>
            </div>
        </aside>

        <div class="flex min-h-screen flex-1 flex-col">
            <header class="sticky top-0 z-20 border-b border-brand-100 bg-white/90 backdrop-blur-xl">
                <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-5 py-4 sm:px-6 lg:px-8">
                    <button type="button" id="adminSidebarOpen" class="inline-flex rounded-xl border border-brand-100 p-2 text-brand-700 transition hover:bg-brand-50 lg:hidden" aria-label="Open navigation">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16"/><path d="M4 12h16"/><path d="M4 18h16"/></svg>
                    </button>
                    <div class="flex-1"></div>
                    <div class="flex items-center gap-3 rounded-2xl border border-brand-100 bg-white px-3 py-2 shadow-sm">
                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-100 to-accent-100 text-sm font-bold text-brand-700 ring-1 ring-accent-200/70">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div class="hidden sm:block">
                            <p class="text-sm font-semibold text-slate-900">{{ auth()->user()->name }}</p>
                            <p class="text-xs font-medium text-slate-500">Administrator</p>
                        </div>
                        <form method="POST" action="{{ route('logout') }}" class="ml-2">
                            @csrf
                            <button type="submit" class="rounded-xl border border-accent-200 bg-white px-4 py-2 text-sm font-semibold text-brand-700 transition hover:border-accent-300 hover:bg-accent-50">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <main class="flex-1 bg-[radial-gradient(circle_at_top_left,_rgba(246,184,0,0.12),transparent_26%),linear-gradient(180deg,_#f7faff_0%,_#ffffff_46%,_#edf3fb_100%)]">
                <div class="mx-auto max-w-7xl px-5 py-8 sm:px-6 lg:px-8">
                    @if(session('success'))
                        <div class="mb-6 rounded-3xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-emerald-700 shadow-sm">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="mb-6 rounded-3xl border border-rose-200 bg-rose-50 px-5 py-4 text-rose-700 shadow-sm">
                            @foreach($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>
    </div>
    @stack('scripts')
    <script>
        (() => {
            const body = document.body;
            const sidebar = document.getElementById('adminSidebar');
            const overlay = document.getElementById('adminMobileOverlay');
            const openButton = document.getElementById('adminSidebarOpen');
            const closeButton = document.getElementById('adminSidebarClose');
            const collapseButton = document.getElementById('adminSidebarCollapse');

            const setMobileSidebar = (isOpen) => {
                sidebar.classList.toggle('-translate-x-full', !isOpen);
                overlay.classList.toggle('hidden', !isOpen);
            };

            openButton?.addEventListener('click', () => setMobileSidebar(true));
            closeButton?.addEventListener('click', () => setMobileSidebar(false));
            overlay?.addEventListener('click', () => setMobileSidebar(false));
            collapseButton?.addEventListener('click', () => {
                body.classList.toggle('admin-sidebar-collapsed');
            });
        })();
    </script>
</body>
</html>
