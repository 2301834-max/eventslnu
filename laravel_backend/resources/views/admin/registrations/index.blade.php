@extends('admin.layout')

@section('title', 'Manage Registrations')

@section('content')
<div class="max-w-7xl mx-auto">
    <h1 class="text-3xl font-bold text-gray-800 mb-8">Manage Student Registrations</h1>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Student</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Event</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Status</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Registered On</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($registrations as $registration)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-medium text-gray-800">
                            <a href="{{ route('admin.students.show', $registration->user) }}" class="text-indigo-600 hover:text-indigo-700">
                                {{ $registration->user->name }}
                            </a>
                        </td>
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
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $registration->created_at->format('M d, Y H:i') }}</td>
                        <td class="px-6 py-4 text-sm space-x-2">
                            @if($registration->status === 'pending')
                                <form method="POST" action="{{ route('admin.registrations.approve', $registration) }}" class="inline">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="text-green-600 hover:text-green-700 font-semibold">Approve</button>
                                </form>
                                <form method="POST" action="{{ route('admin.registrations.reject', $registration) }}" class="inline">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="text-red-600 hover:text-red-700 font-semibold">Reject</button>
                                </form>
                            @else
                                @if($registration->approver)
                                    <span class="text-gray-600 text-xs">by {{ $registration->approver->name }}</span>
                                @endif
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-600">No registrations found</td>
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
</div>
@endsection
