@extends('admin.layout')

@section('title', 'Admin Dashboard')

@section('content')
<div class="max-w-7xl mx-auto">
    <h1 class="mb-8 text-3xl font-bold text-gray-800">Admin Dashboard</h1>

    <div class="mb-8 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-lg bg-white p-6 shadow">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Events</p>
                    <p class="mt-2 text-3xl font-bold text-gray-800">{{ $totalEvents }}</p>
                </div>
                <div class="text-4xl text-blue-400">EV</div>
            </div>
        </div>

        <div class="rounded-lg bg-white p-6 shadow">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Registrations</p>
                    <p class="mt-2 text-3xl font-bold text-gray-800">{{ $totalRegistrations }}</p>
                </div>
                <div class="text-4xl text-green-400">RG</div>
            </div>
        </div>
    </div>

    <div class="mb-8 rounded-lg bg-white p-6 shadow">
        <h2 class="mb-4 text-xl font-bold text-gray-800">Quick Actions</h2>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            <a href="{{ route('admin.events.create') }}" class="rounded-lg bg-indigo-600 px-6 py-3 text-center font-semibold text-white transition hover:bg-indigo-700">
                Create New Event
            </a>
            <a href="{{ route('admin.students.create') }}" class="rounded-lg bg-green-600 px-6 py-3 text-center font-semibold text-white transition hover:bg-green-700">
                Register Student
            </a>
            <a href="{{ route('admin.registrations') }}" class="rounded-lg bg-blue-600 px-6 py-3 text-center font-semibold text-white transition hover:bg-blue-700">
                Review Registrations
            </a>
            <a href="{{ route('admin.reports.index') }}" class="rounded-lg bg-slate-800 px-6 py-3 text-center font-semibold text-white transition hover:bg-slate-900">
                View Reports Dashboard
            </a>
        </div>
    </div>

    <div class="overflow-hidden rounded-lg bg-white shadow">
        <div class="border-b border-gray-200 p-6">
            <h2 class="text-xl font-bold text-gray-800">Recent Events</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Title</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Start Date</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Status</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Registrations</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($recentEvents as $event)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-medium text-gray-800">{{ $event->title }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $event->start_date->format('M d, Y') }}</td>
                        <td class="px-6 py-4 text-sm">
                            @php
                                $statusClass = match ($event->status) {
                                    'draft' => 'bg-gray-100 text-gray-800',
                                    'published' => 'bg-blue-100 text-blue-800',
                                    'ongoing' => 'bg-green-100 text-green-800',
                                    'completed' => 'bg-gray-200 text-gray-900',
                                    'cancelled' => 'bg-red-100 text-red-800',
                                    default => 'bg-gray-100 text-gray-800',
                                };
                            @endphp
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">
                                {{ ucfirst($event->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $event->registrations()->count() }}</td>
                        <td class="px-6 py-4 text-sm space-x-2">
                            <a href="{{ route('admin.events.show', $event) }}" class="font-semibold text-indigo-600 hover:text-indigo-700">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-600">No events yet</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
