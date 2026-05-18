<?php

use App\Http\Controllers\AdminEventController;
use App\Http\Controllers\AdminEventRegistrationQrController;
use App\Http\Controllers\AdminReportController;
use App\Http\Controllers\AdminStudentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SuperAdminController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return redirect()->route('admin.login');
});

Route::get('/storage/{path}', function (string $path) {
    abort_unless(Storage::disk('public')->exists($path), 404);

    return Storage::disk('public')->response($path);
})->where('path', '.*')->name('public-storage.fallback');

Route::get('/event-posters/{path}', function (string $path) {
    $path = ltrim($path, '/');

    abort_unless(str_starts_with($path, 'event-posters/'), 404);
    abort_unless(Storage::disk('public')->exists($path), 404);

    return Storage::disk('public')->response($path);
})->where('path', '.*')->name('event-posters.show');

// Temporary test routes for debugging session/CSRF
Route::get('/test/session', function () {
    $sessionId = session()->getId();
    session(['test' => 'value_'.time()]);

    return response()->json([
        'session_id' => $sessionId,
        'session_driver' => config('session.driver'),
        'session_value' => session('test'),
        'csrf_token' => csrf_token(),
        'message' => 'Session test - check if Set-Cookie header is present',
    ])->header('X-Session-ID', $sessionId);
});

Route::get('/test/csrf-form', function () {
    return view('test-csrf-form');
});

Route::post('/test/csrf-post', function (\Illuminate\Http\Request $request) {
    return response()->json([
        'message' => 'CSRF token validated successfully',
        'session_id' => session()->getId(),
        'csrf_token_received' => $request->input('_token') ? 'YES' : 'NO',
    ]);
});

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/admin/login', [AuthController::class, 'showAdminLoginForm'])->name('admin.login');
    Route::post('/admin/login', [AuthController::class, 'adminLogin'])->name('admin.login.submit');
    Route::get('/super-admin/login', [AuthController::class, 'showSuperAdminLoginForm'])->name('super-admin.login');
    Route::post('/super-admin/login', [AuthController::class, 'superAdminLogin'])->name('super-admin.login.submit');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'superadmin'])->prefix('super-admin')->name('super-admin.')->group(function () {
    Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/admins', [SuperAdminController::class, 'adminAccounts'])->name('admins.index');
    Route::get('/admins/create', [SuperAdminController::class, 'createAdmin'])->name('admins.create');
    Route::post('/admins', [SuperAdminController::class, 'storeAdmin'])->name('admins.store');
    Route::get('/admins/{admin}/edit', [SuperAdminController::class, 'editAdmin'])->name('admins.edit');
    Route::put('/admins/{admin}', [SuperAdminController::class, 'updateAdmin'])->name('admins.update');
    Route::patch('/admins/{admin}/deactivate', [SuperAdminController::class, 'deactivateAdmin'])->name('admins.deactivate');
    Route::delete('/admins/{admin}', [SuperAdminController::class, 'destroyAdmin'])->name('admins.destroy');
    Route::get('/activity-logs', [SuperAdminController::class, 'activityLogs'])->name('activity-logs');
    Route::get('/events-monitoring', [SuperAdminController::class, 'eventsMonitoring'])->name('events-monitoring');
    Route::get('/reports', [SuperAdminController::class, 'reports'])->name('reports');
});

// Admin routes - Web-based authentication with sessions
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Event management
    Route::resource('events', AdminEventController::class);
    Route::post('events/{event}/poster', [AdminEventController::class, 'updatePoster'])
        ->name('events.poster.update');
    Route::post('events/{event}/registration-qr', [AdminEventRegistrationQrController::class, 'generate'])
        ->name('events.registration-qr.generate');

    // Student management
    Route::resource('students', AdminStudentController::class);

    // Registration management
    Route::get('/registrations', [AdminEventController::class, 'registrations'])->name('registrations');
    Route::put('/registrations/{registration}/approve', [AdminEventController::class, 'approveRegistration'])->name('registrations.approve');
    Route::put('/registrations/{registration}/reject', [AdminEventController::class, 'rejectRegistration'])->name('registrations.reject');
    Route::delete('/registrations/{registration}', [AdminEventController::class, 'destroyRegistration'])->name('registrations.destroy');

    // Reports
    Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export/pdf', [AdminReportController::class, 'exportPdf'])->name('reports.export.pdf');
    Route::get('/reports/export/excel', [AdminReportController::class, 'exportExcel'])->name('reports.export.excel');
});

// Dashboard routes (for students) - Web-based authentication
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/events', [DashboardController::class, 'events'])->name('dashboard.events');
    Route::get('/dashboard/events/{event}', [DashboardController::class, 'eventDetail'])->name('dashboard.event-detail');
    Route::get('/dashboard/registrations', [DashboardController::class, 'registrations'])->name('dashboard.registrations');
    Route::get('/dashboard/attendance', [DashboardController::class, 'attendance'])->name('dashboard.attendance');
    Route::get('/dashboard/reports', [DashboardController::class, 'reports'])->name('dashboard.reports');
});
