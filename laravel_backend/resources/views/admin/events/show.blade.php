@extends('admin.layout')

@section('title', $event->title)

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-start mb-8">
        <h1 class="text-3xl font-bold text-gray-800">{{ $event->title }}</h1>
        <div class="flex gap-2">
            <a href="{{ route('admin.events.edit', $event) }}" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg font-semibold">
                Edit
            </a>
            <a href="{{ route('admin.events.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-semibold">
                Back
            </a>
        </div>
    </div>

    <!-- Event Details -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Event Information</h2>
                
                @if($event->event_image)
                    <img src="{{ asset('storage/' . $event->event_image) }}" alt="{{ $event->title }}" class="w-full h-64 object-cover rounded-lg mb-4">
                @endif

                <div class="space-y-4">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Description</p>
                        <p class="text-gray-800 mt-1">{{ $event->description }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-gray-600 text-sm font-medium">Start Date</p>
                            <p class="text-gray-800">{{ $event->start_date->format('M d, Y H:i') }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600 text-sm font-medium">End Date</p>
                            <p class="text-gray-800">{{ $event->end_date->format('M d, Y H:i') }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-gray-600 text-sm font-medium">Location</p>
                            <p class="text-gray-800">{{ $event->location }}</p>
                        </div>
                        <div>
                            <p class="text-gray-600 text-sm font-medium">Status</p>
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
                            <span class="inline-block mt-1 px-3 py-1 rounded-full text-xs font-semibold {{ $statusClass }}">
                                {{ ucfirst($event->status) }}
                            </span>
                        </div>
                    </div>

                    <div>
                        <p class="text-gray-600 text-sm font-medium">Max Participants</p>
                        <p class="text-gray-800">{{ $event->max_participants }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div>
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Quick Stats</h3>
                
                <div class="space-y-3">
                    <div class="border-b pb-3">
                        <p class="text-gray-600 text-sm">Total Registrations</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $event->registrations()->count() }}</p>
                    </div>
                    <div class="border-b pb-3">
                        <p class="text-gray-600 text-sm">Approved</p>
                        <p class="text-2xl font-bold text-green-600">{{ $event->approvedRegistrations()->count() }}</p>
                    </div>
                    <div class="border-b pb-3">
                        <p class="text-gray-600 text-sm">Attendance</p>
                        <p class="text-2xl font-bold text-blue-600">{{ $event->attendanceRecords()->count() }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm">Capacity</p>
                        <p class="text-gray-800">{{ number_format((($event->registrations()->count() / $event->max_participants) * 100), 1) }}%</p>
                    </div>
                </div>
            </div>

            <!-- Event Registration QR -->
            <div class="bg-white rounded-lg shadow p-6 mt-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-800">Event Registration QR</h3>
                    <form method="POST" action="{{ route('admin.events.registration-qr.generate', $event) }}">
                        @csrf
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-semibold">
                            Generate QR
                        </button>
                    </form>
                </div>

                @php
                    $regQr = $event->qrCodes()
                        ->where('type', 'event_registration')
                        ->where('status', 'active')
                        ->latest()
                        ->first();
                @endphp

                @if($regQr)
                    <p class="text-sm text-gray-600 mb-2">Students can scan this code in the mobile app to register instantly.</p>
                    <div class="bg-gray-50 border rounded-lg p-3 text-xs font-mono break-all">{{ $regQr->code }}</div>
                    <p class="text-xs text-gray-500 mt-2">Expires: {{ $regQr->expires_at?->format('M d, Y H:i') ?? 'Never' }}</p>

                    <div class="mt-4">
                        <div id="eventRegQr"></div>
                    </div>

                    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
                    <script>
                        (function () {
                            var el = document.getElementById('eventRegQr');
                            if (!el) return;
                            el.innerHTML = '';
                            new QRCode(el, {
                                text: @json($regQr->code),
                                width: 180,
                                height: 180
                            });
                        })();
                    </script>
                @else
                    <p class="text-sm text-gray-600">No active registration QR for this event.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Registrations Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-800">Registrations</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Student Name</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Email</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Status</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Registered On</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($registrations as $registration)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-medium text-gray-800">{{ $registration->user->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $registration->user->email }}</td>
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
                        <td colspan="4" class="px-6 py-4 text-center text-gray-600">No registrations yet</td>
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
