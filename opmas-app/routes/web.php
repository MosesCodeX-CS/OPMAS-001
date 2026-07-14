<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AlarmController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SystemSettingController;
use App\Http\Controllers\TelemetryController;
use App\Http\Controllers\Auth\LoginController;

Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    // Shared / View-Only Access routes (Any logged-in user can access)
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/alarms', [AlarmController::class, 'index'])->name('alarms');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
    Route::get('/equipment', [EquipmentController::class, 'index'])->name('equipment');
    Route::get('/api/latest-reading', [DashboardController::class, 'latestReading'])->name('api.latest');
    Route::get('/api/active-alarms', [AlarmController::class, 'activeAlarms'])->name('api.active-alarms');
    Route::get('/api/system-status', [DashboardController::class, 'systemStatus'])->name('api.system-status');

    // Admin & System Admin Actions (Resolve Alarms, Edit Equipment Status, Trigger Telemetry)
    Route::middleware('role:system_admin,admin')->group(function () {
        Route::post('/alarms/{alarm}/resolve', [AlarmController::class, 'resolve'])->name('alarms.resolve');
        Route::put('/equipment/{equipment}', [EquipmentController::class, 'update'])->name('equipment.update');
        Route::post('/telemetry/generate', [TelemetryController::class, 'generate'])->name('telemetry.generate');
    });

    // System Admin Only Routes (User Management, Threshold Settings, Equipment CRUD, Delete Alarms)
    Route::middleware('role:system_admin')->group(function () {
        // User CRUD
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        // System Settings
        Route::get('/settings', [SystemSettingController::class, 'index'])->name('settings.index');
        Route::put('/settings', [SystemSettingController::class, 'update'])->name('settings.update');

        // Equipment CRUD (Create & Delete)
        Route::post('/equipment', [EquipmentController::class, 'store'])->name('equipment.store');
        Route::delete('/equipment/{equipment}', [EquipmentController::class, 'destroy'])->name('equipment.destroy');

        // Alarm Delete
        Route::delete('/alarms/{alarm}', [AlarmController::class, 'destroy'])->name('alarms.destroy');
    });
});