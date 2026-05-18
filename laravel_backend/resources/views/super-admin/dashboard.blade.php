@extends('super-admin.layout')

@section('title', 'Overview')

@section('content')
<div class="space-y-8">
    <section class="rounded-2xl bg-[#061a3a] px-8 py-7 text-white shadow-lg shadow-blue-950/10">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-yellow-200">System command center</p>
                <h2 class="mt-3 text-3xl font-bold">Super Admin Dashboard</h2>
                <p class="mt-3 max-w-3xl text-sm leading-7 text-blue-100">Monitor administrators, events, and audit activity across the LNU Smart Events system.</p>
            </div>
            <div class="rounded-xl border border-white/15 bg-white/10 px-4 py-3 text-sm">
                <p class="text-blue-100">Current operator</p>
                <p class="mt-1 font-bold">{{ auth()->user()->name }}</p>
            </div>
        </div>
    </section>

    <section class="grid gap-4 md:grid-cols-3">
        @foreach([
            ['label' => 'Admin Accounts', 'value' => $totalAdmins, 'meta' => 'Total managed admin users', 'accent' => 'bg-blue-50 text-blue-700'],
            ['label' => 'Active Sessions', 'value' => $activeAdmins . ' / ' . $loggedInAdmins, 'meta' => 'Active accounts and recent sign-ins', 'accent' => 'bg-emerald-50 text-emerald-700'],
            ['label' => 'Audit Entries', 'value' => $systemLogs->count(), 'meta' => 'Latest tracked system actions', 'accent' => 'bg-slate-100 text-slate-700'],
        ] as $card)
            <article class="rounded-2xl border border-blue-100 bg-white p-6 shadow-sm shadow-blue-950/5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-slate-500">{{ $card['label'] }}</p>
                        <p class="mt-4 text-4xl font-bold text-slate-950">{{ $card['value'] }}</p>
                    </div>
                    <span class="rounded-full px-3 py-1 text-xs font-bold {{ $card['accent'] }}">Live</span>
                </div>
                <p class="mt-3 text-sm text-slate-500">{{ $card['meta'] }}</p>
            </article>
        @endforeach
    </section>

    <section class="grid gap-6 xl:grid-cols-2">
        <div class="overflow-hidden rounded-2xl border border-blue-100 bg-white shadow-sm shadow-blue-950/5">
            <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50/70 px-6 py-5">
                <div>
                    <h3 class="text-lg font-bold">Admin Activity</h3>
                    <p class="mt-1 text-sm text-slate-500">Recent Super Admin and admin actions</p>
                </div>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($recentAdminActivities as $log)
                    <div class="flex gap-4 px-6 py-4">
                        <div class="mt-1 flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-blue-50 text-xs font-bold text-blue-700">
                            {{ strtoupper(substr($log->user?->name ?? 'S', 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <p class="font-semibold text-slate-950">{{ $log->description }}</p>
                            <p class="mt-1 text-sm text-slate-500">{{ $log->user?->name ?? 'System' }} • {{ $log->created_at->format('M d, Y h:i A') }}</p>
                        </div>
                    </div>
                @empty
                    <p class="px-6 py-10 text-sm text-slate-500">No activity logs yet.</p>
                @endforelse
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-blue-100 bg-white shadow-sm shadow-blue-950/5">
            <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50/70 px-6 py-5">
                <div>
                    <h3 class="text-lg font-bold">Recently Created Events</h3>
                    <p class="mt-1 text-sm text-slate-500">Newest events created by admin accounts</p>
                </div>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($recentEvents as $event)
                    <div class="flex items-start justify-between gap-4 px-6 py-4">
                        <div class="min-w-0">
                            <p class="truncate font-semibold text-slate-950">{{ $event->title }}</p>
                            <p class="mt-1 text-sm text-slate-500">{{ $event->creator?->name ?? 'Unknown admin' }} • {{ $event->created_at->format('M d, Y h:i A') }}</p>
                        </div>
                        <span class="shrink-0 rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">{{ ucfirst($event->status ?? 'Draft') }}</span>
                    </div>
                @empty
                    <p class="px-6 py-10 text-sm text-slate-500">No events yet.</p>
                @endforelse
            </div>
        </div>
    </section>

    <section class="overflow-hidden rounded-2xl border border-blue-100 bg-white shadow-sm shadow-blue-950/5">
        <div class="border-b border-slate-200 bg-slate-50/70 px-6 py-5">
            <h3 class="text-lg font-bold">Audit Trail</h3>
            <p class="mt-1 text-sm text-slate-500">Latest recorded account and system activity</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Action</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Admin</th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($systemLogs as $log)
                        <tr class="hover:bg-slate-50/80">
                            <td class="px-6 py-4 text-sm font-semibold text-slate-950">{{ $log->description }}</td>
                            <td class="px-6 py-4 text-sm text-slate-600">{{ $log->user?->name ?? 'System' }}</td>
                            <td class="px-6 py-4 text-sm text-slate-600">{{ $log->created_at->format('M d, Y h:i A') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
