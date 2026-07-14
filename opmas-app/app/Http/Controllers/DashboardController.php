<?php
namespace App\Http\Controllers;

use App\Models\SensorReading;
use App\Models\Alarm;

class DashboardController extends Controller
{
    public function index()
    {
        $reading  = SensorReading::latest_reading();
        $alarms   = Alarm::active()->orderBy('severity')->limit(5)->get();
        $critical = Alarm::active()->critical()->count();

        return view('dashboard.index', compact('reading', 'alarms', 'critical'));
    }

    public function latestReading()
    {
        return response()->json(SensorReading::latest_reading());
    }
}