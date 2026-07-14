<?php
namespace App\Http\Controllers;

use App\Models\Alarm;
use App\Models\SensorReading;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->query('start_date', now()->subDays(7)->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());

        // Parse dates safely
        try {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();
        } catch (\Exception $e) {
            $start = now()->subDays(7)->startOfDay();
            $end = now()->endOfDay();
            $startDate = $start->toDateString();
            $endDate = $end->toDateString();
        }

        // Summary Statistics (calculated relative to today/last7/last30 for dashboard comparisons)
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

        // Historical readings for Chart.js in the filtered range
        $chartReadings = SensorReading::whereBetween('created_at', [$start, $end])
            ->orderBy('created_at')
            ->limit(500)
            ->get();

        return view('reports.index', compact(
            'dailyStats', 'weeklyStats', 'monthlyStats',
            'dailyRange', 'monthlyRange', 'alarmSummary',
            'chartReadings', 'startDate', 'endDate'
        ));
    }

    public function export(Request $request)
    {
        $startDate = $request->query('start_date', now()->subDays(30)->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());

        try {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();
        } catch (\Exception $e) {
            $start = now()->subDays(30)->startOfDay();
            $end = now()->endOfDay();
        }

        $readings = SensorReading::whereBetween('created_at', [$start, $end])
            ->orderBy('created_at')
            ->get();

        $filename = 'sensor_readings_' . $start->format('Ymd') . '_to_' . $end->format('Ymd') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($readings) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Timestamp', 'Pressure (bar)', 'Purity (%)', 'Flow Rate (L/min)', 'Temperature (C)', 'Tank Level (%)', 'Compressor Status', 'Bed A Status', 'Bed B Status']);

            foreach ($readings as $reading) {
                fputcsv($handle, [
                    $reading->created_at->toDateTimeString(),
                    $reading->pressure,
                    $reading->purity,
                    $reading->flow_rate,
                    $reading->temperature,
                    $reading->tank_level,
                    $reading->compressorStatusLabel(),
                    $reading->bed_a_status ? 'Active' : 'Idle',
                    $reading->bed_b_status ? 'Active' : 'Idle',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
