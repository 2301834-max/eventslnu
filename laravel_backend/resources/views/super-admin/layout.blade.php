<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Super Admin') - LNU Smart Events</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-[#f4f7fb] text-slate-900">
@php
    $nav = [
        ['label' => 'Overview', 'route' => route('super-admin.dashboard'), 'active' => request()->routeIs('super-admin.dashboard')],
        ['label' => 'Admin Accounts', 'route' => route('super-admin.admins.index'), 'active' => request()->routeIs('super-admin.admins.*')],
        ['label' => 'Activity Logs', 'route' => route('super-admin.activity-logs'), 'active' => request()->routeIs('super-admin.activity-logs')],
        ['label' => 'Events Monitoring', 'route' => route('super-admin.events-monitoring'), 'active' => request()->routeIs('super-admin.events-monitoring')],
        ['label' => 'Reports', 'route' => route('super-admin.reports'), 'active' => request()->routeIs('super-admin.reports')],
    ];
@endphp
<div class="flex min-h-screen">
    <aside class="fixed inset-y-0 left-0 hidden w-72 flex-col bg-gradient-to-b from-[#041033] via-[#071f5f] to-[#0047ab] text-white lg:flex">
        <div class="flex h-20 items-center gap-3 border-b border-white/10 px-5">
            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white p-1">
                <img src="{{ asset('images/lnu-logo.png') }}" alt="LNU" class="h-full w-full object-contain">
            </span>
            <div>
                <p class="font-bold">LNU Smart Events</p>
                <p class="text-xs text-blue-100">Super Admin</p>
            </div>
        </div>
        <nav class="flex-1 space-y-2 px-4 py-6">
            @foreach($nav as $item)
                <a href="{{ $item['route'] }}" class="block rounded-2xl px-4 py-3 text-sm font-bold transition {{ $item['active'] ? 'bg-white text-[#071f5f]' : 'text-blue-50/85 hover:bg-white/10 hover:text-white' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>
        <div class="border-t border-white/10 p-4">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="w-full rounded-2xl border border-white/10 px-4 py-3 text-sm font-bold text-blue-50 transition hover:bg-white/10">Logout</button>
            </form>
        </div>
    </aside>
    <div class="min-h-screen flex-1 lg:pl-72">
        <header class="sticky top-0 z-20 border-b border-blue-100 bg-white/90 backdrop-blur">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-5 py-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-700">Super Admin Console</p>
                    <h1 class="text-xl font-bold">@yield('title', 'Overview')</h1>
                </div>
                <div class="rounded-2xl border border-blue-100 bg-white px-4 py-2 text-sm font-bold text-slate-700 shadow-sm">
                    {{ auth()->user()->name }}
                </div>
            </div>
        </header>
        <main class="mx-auto max-w-7xl px-5 py-8">
            @if(session('success'))
                <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>
            @endif
            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
