<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AlarmController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\Auth\LoginController;

Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/alarms', [AlarmController::class, 'index'])->name('alarms');
    Route::post('/alarms/{alarm}/resolve', [AlarmController::class, 'resolve'])->name('alarms.resolve');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
    Route::get('/equipment', [EquipmentController::class, 'index'])->name('equipment');
    Route::get('/api/latest-reading', [DashboardController::class, 'latestReading'])->name('api.latest');
});