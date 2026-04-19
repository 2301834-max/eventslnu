                                                                                                                                                                                                                                                                                                                                                                                                        <?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\RegistrationController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\StatisticsController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\QRCodeController;
use App\Http\Controllers\Api\QRRegistrationController;
use App\Http\Controllers\Api\ProfileController;

// Public authentication routes
Route::post('/login', [AuthController::class, 'apiLogin']);
Route::post('/register', [AuthController::class, 'apiRegister']);

Route::middleware('auth:sanctum')->group(function () {
    
    // API logout
    Route::post('/logout', [AuthController::class, 'apiLogout']);
    
    /**
     * User Routes
     */
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');

    /**
     * EVENTS MANAGEMENT
     * Admin can create, edit, delete events
     */
    Route::prefix('events')->group(function () {
        Route::get('/', [EventController::class, 'index'])->name('events.index');
        Route::post('/', [EventController::class, 'store'])->name('events.store');
        
        Route::prefix('{event}')->group(function () {
            Route::get('/', [EventController::class, 'show'])->name('events.show');
            Route::put('/', [EventController::class, 'update'])->name('events.update');
            Route::delete('/', [EventController::class, 'destroy'])->name('events.destroy');
            
            // Event status management
            Route::post('publish', [EventController::class, 'publish'])->name('events.publish');
            Route::post('start', [EventController::class, 'start'])->name('events.start');
            Route::post('end', [EventController::class, 'end'])->name('events.end');
            Route::post('cancel', [EventController::class, 'cancel'])->name('events.cancel');

            /**
             * REGISTRATIONS MANAGEMENT
             * Admin can approve/reject registrations, view registrations
             */
            Route::prefix('registrations')->group(function () {
                Route::get('/', [RegistrationController::class, 'index'])
                    ->name('registrations.index');
                Route::post('/', [RegistrationController::class, 'store'])
                    ->name('registrations.store');
                Route::get('me', [RegistrationController::class, 'me'])
                    ->name('registrations.me');
                Route::post('bulk-approve', [RegistrationController::class, 'bulkApprove'])
                    ->name('registrations.bulk-approve');

                Route::prefix('{registration}')->group(function () {
                    Route::get('/', [RegistrationController::class, 'show'])
                        ->name('registrations.show');
                    Route::post('approve', [RegistrationController::class, 'approve'])
                        ->name('registrations.approve');
                    Route::post('reject', [RegistrationController::class, 'reject'])
                        ->name('registrations.reject');
                    Route::post('cancel', [RegistrationController::class, 'cancel'])
                        ->name('registrations.cancel');
                });
            });

            /**
             * QR CODE GENERATION
             * Backend API for Flutter to request QR payloads.
             */
            Route::prefix('qr')->group(function () {
                Route::post('registrations/{registration}', [QRCodeController::class, 'generate'])
                    ->name('events.qr.generate');
            });

            /**
             * ATTENDANCE MANAGEMENT
             * Admin can scan QR codes, check in/out attendees, view attendance
             */
            Route::prefix('attendance')->group(function () {
                Route::get('/', [AttendanceController::class, 'getEventAttendance'])
                    ->name('attendance.index');
                Route::post('check-in', [AttendanceController::class, 'checkIn'])
                    ->name('attendance.check-in');
                Route::post('verify-qr', [AttendanceController::class, 'verifyQRCode'])
                    ->name('attendance.verify-qr');
                Route::post('bulk-check-in', [AttendanceController::class, 'bulkCheckIn'])
                    ->name('attendance.bulk-check-in');

                Route::prefix('{attendance}')->group(function () {
                    Route::post('check-out', [AttendanceController::class, 'checkOut'])
                        ->name('attendance.check-out');
                });

                Route::get('user/{userId}', [AttendanceController::class, 'getUserAttendance'])
                    ->name('attendance.user');
            });

            /**
             * STATISTICS & ANALYTICS
             * Admin can view event statistics, attendance rate, breakdowns
             */
            Route::prefix('statistics')->group(function () {
                Route::get('/', [StatisticsController::class, 'eventStats'])
                    ->name('statistics.event');
                Route::get('realtime', [StatisticsController::class, 'realtimeMetrics'])
                    ->name('statistics.realtime');
                Route::get('hourly-attendance', [StatisticsController::class, 'hourlyAttendance'])
                    ->name('statistics.hourly-attendance');
                Route::get('daily-attendance', [StatisticsController::class, 'dailyAttendance'])
                    ->name('statistics.daily-attendance');
                Route::get('location-stats', [StatisticsController::class, 'locationStats'])
                    ->name('statistics.location-stats');
                Route::get('user-patterns', [StatisticsController::class, 'userAttendancePatterns'])
                    ->name('statistics.user-patterns');
                Route::get('comparison', [StatisticsController::class, 'registrationAttendanceComparison'])
                    ->name('statistics.comparison');
                Route::get('no-shows', [StatisticsController::class, 'noShowAnalysis'])
                    ->name('statistics.no-shows');
            });

            /**
             * REPORTS & EXPORTS
             * Admin can export various reports in CSV/JSON format
             */
            Route::prefix('reports')->group(function () {
                Route::get('attendance/csv', [ReportController::class, 'exportAttendanceCSV'])
                    ->name('reports.attendance-csv');
                Route::get('registrations/csv', [ReportController::class, 'exportRegistrationsCSV'])
                    ->name('reports.registrations-csv');
                Route::get('summary', [ReportController::class, 'exportEventSummary'])
                    ->name('reports.summary');
                Route::get('location/csv', [ReportController::class, 'exportLocationBreakdown'])
                    ->name('reports.location-csv');
                Route::get('no-shows/csv', [ReportController::class, 'exportNoShowReport'])
                    ->name('reports.no-shows-csv');
                Route::get('time-analysis/csv', [ReportController::class, 'exportTimeAnalysis'])
                    ->name('reports.time-analysis-csv');
            });
        });
    });

    /**
     * Student QR registration
     */
    Route::post('/qr/register', [QRRegistrationController::class, 'registerViaQr'])
        ->name('qr.register');
});
