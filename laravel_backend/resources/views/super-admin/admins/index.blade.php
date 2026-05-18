@extends('super-admin.layout')

@section('title', 'Admin Accounts')

@section('content')
<section class="overflow-hidden rounded-[1.35rem] border border-blue-100 bg-white shadow-sm">
    <div class="flex items-center justify-between border-b border-slate-200 px-6 py-5">
        <div>
            <h2 class="text-xl font-bold">Admin Accounts</h2>
            <p class="mt-1 text-sm text-slate-500">Create, edit, deactivate, or delete administrator accounts.</p>
        </div>
        <a href="{{ route('super-admin.admins.create') }}" class="rounded-xl bg-[#071f5f] px-5 py-3 text-sm font-bold text-white hover:bg-[#0047ab]">Create Admin</a>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Admin</th>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Organization</th>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Events</th>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Status</th>
                    <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($admins as $admin)
                    <tr>
                        <td class="px-6 py-4">
                            <p class="font-bold">{{ $admin->name }}</p>
                            <p class="text-sm text-slate-500">{{ $admin->username }} • {{ $admin->email }}</p>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-600">{{ $admin->organization_name }}<br><span class="text-xs text-slate-400">{{ $admin->organization_type }}</span></td>
                        <td class="px-6 py-4 text-sm font-semibold">{{ $admin->events_count }}</td>
                        <td class="px-6 py-4"><span class="rounded-full px-3 py-1 text-xs font-bold {{ $admin->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">{{ $admin->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('super-admin.admins.edit', $admin) }}" class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50">Edit</a>
                                <form method="POST" action="{{ route('super-admin.admins.deactivate', $admin) }}">@csrf @method('PATCH')<button class="rounded-xl border border-amber-200 px-3 py-2 text-sm font-bold text-amber-700 hover:bg-amber-50">{{ $admin->is_active ? 'Deactivate' : 'Activate' }}</button></form>
                                <form method="POST" action="{{ route('super-admin.admins.destroy', $admin) }}" onsubmit="return confirm('Delete this admin account?')">@csrf @method('DELETE')<button class="rounded-xl border border-rose-200 px-3 py-2 text-sm font-bold text-rose-700 hover:bg-rose-50">Delete</button></form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-6 py-12 text-center text-sm text-slate-500">No admin accounts yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="border-t border-slate-200 px-6 py-4">{{ $admins->links() }}</div>
</section>
@endsection
