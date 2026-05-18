<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Login - LNU Smart Events</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-[#f4f7fb] text-slate-900">
    <main class="grid min-h-screen lg:grid-cols-[1.05fr_0.95fr]">
        <section class="hidden bg-gradient-to-br from-[#041033] via-[#071f5f] to-[#0047ab] p-12 text-white lg:flex lg:flex-col lg:justify-between">
            <div class="flex items-center gap-4">
                <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white p-2">
                    <img src="{{ asset('images/lnu-logo.png') }}" alt="LNU" class="h-full w-full object-contain">
                </span>
                <div>
                    <p class="text-lg font-bold">LNU Smart Events</p>
                    <p class="text-sm text-blue-100">Super Admin Console</p>
                </div>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.35em] text-yellow-200">System Governance</p>
                <h1 class="mt-5 max-w-xl text-5xl font-bold tracking-tight">Monitor administrators, organizations, and event activity with confidence.</h1>
                <p class="mt-5 max-w-lg text-sm leading-7 text-blue-100">A dedicated control layer for accountability, transparency, and operational oversight.</p>
            </div>
            <p class="text-xs text-blue-100/80">Leyte Normal University</p>
        </section>

        <section class="flex items-center justify-center px-6 py-12">
            <div class="w-full max-w-md">
                <div class="mb-8 lg:hidden">
                    <img src="{{ asset('images/lnu-logo.png') }}" alt="LNU" class="h-14 w-14">
                </div>
                <div class="rounded-[1.5rem] border border-slate-200 bg-white p-8 shadow-xl shadow-blue-950/10">
                    <p class="text-xs font-bold uppercase tracking-[0.25em] text-blue-700">Super Admin</p>
                    <h2 class="mt-3 text-3xl font-bold">Sign in</h2>
                    <p class="mt-2 text-sm text-slate-500">Use your super admin username or email and password.</p>

                    <form method="POST" action="{{ route('super-admin.login.submit') }}" class="mt-8 space-y-5">
                        @csrf
                        <div>
                            <label for="username" class="text-sm font-semibold text-slate-700">Username or email</label>
                            <input id="username" name="username" value="{{ old('username') }}" required autofocus autocomplete="username" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 outline-none transition focus:border-blue-700 focus:ring-4 focus:ring-blue-100">
                            @error('username')
                                <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="password" class="text-sm font-semibold text-slate-700">Password</label>
                            <input id="password" type="password" name="password" required class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 outline-none transition focus:border-blue-700 focus:ring-4 focus:ring-blue-100">
                            @error('password')
                                <p class="mt-2 text-sm font-semibold text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <button type="submit" class="w-full rounded-xl bg-[#071f5f] px-5 py-3 font-bold text-white transition hover:bg-[#0047ab]">
                            Login to Super Admin
                        </button>
                    </form>
                    <a href="{{ route('admin.login') }}" class="mt-5 inline-flex text-sm font-semibold text-blue-700 hover:text-blue-900">Back to admin login</a>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
