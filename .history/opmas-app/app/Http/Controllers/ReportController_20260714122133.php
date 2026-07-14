<?php
namespace App\Http\Controllers;

use App\Models\Alarm;
use App\Models\SensorReading;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $last30days = Carbon::today()->subDays(30);
        $last7days = Carbon::today()->subDays(7);

        $dailyStats = SensorReading::where('created_at', '>=', $today)
            ->selectRaw('COUNT(*) as total, AVG(pressure) as average_pressure, AVG(purity) as average_purity, AVG(flow_rate) as average_flow_rate, AVG(temperature) as average_temperature, AVG(tank_level) as average_tank_level')
            ->first();

        $weeklyStats = SensorReading::where('created_at', '>=', $last7days)
            ->selectRaw('COUNT(*) as total, AVG(pressure) as average_pressure, AVG(purity) as average_purity, AVG(flow_rate) as average_flow_rate, AVG(temperature) as average_temperature, AVG(tank_level) as average_tank_level')
            ->first();

        $monthlyStats = SensorReading::where('created_at', '>=', $last30days)
            ->selectRaw('COUNT(*) as total, AVG(pressure) as average_pressure, AVG(purity) as average_purity, AVG(flow_rate) as average_flow_rate, AVG(temperature) as average_temperature, AVG(tank_level) as average_tank_level')
            ->first();

        $dailyRange = SensorReading::where('created_at', '>=', $today)
            ->selectRaw('MIN(pressure) as min_pressure, MAX(pressure) as max_pressure, MIN(purity) as min_purity, MAX(purity) as max_purity, MIN(flow_rate) as min_flow_rate, MAX(flow_rate) as max_flow_rate, MIN(temperature) as min_temperature, MAX(temperature) as max_temperature, MIN(tank_level) as min_tank_level, MAX(tank_level) as max_tank_level')
            ->first();

        $monthlyRange = SensorReading::where('created_at', '>=', $last30days)
            ->selectRaw('MIN(pressure) as min_pressure, MAX(pressure) as max_pressure, MIN(purity) as min_purity, MAX(purity) as max_purity, MIN(flow_rate) as min_flow_rate, MAX(flow_rate) as max_flow_rate, MIN(temperature) as min_temperature, MAX(temperature) as max_temperature, MIN(tank_level) as min_tank_level, MAX(tank_level) as max_tank_level')
            ->first();

        $alarmSummary = Alarm::selectRaw('severity, count(*) as total')
            ->groupBy('severity')
            ->pluck('total', 'severity');

        return view('reports.index', compact('dailyStats', 'weeklyStats', 'monthlyStats', 'dailyRange', 'monthlyRange', 'alarmSummary'));
    }

    public function export(Request $request)
    {
        $last30days = Carbon::today()->subDays(30);
        $readings = SensorReading::where('created_at', '>=', $last30days)->orderBy('created_at')->get();

        $filename = 'sensor_readings_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($readings) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Timestamp', 'Pressure', 'Purity', 'Flow Rate', 'Temperature', 'Tank Level', 'Compressor Status', 'Bed A Status', 'Bed B Status']);

            foreach ($readings as $reading) {
                fputcsv($handle, [
                    $reading->created_at->toDateTimeString(),
                    $reading->pressure,
                    $reading->purity,
                    $reading->flow_rate,
                    $reading->temperature,
                    $reading->tank_level,
                    $reading->compressor_status,
                    $reading->bed_a_status,
                    $reading->bed_b_status,
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
