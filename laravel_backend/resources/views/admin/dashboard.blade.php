@extends('admin.layout')

@section('title', 'Admin Dashboard')

@section('content')
@php
    $summaryCards = [
        [
            'label' => 'Total Events',
            'value' => $totalEvents,
            'meta' => $upcomingEvents . ' upcoming',
            'tone' => 'bg-brand-50 text-brand-700 ring-brand-100',
            'icon' => 'calendar',
        ],
        [
            'label' => 'Registered Students',
            'value' => $registeredStudents,
            'meta' => 'Student accounts',
            'tone' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
            'icon' => 'users',
        ],
        [
            'label' => 'Pending Registrations',
            'value' => $pendingRegistrations,
            'meta' => 'Need review',
            'tone' => 'bg-amber-50 text-amber-700 ring-amber-100',
            'icon' => 'clipboard',
        ],
        [
            'label' => 'Completed Events',
            'value' => $completedEvents,
            'meta' => 'Ready for reports',
            'tone' => 'bg-accent-50 text-accent-600 ring-accent-100',
            'icon' => 'check',
        ],
    ];

    $quickActions = [
        [
            'label' => 'Create New Event',
            'description' => 'Plan a new campus activity',
            'route' => route('admin.events.create'),
            'icon' => 'plus',
            'tone' => 'text-brand-700 bg-brand-50 ring-brand-100',
        ],
        [
            'label' => 'Register Student',
            'description' => 'Add a student profile',
            'route' => route('admin.students.create'),
            'icon' => 'user-plus',
            'tone' => 'text-emerald-700 bg-emerald-50 ring-emerald-100',
        ],
        [
            'label' => 'Review Registrations',
            'description' => 'Approve or reject requests',
            'route' => route('admin.registrations'),
            'icon' => 'clipboard',
            'tone' => 'text-amber-700 bg-amber-50 ring-amber-100',
        ],
        [
            'label' => 'View Reports Dashboard',
            'description' => 'Analyze event performance',
            'route' => route('admin.reports.index'),
            'icon' => 'chart',
            'tone' => 'text-accent-600 bg-accent-50 ring-accent-100',
        ],
    ];
@endphp

<div class="space-y-8">
    <section class="overflow-hidden rounded-3xl border border-brand-100 bg-gradient-to-br from-brand-950 via-brand-900 to-brand-700 p-6 text-white shadow-panel sm:p-8">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="max-w-3xl">
                <p class="text-sm font-semibold text-accent-100">Admin Dashboard</p>
                <h1 class="mt-3 text-3xl font-bold tracking-tight text-white sm:text-4xl">Overview</h1>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-blue-100">
                    Monitor events, registration queues, and reporting activity from a cleaner command center.
                </p>
            </div>
            <div class="grid grid-cols-2 gap-3 rounded-3xl border border-white/15 bg-white/10 p-4 text-sm shadow-inner sm:min-w-80">
                <div>
                    <p class="font-semibold text-blue-100">Approved</p>
                    <p class="mt-1 text-2xl font-bold text-white">{{ $approvedRegistrations }}</p>
                </div>
                <div>
                    <p class="font-semibold text-blue-100">Attendance</p>
                    <p class="mt-1 text-2xl font-bold text-white">{{ $totalAttendance }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach($summaryCards as $card)
            <article class="group rounded-3xl border border-brand-100 bg-white p-6 shadow-sm transition duration-200 hover:-translate-y-1 hover:border-accent-200 hover:shadow-panel">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold text-slate-500">{{ $card['label'] }}</p>
                        <p class="mt-3 text-4xl font-bold tracking-tight text-slate-950">{{ number_format($card['value']) }}</p>
                        <p class="mt-2 text-sm text-slate-500">{{ $card['meta'] }}</p>
                    </div>
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl ring-1 {{ $card['tone'] }} transition group-hover:scale-105">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            @switch($card['icon'])
                                @case('calendar')
                                    <path d="M8 2v4"/><path d="M16 2v4"/><path d="M3 10h18"/><rect x="3" y="4" width="18" height="18" rx="2"/>
                                    @break
                                @case('users')
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                                    @break
                                @case('clipboard')
                                    <rect x="5" y="5" width="14" height="17" rx="2"/><path d="M9 5a3 3 0 0 1 6 0"/><path d="M9 13h6"/><path d="M9 17h4"/>
                                    @break
                                @case('check')
                                    <path d="m9 12 2 2 4-5"/><circle cx="12" cy="12" r="10"/>
                                    @break
                            @endswitch
                        </svg>
                    </div>
                </div>
            </article>
        @endforeach
    </section>

    <section class="rounded-3xl border border-brand-100 bg-white p-6 shadow-sm sm:p-8">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold text-slate-500">Shortcuts</p>
                <h2 class="mt-1 text-2xl font-bold text-slate-950">Quick Actions</h2>
            </div>
            <p class="text-sm text-slate-500">Fast paths for common admin workflows.</p>
        </div>

        <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach($quickActions as $action)
                <a href="{{ $action['route'] }}" class="group rounded-3xl border border-slate-200 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-1 hover:border-accent-200 hover:shadow-panel focus:outline-none focus:ring-4 focus:ring-accent-100">
                    <div class="flex items-start gap-4">
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl ring-1 {{ $action['tone'] }} transition group-hover:scale-105">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                @switch($action['icon'])
                                    @case('plus')
                                        <path d="M12 5v14"/><path d="M5 12h14"/>
                                        @break
                                    @case('user-plus')
                                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6"/><path d="M22 11h-6"/>
                                        @break
                                    @case('clipboard')
                                        <path d="M9 5h6"/><path d="M9 3h6v4H9z"/><rect x="5" y="5" width="14" height="17" rx="2"/><path d="m9 15 2 2 4-5"/>
                                        @break
                                    @case('chart')
                                        <path d="M3 3v18h18"/><path d="M8 17V9"/><path d="M13 17V5"/><path d="M18 17v-6"/>
                                        @break
                                @endswitch
                            </svg>
                        </span>
                        <span class="min-w-0">
                            <span class="block text-base font-bold text-slate-950">{{ $action['label'] }}</span>
                            <span class="mt-1 block text-sm leading-5 text-slate-500">{{ $action['description'] }}</span>
                        </span>
                    </div>
                </a>
            @endforeach
        </div>
    </section>

    <section class="overflow-hidden rounded-3xl border border-brand-100 bg-white shadow-sm">
        <div class="flex flex-col gap-2 border-b border-slate-200 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-950">Recent Events</h2>
                <p class="mt-1 text-sm text-slate-500">Latest event records across the system.</p>
            </div>
            <a href="{{ route('admin.events.index') }}" class="text-sm font-semibold text-brand-700 transition hover:text-brand-900">
                View all events
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.18em] text-slate-500">Title</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.18em] text-slate-500">Hosted By</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.18em] text-slate-500">Start Date</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.18em] text-slate-500">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.18em] text-slate-500">Registrations</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.18em] text-slate-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($recentEvents as $event)
                    <tr class="transition hover:bg-slate-50">
                        <td class="px-6 py-4 text-sm font-semibold text-slate-900">{{ $event->title }}</td>
                        <td class="px-6 py-4 text-sm text-slate-600">{{ $event->organization ?? 'Not specified' }}</td>
                        <td class="px-6 py-4 text-sm text-slate-600">{{ $event->start_date->format('M d, Y') }}</td>
                        <td class="px-6 py-4 text-sm">
                            @php
                                $statusClass = match ($event->status) {
                                    'draft' => 'bg-slate-100 text-slate-700',
                                    'published' => 'bg-blue-100 text-blue-700',
                                    'ongoing' => 'bg-emerald-100 text-emerald-700',
                                    'completed' => 'bg-violet-100 text-violet-700',
                                    'cancelled' => 'bg-rose-100 text-rose-700',
                                    default => 'bg-slate-100 text-slate-700',
                                };
                            @endphp
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">
                                {{ ucfirst($event->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm font-semibold text-slate-700">{{ $event->registrations()->count() }}</td>
                        <td class="px-6 py-4 text-sm">
                            <a href="{{ route('admin.events.show', $event) }}" class="rounded-xl border border-slate-200 px-3 py-2 font-semibold text-slate-700 transition hover:bg-slate-100">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-sm text-slate-500">No events yet</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
