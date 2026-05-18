@extends('super-admin.layout')

@section('title', 'Activity Logs')

@section('content')
<section class="overflow-hidden rounded-[1.35rem] border border-blue-100 bg-white shadow-sm">
    <div class="border-b border-slate-200 px-6 py-5">
        <h2 class="text-xl font-bold">Transparency Logs</h2>
        <p class="mt-1 text-sm text-slate-500">Admin login, logout, event, student, and registration actions.</p>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Date</th>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Admin</th>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Organization</th>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Action</th>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Description</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($logs as $log)
                    <tr>
                        <td class="px-6 py-4 text-sm text-slate-600">{{ $log->created_at->format('M d, Y h:i A') }}</td>
                        <td class="px-6 py-4 text-sm font-semibold">{{ $log->user?->name ?? 'System' }}</td>
                        <td class="px-6 py-4 text-sm text-slate-600">{{ $log->organization_name ?? 'Not assigned' }}</td>
                        <td class="px-6 py-4 text-sm text-slate-600">{{ $log->action }}</td>
                        <td class="px-6 py-4 text-sm text-slate-700">{{ $log->description }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-6 py-12 text-center text-sm text-slate-500">No logs yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="border-t border-slate-200 px-6 py-4">{{ $logs->links() }}</div>
</section>
@endsection
