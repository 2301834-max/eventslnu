<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
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
            'email' => ['required', 'email', 'regex:' . User::INSTITUTIONAL_EMAIL_REGEX],
            'password' => 'required|string|min:6',
        ], [
            'email.regex' => 'Please use your institutional email ending in @lnu.edu.ph.',
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

        if (!$user || !Hash::check($validated['password'], $user->password)) {
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
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
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
            'email' => ['required', 'email', 'max:255', 'unique:users,email', 'regex:' . User::INSTITUTIONAL_EMAIL_REGEX],
            'student_id' => 'required|string|max:50|unique:users,student_id|regex:/^[A-Za-z0-9-]+$/',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'email.regex' => 'Please use your institutional email ending in @lnu.edu.ph.',
            'student_id.regex' => 'Student ID may only contain letters, numbers, and hyphens.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'student_id' => strtoupper($validated['student_id']),
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
            'email' => ['required', 'email', 'max:255', 'unique:users,email', 'regex:' . User::INSTITUTIONAL_EMAIL_REGEX],
            'student_id' => 'required|string|max:50|unique:users,student_id|regex:/^[A-Za-z0-9-]+$/',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'email.regex' => 'Please use your institutional email ending in @lnu.edu.ph.',
            'student_id.regex' => 'Student ID may only contain letters, numbers, and hyphens.',
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
            'student_id' => strtoupper($validated['student_id']),
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
            $normalized['student_id'] = strtoupper(trim((string) ($normalized['student_id'] ?? $request->input('student_id'))));
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

    private function attemptAdminWebLogin(Request $request): RedirectResponse
    {
        $this->ensureLocalAdminAccount();

        $request->validate([
            'email' => ['required', 'email'],
            'password' => 'required|min:6',
        ]);

        $user = User::where('email', strtolower($request->string('email')->trim()->toString()))
            ->where('role', 'admin')
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return redirect()->route('admin.login')->withErrors([
                'email' => 'Only administrator accounts can sign in here.',
            ])->onlyInput('email');
        }

        // Use the web guard explicitly
        Auth::guard('web')->login($user, true);

        $request->session()->regenerate();

        // Generate Sanctum token for API access
        $token = $user->createToken('admin-token')->plainTextToken;
        session(['api_token' => $token]);

        return redirect()->route('admin.dashboard');
    }

    private function ensureLocalAdminAccount(): void
    {
        if (!app()->environment('local')) {
            return;
        }

        DB::table('users')->updateOrInsert(
            ['email' => 'admin@lnusystem.local'],
            [
                'name' => 'LNU Administrator',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'student_id' => null,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }
}
