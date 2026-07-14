<?php
namespace App\Http\Controllers;

use App\Models\SensorReading;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $last30days = Carbon::today()->subDays(30);

        $dailyStats = SensorReading::where('created_at', '>=', $today)
            ->selectRaw('COUNT(*) as total, AVG(pressure) as average_pressure, AVG(purity) as average_purity, AVG(flow_rate) as average_flow_rate, AVG(temperature) as average_temperature, AVG(tank_level) as average_tank_level')
            ->first();

        $monthlyStats = SensorReading::where('created_at', '>=', $last30days)
            ->selectRaw('COUNT(*) as total, AVG(pressure) as average_pressure, AVG(purity) as average_purity, AVG(flow_rate) as average_flow_rate, AVG(temperature) as average_temperature, AVG(tank_level) as average_tank_level')
            ->first();

        return view('reports.index', compact('dailyStats', 'monthlyStats'));
    }
}
