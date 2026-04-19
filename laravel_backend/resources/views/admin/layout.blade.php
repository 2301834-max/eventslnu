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
                            900: '#081c4d',
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
    @stack('head')
</head>
<body class="min-h-screen bg-mist-50 text-slate-900">
    <div class="flex min-h-screen">
        <aside class="relative hidden w-80 shrink-0 overflow-hidden border-r border-brand-900/20 bg-gradient-to-b from-brand-900 via-brand-700 to-brand-600 text-slate-100 lg:block">
            <div class="pointer-events-none absolute -right-12 top-10 h-40 w-40 rounded-full bg-accent-400/30 blur-3xl"></div>
            <div class="pointer-events-none absolute bottom-10 left-0 h-36 w-36 rounded-full bg-white/10 blur-3xl"></div>

            <div class="border-b border-white/10 px-6 py-8">
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-accent-100">Admin Console</p>
                <a href="{{ route('admin.dashboard') }}" class="mt-3 block text-2xl font-semibold tracking-tight text-white">
                    LNU Smart Events
                </a>
                <p class="mt-3 text-sm leading-6 text-blue-100/85">
                    A focused command center for campus events, live registrations, and reporting.
                </p>
                <div class="mt-6 rounded-3xl border border-white/15 bg-white/10 px-4 py-4 backdrop-blur">
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-accent-100">LNU Theme</p>
                    <p class="mt-2 text-sm leading-6 text-blue-50/85">
                        Blue for control, yellow for motion, and cleaner data surfaces for faster admin work.
                    </p>
                </div>
            </div>

            <nav class="space-y-8 px-4 py-6">
                <div>
                    <p class="px-3 text-xs font-semibold uppercase tracking-[0.3em] text-blue-200/70">Overview</p>
                    <div class="mt-3 space-y-1">
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center justify-between rounded-2xl px-4 py-3 transition {{ request()->routeIs('admin.dashboard') ? 'bg-white text-brand-700 shadow-glow' : 'text-blue-50/90 hover:bg-white/10 hover:text-white' }}">
                            <span>Dashboard</span>
                            <span class="text-xs uppercase tracking-[0.25em]">Home</span>
                        </a>
                        <a href="{{ route('admin.reports.index') }}" class="flex items-center justify-between rounded-2xl px-4 py-3 transition {{ request()->routeIs('admin.reports.*') ? 'bg-white text-brand-700 shadow-glow' : 'text-blue-50/90 hover:bg-white/10 hover:text-white' }}">
                            <span>Reports Dashboard</span>
                            <span class="text-xs uppercase tracking-[0.25em]">Data</span>
                        </a>
                    </div>
                </div>

                <div>
                    <p class="px-3 text-xs font-semibold uppercase tracking-[0.3em] text-blue-200/70">Management</p>
                    <div class="mt-3 space-y-1">
                        <a href="{{ route('admin.events.index') }}" class="flex items-center justify-between rounded-2xl px-4 py-3 transition {{ request()->routeIs('admin.events.index') ? 'bg-white text-brand-700 shadow-glow' : 'text-blue-50/90 hover:bg-white/10 hover:text-white' }}">
                            <span>Event List</span>
                            <span class="text-xs uppercase tracking-[0.25em]">Events</span>
                        </a>
                        <a href="{{ route('admin.events.create') }}" class="flex items-center justify-between rounded-2xl px-4 py-3 transition {{ request()->routeIs('admin.events.create') ? 'bg-white text-brand-700 shadow-glow' : 'text-blue-50/90 hover:bg-white/10 hover:text-white' }}">
                            <span>Register Event</span>
                            <span class="text-xs uppercase tracking-[0.25em]">New</span>
                        </a>
                        <a href="{{ route('admin.students.index') }}" class="flex items-center justify-between rounded-2xl px-4 py-3 transition {{ request()->routeIs('admin.students.*') ? 'bg-white text-brand-700 shadow-glow' : 'text-blue-50/90 hover:bg-white/10 hover:text-white' }}">
                            <span>Students</span>
                            <span class="text-xs uppercase tracking-[0.25em]">Users</span>
                        </a>
                        <a href="{{ route('admin.registrations') }}" class="flex items-center justify-between rounded-2xl px-4 py-3 transition {{ request()->routeIs('admin.registrations') ? 'bg-white text-brand-700 shadow-glow' : 'text-blue-50/90 hover:bg-white/10 hover:text-white' }}">
                            <span>Manage Registrations</span>
                            <span class="text-xs uppercase tracking-[0.25em]">Queue</span>
                        </a>
                    </div>
                </div>
            </nav>
        </aside>

        <div class="flex min-h-screen flex-1 flex-col">
            <header class="border-b border-brand-100 bg-white/85 backdrop-blur">
                <div class="mx-auto flex max-w-7xl flex-col gap-4 px-6 py-5 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-brand-600">Administration</p>
                        <h1 class="mt-2 text-2xl font-semibold tracking-tight text-slate-900">@yield('title', 'Admin Dashboard')</h1>
                    </div>
                    <div class="flex items-center gap-4 rounded-3xl border border-brand-100 bg-gradient-to-r from-white to-accent-50 px-4 py-3 shadow-sm">
                        <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-100 to-accent-100 text-sm font-bold text-brand-700">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-900">{{ auth()->user()->name }}</p>
                            <p class="text-xs uppercase tracking-[0.25em] text-brand-600">Administrator</p>
                        </div>
                        <form method="POST" action="{{ route('logout') }}" class="ml-2">
                            @csrf
                            <button type="submit" class="rounded-xl border border-accent-200 bg-white px-4 py-2 text-sm font-semibold text-brand-700 transition hover:border-brand-200 hover:bg-brand-50">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <main class="flex-1 bg-[radial-gradient(circle_at_top_left,_rgba(255,205,56,0.14),transparent_22%),linear-gradient(180deg,_#f7faff_0%,_#ffffff_48%,_#edf3fb_100%)]">
                <div class="mx-auto max-w-7xl px-6 py-8">
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
</body>
</html>
