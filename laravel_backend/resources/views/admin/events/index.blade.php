@extends('admin.layout')

@section('title', 'Event List')

@section('content')
<div class="space-y-8">
    <section class="rounded-[2rem] bg-gradient-to-r from-slate-950 via-brand-900 to-sky-900 px-8 py-8 text-white shadow-panel">
        <div class="flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
            <div class="max-w-2xl">
                <p class="text-xs font-semibold uppercase tracking-[0.35em] text-sky-200">Event Operations</p>
                <h2 class="mt-3 text-4xl font-semibold tracking-tight">Keep the event pipeline clean, current, and ready to publish.</h2>
                <p class="mt-4 text-sm leading-7 text-slate-200">
                    Review schedules, spot completed activities, and move quickly into new registrations from the admin workspace.
                </p>
            </div>
            <a href="{{ route('admin.events.create') }}" class="inline-flex items-center justify-center rounded-2xl bg-white px-6 py-3 text-sm font-semibold text-slate-900 transition hover:bg-slate-100">
                Create Event
            </a>
        </div>
    </section>

    <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-panel">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Total</p>
            <p class="mt-4 text-4xl font-semibold text-slate-900">{{ $summary['total'] }}</p>
            <p class="mt-2 text-sm text-slate-500">All events in the system.</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-panel">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Upcoming</p>
            <p class="mt-4 text-4xl font-semibold text-slate-900">{{ $summary['upcoming'] }}</p>
            <p class="mt-2 text-sm text-slate-500">Events scheduled from now onward.</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-panel">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Active</p>
            <p class="mt-4 text-4xl font-semibold text-slate-900">{{ $summary['active'] }}</p>
            <p class="mt-2 text-sm text-slate-500">Published or ongoing events.</p>
        </div>
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-panel">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Completed</p>
            <p class="mt-4 text-4xl font-semibold text-slate-900">{{ $summary['completed'] }}</p>
            <p class="mt-2 text-sm text-slate-500">Closed events ready for reporting.</p>
        </div>
    </section>

    <section class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-panel">
        <div class="flex flex-col gap-3 border-b border-slate-200 px-8 py-6 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-slate-900">All Events</h2>
                <p class="mt-1 text-sm text-slate-500">A live view of schedule, status, and registration volume.</p>
            </div>
            <div class="rounded-2xl bg-slate-100 px-4 py-2 text-sm text-slate-600">
                {{ $events->total() }} records
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-8 py-4 text-left text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Event</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Hosted By</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Schedule</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Registrations</th>
                        <th class="w-72 px-6 py-4 text-left text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($events as $event)
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
                        <tr class="hover:bg-slate-50/80">
                            <td class="px-8 py-5 align-top">
                                <div class="flex items-center gap-4">
                                    <div class="flex h-16 w-24 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-gradient-to-br from-brand-700 via-brand-600 to-sky-500">
                                        @if($event->poster)
                                            <img
                                                src="{{ $event->poster_url }}"
                                                alt="{{ $event->title }} poster"
                                                class="h-full w-full object-cover"
                                                onerror="this.closest('div').innerHTML='<span class=&quot;text-xs font-bold uppercase tracking-[0.2em] text-white&quot;>POSTER</span>';"
                                            >
                                        @else
                                            <span class="text-xs font-bold uppercase tracking-[0.2em] text-white">POSTER</span>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">{{ $event->title }}</p>
                                        <p class="mt-2 text-sm text-slate-500">{{ $event->location }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5 text-sm text-slate-600">
                                {{ $event->organization ?? 'Not specified' }}
                            </td>
                            <td class="px-6 py-5 text-sm text-slate-600">
                                <p>{{ $event->start_date->format('M d, Y h:i A') }}</p>
                                <p class="mt-1 text-xs text-slate-400">to {{ $event->end_date->format('M d, Y h:i A') }}</p>
                            </td>
                            <td class="px-6 py-5 text-sm">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">
                                    {{ ucfirst($event->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-5 text-sm text-slate-700">
                                <span class="font-semibold">{{ $event->registrations_count }}</span>
                                <span class="text-slate-400">/ {{ $event->max_participants }}</span>
                            </td>
                            <td class="w-72 px-6 py-5 text-sm">
                                @if($event->allowsAdminChanges())
                                    <div class="inline-flex overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                                        <a href="{{ route('admin.events.show', $event) }}" class="inline-flex h-10 w-20 items-center justify-center border-r border-slate-200 px-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">
                                            View
                                        </a>
                                        <a href="{{ route('admin.events.edit', $event) }}" class="inline-flex h-10 w-20 items-center justify-center border-r border-slate-200 px-3 text-sm font-semibold text-amber-700 transition hover:bg-amber-50">
                                            Edit
                                        </a>
                                        <form method="POST" action="{{ route('admin.events.destroy', $event) }}" class="inline-flex" onsubmit="return confirm('Are you sure you want to delete this event?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex h-10 w-20 items-center justify-center px-3 text-sm font-semibold text-rose-700 transition hover:bg-rose-50">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <a href="{{ route('admin.events.show', $event) }}" class="inline-flex h-10 w-20 items-center justify-center rounded-xl border border-slate-200 px-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">
                                        View
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-8 py-16 text-center">
                                <p class="text-lg font-semibold text-slate-700">No events found</p>
                                <p class="mt-2 text-sm text-slate-500">Create your first event to start managing registrations.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($events->hasPages())
            <div class="border-t border-slate-200 bg-slate-50 px-8 py-4">
                {{ $events->links() }}
            </div>
        @endif
    </section>
</div>
@endsection
