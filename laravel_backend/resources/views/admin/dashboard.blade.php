@extends('admin.layout')

@section('title', 'Admin Dashboard')

@section('content')
@php
    $summaryCards = [
        [
            'label' => 'Total Events',
            'value' => $totalEvents,
            'meta' => number_format($upcomingEvents) . ' upcoming',
            'tone' => 'bg-brand-50 text-brand-700 ring-brand-100',
            'icon' => 'calendar',
        ],
        [
            'label' => 'Students',
            'value' => $registeredStudents,
            'meta' => 'Registered accounts',
            'tone' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
            'icon' => 'users',
        ],
        [
            'label' => 'Pending',
            'value' => $pendingRegistrations,
            'meta' => 'Registrations to review',
            'tone' => 'bg-amber-50 text-amber-700 ring-amber-100',
            'icon' => 'clipboard',
        ],
        [
            'label' => 'Completed',
            'value' => $completedEvents,
            'meta' => 'Ready for reporting',
            'tone' => 'bg-violet-50 text-violet-700 ring-violet-100',
            'icon' => 'check',
        ],
    ];

    $quickActions = [
        [
            'label' => 'Create Event',
            'description' => 'Schedule a new activity',
            'route' => route('admin.events.create'),
            'icon' => 'plus',
            'tone' => 'text-brand-700 bg-brand-50 ring-brand-100',
        ],
        [
            'label' => 'Review Queue',
            'description' => 'Approve registrations',
            'route' => route('admin.registrations'),
            'icon' => 'clipboard',
            'tone' => 'text-amber-700 bg-amber-50 ring-amber-100',
        ],
        [
            'label' => 'Reports',
            'description' => 'Analyze performance',
            'route' => route('admin.reports.index'),
            'icon' => 'chart',
            'tone' => 'text-violet-700 bg-violet-50 ring-violet-100',
        ],
    ];
@endphp

<div class="space-y-8">
    <section class="overflow-hidden rounded-[1.75rem] border border-brand-100 bg-white shadow-panel">
        <div class="grid gap-0 xl:grid-cols-[1.4fr_0.6fr]">
            <div class="bg-gradient-to-br from-brand-950 via-brand-900 to-brand-700 p-8 text-white">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-accent-100">Admin command center</p>
                <h1 class="mt-4 text-4xl font-bold tracking-tight">Admin Dashboard</h1>
                <p class="mt-4 max-w-2xl text-sm leading-7 text-blue-100">
                    Monitor event volume, registration queues, attendance activity, and reporting readiness from one focused workspace.
                </p>
            </div>
            <div class="grid grid-cols-2 gap-4 bg-slate-950 p-8 text-white xl:block xl:space-y-5">
                <div class="rounded-2xl border border-white/10 bg-white/10 p-5">
                    <p class="text-sm font-semibold text-blue-100">Approved</p>
                    <p class="mt-2 text-3xl font-bold">{{ number_format($approvedRegistrations) }}</p>
                    <p class="mt-1 text-xs text-blue-100/80">Ready for attendance</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/10 p-5">
                    <p class="text-sm font-semibold text-blue-100">Attendance</p>
                    <p class="mt-2 text-3xl font-bold">{{ number_format($totalAttendance) }}</p>
                    <p class="mt-1 text-xs text-blue-100/80">Recorded check-ins</p>
                </div>
            </div>
        </div>
    </section>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach($summaryCards as $card)
            <article class="rounded-[1.35rem] border border-brand-100 bg-white p-6 shadow-sm transition duration-200 hover:-translate-y-1 hover:shadow-panel">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">{{ $card['label'] }}</p>
                        <p class="mt-3 text-4xl font-bold tracking-tight text-slate-950">{{ number_format($card['value']) }}</p>
                        <p class="mt-2 text-sm text-slate-500">{{ $card['meta'] }}</p>
                    </div>
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl ring-1 {{ $card['tone'] }}">
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

    <section class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
        <div class="rounded-[1.35rem] border border-brand-100 bg-white p-6 shadow-sm">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Shortcuts</p>
                    <h2 class="mt-2 text-2xl font-bold text-slate-950">Quick Actions</h2>
                </div>
            </div>

            <div class="mt-5 grid gap-3 md:grid-cols-3">
                @foreach($quickActions as $action)
                    <a href="{{ $action['route'] }}" class="group rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition duration-200 hover:-translate-y-1 hover:border-accent-200 hover:shadow-panel focus:outline-none focus:ring-4 focus:ring-accent-100">
                        <div class="flex items-start gap-4">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl ring-1 {{ $action['tone'] }}">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
                            <span>
                                <span class="block font-bold text-slate-950">{{ $action['label'] }}</span>
                                <span class="mt-1 block text-sm leading-5 text-slate-500">{{ $action['description'] }}</span>
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>

        <div class="rounded-[1.35rem] border border-brand-100 bg-white p-6 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">System Snapshot</p>
            <h2 class="mt-2 text-2xl font-bold text-slate-950">Registration Flow</h2>
            <div class="mt-6 grid gap-4 sm:grid-cols-3">
                <div class="rounded-2xl bg-slate-50 p-5">
                    <p class="text-sm font-semibold text-slate-500">Total</p>
                    <p class="mt-2 text-3xl font-bold text-slate-950">{{ number_format($totalRegistrations) }}</p>
                </div>
                <div class="rounded-2xl bg-amber-50 p-5">
                    <p class="text-sm font-semibold text-amber-700">Pending</p>
                    <p class="mt-2 text-3xl font-bold text-amber-800">{{ number_format($pendingRegistrations) }}</p>
                </div>
                <div class="rounded-2xl bg-emerald-50 p-5">
                    <p class="text-sm font-semibold text-emerald-700">Approved</p>
                    <p class="mt-2 text-3xl font-bold text-emerald-800">{{ number_format($approvedRegistrations) }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="overflow-hidden rounded-[1.35rem] border border-brand-100 bg-white shadow-sm">
        <div class="flex flex-col gap-2 border-b border-slate-200 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Latest Records</p>
                <h2 class="mt-1 text-xl font-bold text-slate-950">Recent Events</h2>
            </div>
            <a href="{{ route('admin.events.index') }}" class="rounded-xl border border-brand-100 px-4 py-2 text-sm font-semibold text-brand-700 transition hover:bg-brand-50">
                View all events
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.16em] text-slate-500">Title</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.16em] text-slate-500">Hosted By</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.16em] text-slate-500">Schedule</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.16em] text-slate-500">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.16em] text-slate-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($recentEvents as $event)
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
                        <tr class="transition hover:bg-slate-50">
                            <td class="px-6 py-4 text-sm font-semibold text-slate-900">{{ $event->title }}</td>
                            <td class="px-6 py-4 text-sm text-slate-600">{{ $event->organization ?? 'Not specified' }}</td>
                            <td class="px-6 py-4 text-sm text-slate-600">{{ $event->start_date->format('M d, Y h:i A') }}</td>
                            <td class="px-6 py-4 text-sm">
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">
                                    {{ ucfirst($event->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <a href="{{ route('admin.events.show', $event) }}" class="rounded-xl border border-slate-200 px-3 py-2 font-semibold text-slate-700 transition hover:bg-slate-100">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-sm text-slate-500">No events yet</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
