<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') - LNU Smart Events System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('admin.dashboard') }}" class="text-2xl font-bold text-indigo-600">
                        LNU Events
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-700">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-red-600 hover:text-red-700 font-semibold">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <div class="flex">
        <!-- Sidebar Navigation -->
        <aside class="w-64 bg-gray-900 text-white min-h-screen">
            <nav class="p-4 space-y-2">
                <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 rounded hover:bg-gray-800 {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-600' : '' }}">
                    📊 Dashboard
                </a>
                
                <div class="text-gray-400 text-sm font-semibold px-4 py-2 mt-6 mb-2">EVENTS MANAGEMENT</div>
                <a href="{{ route('admin.events.index') }}" class="block px-4 py-2 rounded hover:bg-gray-800 {{ request()->routeIs('admin.events.index') ? 'bg-indigo-600' : '' }}">
                    📅 Event List
                </a>
                <a href="{{ route('admin.events.create') }}" class="block px-4 py-2 rounded hover:bg-gray-800 {{ request()->routeIs('admin.events.create') ? 'bg-indigo-600' : '' }}">
                    ➕ Register Event
                </a>

                <div class="text-gray-400 text-sm font-semibold px-4 py-2 mt-6 mb-2">STUDENT MANAGEMENT</div>
                <a href="{{ route('admin.students.index') }}" class="block px-4 py-2 rounded hover:bg-gray-800 {{ request()->routeIs('admin.students.*') ? 'bg-indigo-600' : '' }}">
                    👥 Students
                </a>

                <div class="text-gray-400 text-sm font-semibold px-4 py-2 mt-6 mb-2">REGISTRATIONS</div>
                <a href="{{ route('admin.registrations') }}" class="block px-4 py-2 rounded hover:bg-gray-800 {{ request()->routeIs('admin.registrations') ? 'bg-indigo-600' : '' }}">
                    📝 Manage Registrations
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-8">
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</body>
</html>
