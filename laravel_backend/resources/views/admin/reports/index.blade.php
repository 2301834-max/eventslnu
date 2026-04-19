@extends('admin.layout')

@section('title', 'Reports Dashboard')

@section('content')
<div class="max-w-7xl mx-auto space-y-8">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Reports Dashboard</h1>
            <p class="mt-2 text-sm text-gray-600">
                Review event performance, registration activity, and attendance in one place.
            </p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.reports.export.pdf', request()->query()) }}" class="inline-flex items-center rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">
                Export PDF
            </a>
            <a href="{{ route('admin.reports.export.excel', request()->query()) }}" class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                Export Excel
            </a>
        </div>
    </div>

    <div class="rounded-2xl bg-gradient-to-r from-slate-900 via-indigo-900 to-blue-900 p-6 text-white shadow-lg">
        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
            <div>
                <p class="text-sm uppercase tracking-[0.2em] text-blue-200">Generated</p>
                <p class="mt-2 text-2xl font-semibold">{{ $generatedAt->format('M d, Y') }}</p>
                <p class="text-sm text-blue-100">{{ $generatedAt->format('h:i A') }}</p>
            </div>
            <div>
                <p class="text-sm uppercase tracking-[0.2em] text-blue-200">Top Event</p>
                <p class="mt-2 text-xl font-semibold">{{ $topEvent?->title ?? 'No event data' }}</p>
                <p class="text-sm text-blue-100">
                    {{ $topEvent ? $topEvent->registrations_count . ' registrations' : 'Adjust the filters to widen the report.' }}
                </p>
            </div>
            <div>
                <p class="text-sm uppercase tracking-[0.2em] text-blue-200">Filter Status</p>
                <p class="mt-2 text-2xl font-semibold">{{ $filters['status'] !== '' ? ucfirst($filters['status']) : 'All' }}</p>
                <p class="text-sm text-blue-100">
                    {{ $filters['date_from'] !== '' || $filters['date_to'] !== '' ? 'Date range applied' : 'No date range filter' }}
                </p>
            </div>
            <div>
                <p class="text-sm uppercase tracking-[0.2em] text-blue-200">Search</p>
                <p class="mt-2 text-2xl font-semibold">{{ $filters['search'] !== '' ? $filters['search'] : 'None' }}</p>
                <p class="text-sm text-blue-100">Location and title matching</p>
            </div>
        </div>
    </div>

    <div class="rounded-2xl bg-white p-6 shadow">
        <form method="GET" action="{{ route('admin.reports.index') }}" class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
            <div>
                <label for="search" class="mb-2 block text-sm font-semibold text-gray-700">Search</label>
                <input
                    id="search"
                    name="search"
                    type="text"
                    value="{{ $filters['search'] }}"
                    placeholder="Event title or location"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                >
            </div>
            <div>
                <label for="status" class="mb-2 block text-sm font-semibold text-gray-700">Status</label>
                <select
                    id="status"
                    name="status"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                >
                    <option value="">All statuses</option>
                    @foreach(['draft', 'published', 'ongoing', 'completed', 'cancelled'] as $status)
                        <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="date_from" class="mb-2 block text-sm font-semibold text-gray-700">Start From</label>
                <input
                    id="date_from"
                    name="date_from"
                    type="date"
                    value="{{ $filters['date_from'] }}"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                >
            </div>
            <div>
                <label for="date_to" class="mb-2 block text-sm font-semibold text-gray-700">End To</label>
                <input
                    id="date_to"
                    name="date_to"
                    type="date"
                    value="{{ $filters['date_to'] }}"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"
                >
            </div>
            <div class="flex items-end gap-3">
                <button type="submit" class="inline-flex w-full items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                    Apply Filters
                </button>
                <a href="{{ route('admin.reports.index') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
        <div class="rounded-2xl bg-white p-6 shadow">
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-gray-500">Total Events</p>
            <p class="mt-3 text-4xl font-bold text-gray-900">{{ $summary['total_events'] }}</p>
            <p class="mt-2 text-sm text-gray-500">Events included in the current report.</p>
        </div>
        <div class="rounded-2xl bg-white p-6 shadow">
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-gray-500">Registrations</p>
            <p class="mt-3 text-4xl font-bold text-gray-900">{{ $summary['total_registrations'] }}</p>
            <p class="mt-2 text-sm text-gray-500">{{ $summary['approved_registrations'] }} approved registrations.</p>
        </div>
        <div class="rounded-2xl bg-white p-6 shadow">
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-gray-500">Attendance Rate</p>
            <p class="mt-3 text-4xl font-bold text-gray-900">{{ number_format($summary['average_attendance_rate'], 2) }}%</p>
            <p class="mt-2 text-sm text-gray-500">{{ $summary['attendance_records'] }} attendance records logged.</p>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1.4fr_1fr]">
        <div class="rounded-2xl bg-white shadow">
            <div class="border-b border-gray-200 px-6 py-4">
                <h2 class="text-xl font-bold text-gray-800">Event Performance</h2>
                <p class="mt-1 text-sm text-gray-500">Registration and attendance totals for each matching event.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-gray-600">Event</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-gray-600">Schedule</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-gray-600">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-gray-600">Registrations</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-gray-600">Approved</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-gray-600">Attendance</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.2em] text-gray-600">Rate</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($events as $event)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 align-top">
                                    <p class="text-sm font-semibold text-gray-900">{{ $event->title }}</p>
                                    <p class="mt-1 text-xs text-gray-500">{{ $event->location }}</p>
                                    <p class="mt-1 text-xs text-gray-400">Created by {{ $event->creator?->name ?? 'Unknown' }}</p>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <p>{{ $event->start_date->format('M d, Y h:i A') }}</p>
                                    <p class="mt-1 text-xs text-gray-400">to {{ $event->end_date->format('M d, Y h:i A') }}</p>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    @php
                                        $statusClass = match ($event->status) {
                                            'draft' => 'bg-slate-100 text-slate-700',
                                            'published' => 'bg-blue-100 text-blue-700',
                                            'ongoing' => 'bg-emerald-100 text-emerald-700',
                                            'completed' => 'bg-violet-100 text-violet-700',
                                            'cancelled' => 'bg-rose-100 text-rose-700',
                                            default => 'bg-gray-100 text-gray-700',
                                        };
                                        $attendanceRate = $event->approved_registrations_count > 0
                                            ? round(($event->attendance_records_count / $event->approved_registrations_count) * 100, 2)
                                            : 0;
                                    @endphp
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">
                                        {{ ucfirst($event->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-800">{{ $event->registrations_count }}</td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-800">{{ $event->approved_registrations_count }}</td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-800">{{ $event->attendance_records_count }}</td>
                                <td class="px-6 py-4 text-sm font-semibold text-indigo-700">{{ number_format($attendanceRate, 2) }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-10 text-center text-sm text-gray-500">
                                    No events matched the selected filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-2xl bg-white p-6 shadow">
                <h2 class="text-xl font-bold text-gray-800">Status Breakdown</h2>
                <div class="mt-6 space-y-4">
                    @foreach($statusCounts as $status => $count)
                        @php
                            $percent = $summary['total_events'] > 0 ? round(($count / $summary['total_events']) * 100, 1) : 0;
                        @endphp
                        <div>
                            <div class="mb-2 flex items-center justify-between">
                                <span class="text-sm font-semibold text-gray-700">{{ ucfirst($status) }}</span>
                                <span class="text-sm text-gray-500">{{ $count }} events</span>
                            </div>
                            <div class="h-2 rounded-full bg-gray-100">
                                <div class="h-2 rounded-full bg-indigo-500" style="width: {{ $percent }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-2xl bg-white p-6 shadow">
                <h2 class="text-xl font-bold text-gray-800">Report Notes</h2>
                <ul class="mt-4 space-y-3 text-sm text-gray-600">
                    <li>Total attendance uses records captured from event check-ins.</li>
                    <li>Attendance rate is based on approved registrations only.</li>
                    <li>Excel and PDF exports reuse the active filters on this page.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
