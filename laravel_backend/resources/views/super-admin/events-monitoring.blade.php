@extends('super-admin.layout')

@section('title', 'Events Monitoring')

@section('content')
<section class="overflow-hidden rounded-[1.35rem] border border-blue-100 bg-white shadow-sm">
    <div class="border-b border-slate-200 px-6 py-5">
        <h2 class="text-xl font-bold">Events Created by Admins</h2>
        <p class="mt-1 text-sm text-slate-500">Track event ownership, organizations, dates, and statuses.</p>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Event</th>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Organization</th>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Date</th>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Status</th>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Created By</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($events as $event)
                    <tr>
                        <td class="px-6 py-4 text-sm font-semibold">{{ $event->title }}</td>
                        <td class="px-6 py-4 text-sm text-slate-600">{{ $event->organization ?? 'Not assigned' }}</td>
                        <td class="px-6 py-4 text-sm text-slate-600">{{ $event->start_date->format('M d, Y h:i A') }}</td>
                        <td class="px-6 py-4"><span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700">{{ ucfirst($event->status) }}</span></td>
                        <td class="px-6 py-4 text-sm text-slate-600">{{ $event->creator?->name ?? 'Unknown' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-6 py-12 text-center text-sm text-slate-500">No events yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="border-t border-slate-200 px-6 py-4">{{ $events->links() }}</div>
</section>
@endsection
