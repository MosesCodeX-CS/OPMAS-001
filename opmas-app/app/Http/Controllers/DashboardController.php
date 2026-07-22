<?php
namespace App\Http\Controllers;

use App\Models\Alarm;
use App\Models\Equipment;
use App\Services\LatestTelemetryReadingService;

class DashboardController extends Controller
{
    protected LatestTelemetryReadingService $readingService;

    public function __construct(LatestTelemetryReadingService $readingService)
    {
        $this->readingService = $readingService;
    }

    public function index()
    {
        $reading = $this->readingService->latestReading();
        $alarms = Alarm::active()
            ->orderByRaw("CASE severity WHEN 'CRITICAL' THEN 1 WHEN 'WARNING' THEN 2 WHEN 'INFO' THEN 3 ELSE 4 END")
            ->limit(5)
            ->get();
        $critical = Alarm::active()->critical()->count();
        $activeAlarms = Alarm::active()->count();
        $equipmentSummary = Equipment::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $upcomingMaintenance = Equipment::whereDate('next_service', '<=', now()->addDays(14))->orderBy('next_service')->get();

        return view('dashboard.index', compact('reading', 'alarms', 'critical', 'activeAlarms', 'equipmentSummary', 'upcomingMaintenance'));
    }

    public function latestReading()
    {
        return response()->json($this->readingService->latestReading());
    }

    public function systemStatus()
    {
        $reading = $this->readingService->latestReading();
        $activeAlarms = Alarm::active()
            ->orderByRaw("CASE severity WHEN 'CRITICAL' THEN 1 WHEN 'WARNING' THEN 2 WHEN 'INFO' THEN 3 ELSE 4 END")
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
            
        return response()->json([
            'reading' => $reading,
            'active_alarms' => $activeAlarms,
            'reading_age' => $reading ? abs(now()->diffInSeconds($reading->created_at)) : null,
        ]);
    }
}