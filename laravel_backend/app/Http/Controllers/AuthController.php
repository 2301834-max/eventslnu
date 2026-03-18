<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Display the login form
     */
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    /**
     * Handle login form submission
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();

            // Generate Sanctum token for API access
            $user = Auth::user();
            $token = $user->createToken('web-token')->plainTextToken;

            // Store token in session to pass to frontend
            session(['api_token' => $token]);

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * API Login - Issue Sanctum token
     */
    public function apiLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // Allow either email or username-style value from Flutter.
            'email' => 'required|string',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        $login = $validated['email'];

        // Support login via email OR name (username-like).
        $user = User::where('email', $login)
            ->orWhere('name', $login)
            ->first();

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
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
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
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
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
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'token' => $token,
            'user' => $user,
        ], 201);
    }

    /**
     * Display the admin login form
     */
    public function showAdminLoginForm(): View
    {
        return view('auth.admin-login');
    }

    /**
     * Handle admin login
     */
    public function adminLogin(Request $request)
    {
        \Illuminate\Support\Facades\Log::info('Admin login request received', [
            'method' => $request->method(),
            'url' => $request->url(),
            'session_id' => session()->getId(),
            'session_driver' => config('session.driver'),
            'has_csrf_token' => $request->has('_token'),
            'request_token' => $request->input('_token') ? substr($request->input('_token'), 0, 10) . '...' : 'NONE',
            'session_token' => csrf_token() ? substr(csrf_token(), 0, 10) . '...' : 'NONE',
        ]);

        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        $user = User::where('email', $request->email)->first();

        \Illuminate\Support\Facades\Log::info('User lookup', [
            'email' => $request->email,
            'user_found' => $user ? true : false,
            'user_role' => $user?->role,
        ]);

        if (!$user || !Hash::check($request->password, $user->password)) {
            \Illuminate\Support\Facades\Log::warning('Invalid credentials', [
                'email' => $request->email,
                'user_exists' => $user ? true : false,
                'password_matches' => $user ? Hash::check($request->password, $user->password) : false,
            ]);
            return back()->withErrors([
                'email' => 'Invalid credentials',
            ])->onlyInput('email');
        }

        if (!$user->isAdmin()) {
            \Illuminate\Support\Facades\Log::warning('Non-admin attempted admin login', [
                'email' => $request->email,
                'user_role' => $user->role,
            ]);
            return back()->withErrors([
                'email' => 'You are not authorized to access admin panel',
            ])->onlyInput('email');
        }

        \Illuminate\Support\Facades\Log::info('Admin login successful - authenticating', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        // Use the web guard explicitly
        Auth::guard('web')->login($user, true);

        \Illuminate\Support\Facades\Log::info('After guard login', [
            'authenticated' => Auth::check(),
            'user_id' => Auth::id(),
        ]);

        $request->session()->regenerate();

        \Illuminate\Support\Facades\Log::info('Session after regenerate', [
            'authenticated' => Auth::check(),
            'user_id' => Auth::id(),
            'session_id' => session()->getId(),
        ]);

        // Generate Sanctum token for API access
        $token = $user->createToken('admin-token')->plainTextToken;
        session(['api_token' => $token]);

        \Illuminate\Support\Facades\Log::info('Redirecting to admin dashboard', [
            'user_id' => $user->id,
            'authenticated' => Auth::check(),
        ]);

        return redirect()->route('admin.dashboard');
    }
}
