<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SuperAdminController extends Controller
{
    public function dashboard(): View
    {
        $adminQuery = User::where('role', 'admin');

        return view('super-admin.dashboard', [
            'totalAdmins' => (clone $adminQuery)->count(),
            'activeAdmins' => (clone $adminQuery)->where('is_active', true)->count(),
            'loggedInAdmins' => (clone $adminQuery)->where('last_login_at', '>=', now()->subMinutes(30))->count(),
            'recentAdminActivities' => ActivityLog::with('user')->latest()->take(8)->get(),
            'recentEvents' => Event::with('creator')->latest()->take(8)->get(),
            'systemLogs' => ActivityLog::with('user')->latest()->take(12)->get(),
        ]);
    }

    public function adminAccounts(): View
    {
        $admins = User::where('role', 'admin')
            ->withCount('events')
            ->latest()
            ->paginate(12);

        return view('super-admin.admins.index', ['admins' => $admins]);
    }

    public function createAdmin(): View
    {
        return view('super-admin.admins.create', [
            'organizationTypes' => $this->organizationTypes(),
        ]);
    }

    public function storeAdmin(Request $request): RedirectResponse
    {
        $validated = $this->validateAdmin($request);

        $admin = User::create([
            'name' => $validated['name'],
            'username' => strtolower($validated['username']),
            'email' => strtolower($validated['username']).'@admin.lnusystem.local',
            'password' => Hash::make($validated['password']),
            'role' => 'admin',
            'student_id' => null,
            'organization_type' => $validated['organization_type'],
            'organization_name' => $validated['organization_name'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        ActivityLog::record('admin.created', 'Created admin account: '.$admin->name, $admin);

        return redirect()->route('super-admin.admins.index')->with('success', 'Admin account created.');
    }

    public function editAdmin(User $admin): View
    {
        abort_unless($admin->isAdmin(), 404);

        return view('super-admin.admins.edit', [
            'admin' => $admin,
            'organizationTypes' => $this->organizationTypes(),
        ]);
    }

    public function updateAdmin(Request $request, User $admin): RedirectResponse
    {
        abort_unless($admin->isAdmin(), 404);

        $validated = $this->validateAdmin($request, $admin);

        $payload = [
            'name' => $validated['name'],
            'username' => strtolower($validated['username']),
            'email' => strtolower($validated['username']).'@admin.lnusystem.local',
            'organization_type' => $validated['organization_type'],
            'organization_name' => $validated['organization_name'],
            'is_active' => $request->boolean('is_active'),
        ];

        if (! empty($validated['password'])) {
            $payload['password'] = Hash::make($validated['password']);
        }

        $admin->update($payload);

        ActivityLog::record('admin.updated', 'Updated admin account: '.$admin->name, $admin);

        return redirect()->route('super-admin.admins.index')->with('success', 'Admin account updated.');
    }

    public function deactivateAdmin(User $admin): RedirectResponse
    {
        abort_unless($admin->isAdmin(), 404);

        $admin->update(['is_active' => ! $admin->is_active]);

        ActivityLog::record(
            $admin->is_active ? 'admin.activated' : 'admin.deactivated',
            ($admin->is_active ? 'Activated admin account: ' : 'Deactivated admin account: ').$admin->name,
            $admin
        );

        return redirect()->back()->with('success', 'Admin account status updated.');
    }

    public function destroyAdmin(User $admin): RedirectResponse
    {
        abort_unless($admin->isAdmin(), 404);

        $name = $admin->name;
        $admin->delete();

        ActivityLog::record('admin.deleted', 'Deleted admin account: '.$name, $admin);

        return redirect()->route('super-admin.admins.index')->with('success', 'Admin account deleted.');
    }

    public function activityLogs(): View
    {
        $logs = ActivityLog::with('user')->latest()->paginate(20);

        return view('super-admin.activity-logs', ['logs' => $logs]);
    }

    public function eventsMonitoring(): View
    {
        $events = Event::with('creator')
            ->latest()
            ->paginate(15);

        return view('super-admin.events-monitoring', ['events' => $events]);
    }

    public function reports(): View
    {
        return view('super-admin.reports', [
            'adminsCount' => User::where('role', 'admin')->count(),
            'eventsCount' => Event::count(),
            'logsCount' => ActivityLog::count(),
        ]);
    }

    private function validateAdmin(Request $request, ?User $admin = null): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'username' => [
                'required',
                'string',
                'max:50',
                'alpha_dash',
                Rule::unique('users', 'username')->ignore($admin?->id),
            ],
            'password' => [$admin ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'organization_type' => 'required|string|max:255',
            'organization_name' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);
    }

    private function organizationTypes(): array
    {
        return [
            'Academic Organization',
            'Administrative Office',
            'Student Organization',
            'College Department',
            'University Office',
            'External Partner',
        ];
    }
}
