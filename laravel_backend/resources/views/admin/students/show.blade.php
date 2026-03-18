@extends('admin.layout')

@section('title', $student->name)

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-start mb-8">
        <h1 class="text-3xl font-bold text-gray-800">{{ $student->name }}</h1>
        <div class="flex gap-2">
            <a href="{{ route('admin.students.edit', $student) }}" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg font-semibold">
                Edit
            </a>
            <a href="{{ route('admin.students.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-semibold">
                Back
            </a>
        </div>
    </div>

    <!-- Student Info -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Student Information</h2>
                
                <div class="space-y-4">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Email</p>
                        <p class="text-gray-800">{{ $student->email }}</p>
                    </div>

                    <div>
                        <p class="text-gray-600 text-sm font-medium">Joined</p>
                        <p class="text-gray-800">{{ $student->created_at->format('M d, Y H:i') }}</p>
                    </div>

                    <div>
                        <p class="text-gray-600 text-sm font-medium">Last Updated</p>
                        <p class="text-gray-800">{{ $student->updated_at->format('M d, Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div>
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Statistics</h3>
                
                <div class="space-y-3">
                    <div class="border-b pb-3">
                        <p class="text-gray-600 text-sm">Total Registrations</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $registrations->total() }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm">Attendance Records</p>
                        <p class="text-2xl font-bold text-blue-600">{{ $attendanceRecords->total() }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Event Registrations -->
    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-800">Event Registrations</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Event</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Status</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Registered On</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($registrations as $registration)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-medium text-gray-800">
                            <a href="{{ route('admin.events.show', $registration->event) }}" class="text-indigo-600 hover:text-indigo-700">
                                {{ $registration->event->title }}
                            </a>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold 
                                {{ $registration->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                   ($registration->status === 'approved' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800') }}">
                                {{ ucfirst($registration->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $registration->created_at->format('M d, Y') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-6 py-4 text-center text-gray-600">No registrations yet</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($registrations->hasPages())
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            {{ $registrations->links() }}
        </div>
        @endif
    </div>

    <!-- Attendance Records -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-800">Attendance Records</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Event</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Check In Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($attendanceRecords as $record)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-medium text-gray-800">
                            <a href="{{ route('admin.events.show', $record->event) }}" class="text-indigo-600 hover:text-indigo-700">
                                {{ $record->event->title }}
                            </a>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $record->check_in_time->format('M d, Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="2" class="px-6 py-4 text-center text-gray-600">No attendance records yet</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($attendanceRecords->hasPages())
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            {{ $attendanceRecords->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
