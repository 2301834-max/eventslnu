@extends('admin.layout')

@section('title', 'Admin Dashboard')

@section('content')
<div class="max-w-7xl mx-auto">
    <h1 class="text-3xl font-bold text-gray-800 mb-8">Admin Dashboard</h1>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Events -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Total Events</p>
                    <p class="text-3xl font-bold text-gray-800 mt-2">{{ $totalEvents }}</p>
                </div>
                <div class="text-4xl text-blue-400">📅</div>
            </div>
        </div>

        <!-- Total Registrations -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-gray-600 text-sm font-medium">Total Registrations</p>
                    <p class="text-3xl font-bold text-gray-800 mt-2">{{ $totalRegistrations }}</p>
                </div>
                <div class="text-4xl text-green-400">📝</div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Quick Actions</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="{{ route('admin.events.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg text-center font-semibold transition">
                ➕ Create New Event
            </a>
            <a href="{{ route('admin.students.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg text-center font-semibold transition">
                ➕ Register Student
            </a>
            <a href="{{ route('admin.registrations') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg text-center font-semibold transition">
                📋 Review Registrations
            </a>
        </div>
    </div>

    <!-- Recent Events -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6 border-b border-gray-200">
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
                            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusClass }}">
                                {{ ucfirst($event->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $event->registrations()->count() }}</td>
                        <td class="px-6 py-4 text-sm space-x-2">
                            <a href="{{ route('admin.events.show', $event) }}" class="text-indigo-600 hover:text-indigo-700 font-semibold">View</a>
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
