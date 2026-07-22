<?php

namespace App\Services;

use App\Models\Telemetry;
use App\Services\TelemetryInterpreter;
use Illuminate\Database\Query\JoinClause;
use stdClass;

class LatestTelemetryReadingService
{
    protected TelemetryInterpreter $interpreter;

    public function __construct()
    {
        $this->interpreter = new TelemetryInterpreter();
    }

    public function latestReading(?int $equipmentId = null): ?stdClass
    {
        $latestPerRegister = Telemetry::query()
            ->selectRaw('register_definition_id, MAX(collector_timestamp) AS max_collector_timestamp')
            ->when($equipmentId !== null, function ($query) use ($equipmentId) {
                $query->join('poll_cycles', 'telemetry.poll_cycle_id', '=', 'poll_cycles.id')
                    ->where('poll_cycles.equipment_id', $equipmentId);
            })
            ->groupBy('register_definition_id');

        $telemetryRows = Telemetry::query()
            ->select('telemetry.*')
            ->joinSub($latestPerRegister, 'latest', function (JoinClause $join) {
                $join->on('telemetry.register_definition_id', '=', 'latest.register_definition_id')
                    ->on('telemetry.collector_timestamp', '=', 'latest.max_collector_timestamp');
            })
            ->with('definition.versions')
            ->get()
            ->unique('register_definition_id');

        if ($telemetryRows->isEmpty()) {
            return null;
        }

        $addressScheme = $this->detectAddressScheme($telemetryRows);

        $reading = new stdClass();
        $reading->pressure = null;
        $reading->purity = null;
        $reading->flow_rate = null;
        $reading->temperature = null;
        $reading->tank_level = null;
        $reading->compressor_status = null;
        $reading->bed_a_status = null;
        $reading->bed_b_status = null;
        $reading->created_at = null;
        $reading->updated_at = null;

        foreach ($telemetryRows as $telemetry) {
            $interpretation = $this->interpreter->interpret($telemetry);
            $field = $this->normalizeFieldName(
                (string) $interpretation['name'], 
                $telemetry->definition->address ?? null,
                $addressScheme
            );

            if (! $field) {
                continue;
            }

            $reading->{$field} = $interpretation['value'] ?? $interpretation['raw_value'];
            $createdAt = $telemetry->collector_timestamp ?: $telemetry->created_at;
            $updatedAt = $telemetry->updated_at ?: $createdAt;

            if ($createdAt !== null && ($reading->created_at === null || $createdAt > $reading->created_at)) {
                $reading->created_at = $createdAt;
            }

            if ($updatedAt !== null && ($reading->updated_at === null || $updatedAt > $reading->updated_at)) {
                $reading->updated_at = $updatedAt;
            }
        }

        if ($reading->created_at === null) {
            return null;
        }

        return $reading;
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

    protected function detectAddressScheme($telemetryRows): ?string
    {
        $addresses = $telemetryRows
            ->map(fn ($row) => $row->definition->address)
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
}
