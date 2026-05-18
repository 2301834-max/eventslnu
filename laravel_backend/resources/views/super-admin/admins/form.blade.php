<form method="POST" action="{{ $action }}" class="mx-auto max-w-3xl rounded-[1.35rem] border border-blue-100 bg-white p-8 shadow-sm">
    @csrf
    @if($method !== 'POST') @method($method) @endif
    <div class="grid gap-5">
        <div>
            <label class="text-sm font-bold">Full Name</label>
            <input name="name" value="{{ old('name', $admin?->name) }}" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3">
            @error('name')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="text-sm font-bold">Username</label>
            <input name="username" value="{{ old('username', $admin?->username) }}" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3">
            @error('username')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
        </div>
        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label class="text-sm font-bold">Password</label>
                <input type="password" name="password" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3">
                @error('password')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="text-sm font-bold">Confirm Password</label>
                <input type="password" name="password_confirmation" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3">
            </div>
        </div>
        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label class="text-sm font-bold">Organization Type</label>
                <select name="organization_type" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3">
                    @foreach($organizationTypes as $type)
                        <option value="{{ $type }}" @selected(old('organization_type', $admin?->organization_type) === $type)>{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-sm font-bold">Organization Name</label>
                <input name="organization_name" value="{{ old('organization_name', $admin?->organization_name) }}" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3">
                @error('organization_name')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
        </div>
        <label class="inline-flex items-center gap-3 text-sm font-bold">
            <input type="checkbox" name="is_active" value="1" class="h-5 w-5 rounded" @checked(old('is_active', $admin?->is_active ?? true))>
            Active admin account
        </label>
    </div>
    <div class="mt-8 flex gap-3 border-t border-slate-200 pt-6">
        <button class="rounded-xl bg-[#071f5f] px-6 py-3 font-bold text-white">{{ $submitLabel }}</button>
        <a href="{{ route('super-admin.admins.index') }}" class="rounded-xl border border-slate-200 px-6 py-3 font-bold text-slate-700">Cancel</a>
    </div>
</form>
