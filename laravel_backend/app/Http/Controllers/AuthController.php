<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Display the login form
     */
    public function showLoginForm(): RedirectResponse
    {
        return redirect()->route('admin.login');
    }

    /**
     * Handle login form submission
     */
    public function login(Request $request): RedirectResponse
    {
        return $this->attemptAdminWebLogin($request);
    }

    /**
     * API Login - Issue Sanctum token
     */
    public function apiLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email', 'regex:'.User::INSTITUTIONAL_EMAIL_REGEX],
            'password' => 'required|string|min:6',
        ], [
            'email.regex' => 'Email must use your student number with @lnu.edu.ph, for example 2301360@lnu.edu.ph.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        $login = strtolower($validated['email']);

        $user = User::where('email', $login)->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => $user,
        ]);
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        $user = $request->user();
        $redirectRoute = $user?->isSuperAdmin()
            ? route('super-admin.login')
            : ($user?->isAdmin() ? route('admin.login') : url('/'));

        if ($user?->isAdmin() || $user?->isSuperAdmin()) {
            ActivityLog::record('auth.logout', $user->name.' logged out.', null, $user);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect($redirectRoute);
    }

    /**
     * API Logout - Delete Sanctum token
     */
    public function apiLogout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Display register form
     */
    public function showRegisterForm(): View
    {
        return view('auth.register');
    }

    /**
     * Handle web registration
     */
    public function register(Request $request)
    {
        $this->normalizeRegistrationInput($request);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', 'unique:users,email', 'regex:'.User::INSTITUTIONAL_EMAIL_REGEX],
            'student_id' => 'required|string|max:7|unique:users,student_id|regex:/^\d{1,7}$/',
            'password' => ['required', 'string', 'min:8', 'confirmed', 'regex:/^(?=.*[A-Za-z])(?=.*\d).+$/'],
        ], [
            'email.regex' => 'Email must use your student number with @lnu.edu.ph, for example 2301360@lnu.edu.ph.',
            'student_id.regex' => 'Student ID must contain numbers only, up to 7 digits.',
            'password.regex' => 'Password must include at least one letter and one number.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'student_id' => $validated['student_id'],
            'password' => Hash::make($validated['password']),
            'role' => 'student',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Handle API registration and always return JSON responses.
     */
    public function apiRegister(Request $request)
    {
        $this->normalizeRegistrationInput($request);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', 'unique:users,email', 'regex:'.User::INSTITUTIONAL_EMAIL_REGEX],
            'student_id' => 'required|string|max:7|unique:users,student_id|regex:/^\d{1,7}$/',
            'password' => ['required', 'string', 'min:8', 'confirmed', 'regex:/^(?=.*[A-Za-z])(?=.*\d).+$/'],
        ], [
            'email.regex' => 'Email must use your student number with @lnu.edu.ph, for example 2301360@lnu.edu.ph.',
            'student_id.regex' => 'Student ID must contain numbers only, up to 7 digits.',
            'password.regex' => 'Password must include at least one letter and one number.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'student_id' => $validated['student_id'],
            'password' => Hash::make($validated['password']),
            'role' => 'student',
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'token' => $token,
            'user' => $user,
        ], 201);
    }

    private function normalizeRegistrationInput(Request $request): void
    {
        $aliases = [
            'student_id' => ['studentId', 'studentID', 'student_number', 'studentNumber'],
            'password_confirmation' => ['confirmPassword', 'passwordConfirmation', 'confirm_password'],
        ];

        $normalized = [];

        foreach ($aliases as $field => $fieldAliases) {
            if ($request->filled($field)) {
                continue;
            }

            foreach ($fieldAliases as $alias) {
                if ($request->filled($alias)) {
                    $normalized[$field] = $request->input($alias);
                    break;
                }
            }
        }

        if ($request->filled('email')) {
            $normalized['email'] = strtolower(trim((string) $request->input('email')));
        }

        if ($request->filled('student_id') || array_key_exists('student_id', $normalized)) {
            $normalized['student_id'] = trim((string) ($normalized['student_id'] ?? $request->input('student_id')));
        }

        if ($normalized !== []) {
            $request->merge($normalized);
        }
    }

    /**
     * Display the admin login form
     */
    public function showAdminLoginForm(): View
    {
        $this->ensureLocalAdminAccount();

        return view('auth.admin-login');
    }

    /**
     * Handle admin login
     */
    public function adminLogin(Request $request): RedirectResponse
    {
        return $this->attemptAdminWebLogin($request);
    }

    public function showSuperAdminLoginForm(): View
    {
        $this->ensureLocalSuperAdminAccount();

        return view('auth.super-admin-login');
    }

    public function superAdminLogin(Request $request): RedirectResponse
    {
        $this->ensureLocalSuperAdminAccount();

        $validated = $request->validate([
            'username' => 'required|string|max:255',
            'password' => 'required|string|min:6',
        ]);

        $identifier = strtolower(trim($validated['username']));
        $user = User::where('role', 'super_admin')
            ->where(function ($query) use ($identifier) {
                $query->where('username', $identifier)
                    ->orWhere('email', $identifier);
            })
            ->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return redirect()->route('super-admin.login')->withErrors([
                'username' => 'Invalid super admin credentials.',
            ])->onlyInput('username');
        }

        if (! $user->is_active) {
            return redirect()->route('super-admin.login')->withErrors([
                'username' => 'This super admin account is inactive.',
            ])->onlyInput('username');
        }

        Auth::guard('web')->login($user, true);
        $request->session()->regenerate();

        $user->forceFill(['last_login_at' => now()])->save();
        ActivityLog::record('auth.login', 'Signed in to the Super Admin console.', null, $user);

        return redirect()->route('super-admin.dashboard');
    }

    private function attemptAdminWebLogin(Request $request): RedirectResponse
    {
        $this->ensureLocalAdminAccount();

        $request->validate([
            'email' => ['required', 'string'],
            'password' => 'required|min:6',
        ]);

        $identifier = strtolower($request->string('email')->trim()->toString());
        $user = User::where('role', 'admin')
            ->where(function ($query) use ($identifier) {
                $query->where('email', $identifier)
                    ->orWhere('username', $identifier);
            })
            ->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return redirect()->route('admin.login')->withErrors([
                'email' => 'Only administrator accounts can sign in here.',
            ])->onlyInput('email');
        }

        if (! $user->is_active) {
            return redirect()->route('admin.login')->withErrors([
                'email' => 'This administrator account is inactive.',
            ])->onlyInput('email');
        }

        // Use the web guard explicitly
        Auth::guard('web')->login($user, true);

        $request->session()->regenerate();

        // Generate Sanctum token for API access
        $token = $user->createToken('admin-token')->plainTextToken;
        session(['api_token' => $token]);

        $user->forceFill(['last_login_at' => now()])->save();
        ActivityLog::record('auth.login', $user->name.' logged in as Admin.', null, $user);

        return redirect()->route('admin.dashboard');
    }

    private function ensureLocalAdminAccount(): void
    {
        if (! app()->environment('local')) {
            return;
        }

        DB::table('users')->updateOrInsert(
            ['email' => 'admin@lnusystem.local'],
            [
                'name' => 'LNU Administrator',
                'username' => 'admin',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'student_id' => null,
                'organization_type' => 'University Office',
                'organization_name' => 'Leyte Normal University',
                'is_active' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    private function ensureLocalSuperAdminAccount(): void
    {
        if (! app()->environment('local')) {
            return;
        }

        DB::table('users')->updateOrInsert(
            ['username' => 'superadmin'],
            [
                'name' => 'Super Administrator',
                'email' => 'superadmin@lnusystem.local',
                'password' => Hash::make('superadmin123'),
                'role' => 'super_admin',
                'student_id' => null,
                'organization_type' => 'System',
                'organization_name' => 'LNU Smart Events',
                'is_active' => true,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }
}
