<?php
namespace App\Http\Controllers;

use App\Models\Alarm;
use App\Models\Telemetry;
use App\Models\PollCycle;
use App\Models\RegisterDefinition;
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

        // Summary Statistics
        $today = Carbon::today();
        $last30days = Carbon::today()->subDays(30);
        $last7days = Carbon::today()->subDays(7);

        $dailyStats = $this->getPeriodStats($today);
        $weeklyStats = $this->getPeriodStats($last7days);
        $monthlyStats = $this->getPeriodStats($last30days);

        $dailyRange = $dailyStats;
        $monthlyRange = $monthlyStats;

        $alarmSummary = Alarm::selectRaw('severity, count(*) as total')
            ->groupBy('severity')
            ->pluck('total', 'severity');

        // Historical readings for Chart.js in the filtered range
        $pollCycles = PollCycle::whereBetween('started_at', [$start, $end])
            ->orderBy('started_at', 'asc')
            ->limit(500)
            ->get();

        $cycleIds = $pollCycles->pluck('id');

        $telemetryRows = Telemetry::whereIn('poll_cycle_id', $cycleIds)
            ->with('definition.versions')
            ->get()
            ->groupBy('poll_cycle_id');

        $addressScheme = null;
        if ($telemetryRows->isNotEmpty()) {
            $firstGroup = $telemetryRows->first();
            $addressScheme = $this->detectAddressSchemeFromStats($firstGroup);
        }

        $interpreter = new \App\Services\TelemetryInterpreter();

        $chartReadings = [];
        foreach ($pollCycles as $cycle) {
            $rowsInCycle = $telemetryRows->get($cycle->id);
            if (!$rowsInCycle) {
                continue;
            }

            $reading = new \stdClass();
            $reading->created_at = $cycle->started_at;
            $reading->pressure = null;
            $reading->purity = null;
            $reading->flow_rate = null;
            $reading->temperature = null;
            $reading->tank_level = null;
            $reading->compressor_status = null;
            $reading->bed_a_status = null;
            $reading->bed_b_status = null;

            foreach ($rowsInCycle as $telemetry) {
                $interpretation = $interpreter->interpret($telemetry);
                $field = $this->normalizeFieldName(
                    (string) $interpretation['name'],
                    $telemetry->definition->address ?? null,
                    $addressScheme
                );

                if ($field) {
                    $reading->{$field} = $interpretation['value'] ?? $interpretation['raw_value'];
                }
            }

            $chartReadings[] = $reading;
        }

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

        $pollCycles = PollCycle::whereBetween('started_at', [$start, $end])
            ->orderBy('started_at', 'asc')
            ->get();

        $cycleIds = $pollCycles->pluck('id');

        $telemetryRows = Telemetry::whereIn('poll_cycle_id', $cycleIds)
            ->with('definition.versions')
            ->get()
            ->groupBy('poll_cycle_id');

        $addressScheme = null;
        if ($telemetryRows->isNotEmpty()) {
            $firstGroup = $telemetryRows->first();
            $addressScheme = $this->detectAddressSchemeFromStats($firstGroup);
        }

        $interpreter = new \App\Services\TelemetryInterpreter();

        $filename = 'sensor_readings_' . $start->format('Ymd') . '_to_' . $end->format('Ymd') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($pollCycles, $telemetryRows, $interpreter, $addressScheme) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Timestamp', 'Pressure (bar)', 'Purity (%)', 'Flow Rate (L/min)', 'Temperature (C)', 'Tank Level (%)', 'Compressor Status', 'Bed A Status', 'Bed B Status']);

            foreach ($pollCycles as $cycle) {
                $rowsInCycle = $telemetryRows->get($cycle->id);
                if (!$rowsInCycle) {
                    continue;
                }

                $reading = new \stdClass();
                $reading->pressure = null;
                $reading->purity = null;
                $reading->flow_rate = null;
                $reading->temperature = null;
                $reading->tank_level = null;
                $reading->compressor_status = null;
                $reading->bed_a_status = null;
                $reading->bed_b_status = null;

                foreach ($rowsInCycle as $telemetry) {
                    $interpretation = $interpreter->interpret($telemetry);
                    $field = $this->normalizeFieldName(
                        (string) $interpretation['name'],
                        $telemetry->definition->address ?? null,
                        $addressScheme
                    );

                    if ($field) {
                        $reading->{$field} = $interpretation['value'] ?? $interpretation['raw_value'];
                    }
                }

                $compressorLabel = match ((int)$reading->compressor_status) {
                    1       => 'RUNNING',
                    2       => 'FAULT',
                    default => 'OFF',
                };

                fputcsv($handle, [
                    $cycle->started_at,
                    $reading->pressure,
                    $reading->purity,
                    $reading->flow_rate,
                    $reading->temperature,
                    $reading->tank_level,
                    $compressorLabel,
                    $reading->bed_a_status ? 'Active' : 'Idle',
                    $reading->bed_b_status ? 'Active' : 'Idle',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    protected function getPeriodStats(Carbon $sinceDate, ?string $addressScheme = null)
    {
        $stats = Telemetry::query()
            ->selectRaw('register_definition_id, AVG(raw_value) as avg_raw, MIN(raw_value) as min_raw, MAX(raw_value) as max_raw')
            ->join('poll_cycles', 'telemetry.poll_cycle_id', '=', 'poll_cycles.id')
            ->where('poll_cycles.started_at', '>=', $sinceDate)
            ->groupBy('register_definition_id')
            ->with('definition.versions')
            ->get();

        if ($addressScheme === null) {
            $addressScheme = $this->detectAddressSchemeFromStats($stats);
        }

        $result = new \stdClass();
        $result->total = PollCycle::where('started_at', '>=', $sinceDate)->count();
        $result->average_pressure = null;
        $result->average_purity = null;
        $result->average_flow_rate = null;
        $result->average_temperature = null;
        $result->average_tank_level = null;
        $result->min_pressure = null;
        $result->max_pressure = null;
        $result->min_purity = null;
        $result->max_purity = null;
        $result->min_flow_rate = null;
        $result->max_flow_rate = null;
        $result->min_temperature = null;
        $result->max_temperature = null;
        $result->min_tank_level = null;
        $result->max_tank_level = null;

        foreach ($stats as $stat) {
            $definition = $stat->definition;
            if (!$definition) {
                continue;
            }

            $activeVersion = $definition->relationLoaded('versions')
                ? $definition->versions->sortByDesc('effective_from')->first()
                : $definition->activeVersion();

            $scale = 1.0;
            $offset = 0.0;
            $name = '';

            if ($activeVersion) {
                $scale = (float) $activeVersion->scale;
                $offset = (float) $activeVersion->offset;
                $name = $activeVersion->name;
            } else {
                $address = $definition->address;
                $defaults = [
                    0 => ['name' => 'Pressure', 'scale' => 0.1, 'offset' => 0.0],
                    1 => ['name' => 'Purity', 'scale' => 0.1, 'offset' => 0.0],
                    2 => ['name' => 'Flow Rate', 'scale' => 1.0, 'offset' => 0.0],
                    3 => ['name' => 'Temperature', 'scale' => 1.0, 'offset' => 0.0],
                    4 => ['name' => 'Tank Level', 'scale' => 1.0, 'offset' => 0.0],
                ];
                if (isset($defaults[$address])) {
                    $scale = $defaults[$address]['scale'];
                    $offset = $defaults[$address]['offset'];
                    $name = $defaults[$address]['name'];
                }
            }

            $field = $this->normalizeFieldName($name, $definition->address, $addressScheme);
            if ($field) {
                $result->{"average_" . $field} = (float)$stat->avg_raw * $scale + $offset;
                $result->{"min_" . $field} = (float)$stat->min_raw * $scale + $offset;
                $result->{"max_" . $field} = (float)$stat->max_raw * $scale + $offset;
            }
        }

        return $result;
    }

    protected function detectAddressSchemeFromStats($stats): ?string
    {
        $addresses = $stats
            ->map(fn ($row) => $row->definition->address ?? null)
            ->filter(fn ($address) => $address !== null)
            ->unique();

        if ($addresses->isEmpty()) {
            return null;
        }

        $containsZero = $addresses->contains(0);
        $containsEight = $addresses->contains(8);
        $allZeroBased = $addresses->every(fn ($address) => $address >= 0 && $address <= 7);
        $allOneBased = $addresses->every(fn ($address) => $address >= 1 && $address <= 8);

        if ($containsZero || ($allZeroBased && ! $containsEight)) {
            return 'zero-based';
        }

        if ($containsEight || $allOneBased) {
            return 'one-based';
        }

        return null;
    }

    protected function normalizeFieldName(string $name, ?int $address = null, ?string $addressScheme = null): ?string
    {
        $normalized = trim(strtolower(str_replace([' ', '_', '-', '%', '°', '₂'], [' ', ' ', ' ', '', '', '2'], $name)));

        if (preg_match('/^unknown register\s*(\d+)$/', $normalized, $matches)) {
            return $this->fieldFromAddress((int) $matches[1], $addressScheme);
        }

        if (! empty($normalized)) {
            return match ($normalized) {
                'pressure', 'pressure bar' => 'pressure',
                'o2 purity', 'o2 purity', 'oxygen purity', 'purity' => 'purity',
                'flow rate', 'flow_rate', 'flow' => 'flow_rate',
                'temperature' => 'temperature',
                'tank level', 'tank_level' => 'tank_level',
                'compressor status', 'compressor_status' => 'compressor_status',
                'bed a status', 'bed_a_status', 'bed a' => 'bed_a_status',
                'bed b status', 'bed_b_status', 'bed b' => 'bed_b_status',
                default => null,
            };
        }

        return $this->fieldFromAddress($address, $addressScheme);
    }

    protected function fieldFromAddress(?int $address, ?string $addressScheme = null): ?string
    {
        if ($address === null) {
            return null;
        }

        if ($addressScheme === 'zero-based') {
            return match ($address) {
                0 => 'pressure',
                1 => 'purity',
                2 => 'flow_rate',
                3 => 'temperature',
                4 => 'tank_level',
                5 => 'compressor_status',
                6 => 'bed_a_status',
                7 => 'bed_b_status',
                default => null,
            };
        }

        if ($addressScheme === 'one-based') {
            return match ($address) {
                1 => 'pressure',
                2 => 'purity',
                3 => 'flow_rate',
                4 => 'temperature',
                5 => 'tank_level',
                6 => 'compressor_status',
                7 => 'bed_a_status',
                8 => 'bed_b_status',
                default => null,
            };
        }

        return match ($address) {
            0 => 'pressure',
            1 => 'purity',
            2 => 'flow_rate',
            3 => 'temperature',
            4 => 'tank_level',
            5 => 'compressor_status',
            6 => 'bed_a_status',
            7 => 'bed_b_status',
            8 => 'bed_b_status',
            default => null,
        };
    }
}
