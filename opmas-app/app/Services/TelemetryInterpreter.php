<?php

namespace App\Services;

use App\Models\Telemetry;
use App\Models\RegisterDefinitionVersion;

class TelemetryInterpreter
{
    public function interpret(Telemetry $telemetry): array
    {
        $definition = $telemetry->definition;
        $version = $this->activeVersion($definition);

        if (! $version || str_starts_with(strtolower($version->name ?? ''), 'unknown register')) {
            $fallback = $this->fallbackVersion($telemetry);
            if ($fallback !== null) {
                $version = $fallback;
            }
        }

        if (! $version) {
            return [
                'name' => null,
                'value' => $telemetry->raw_value,
                'raw_value' => $telemetry->raw_value,
                'unit' => null,
                'quality' => $telemetry->quality,
            ];
        }

        $scaled = (float) $telemetry->raw_value * (float) $version->scale + (float) $version->offset;

        return [
            'name' => $version->name,
            'value' => round($scaled, (int) $version->decimals),
            'raw_value' => $telemetry->raw_value,
            'unit' => $version->unit,
            'quality' => $telemetry->quality,
        ];
    }

    protected function activeVersion($definition): ?RegisterDefinitionVersion
    {
        if (! $definition) {
            return null;
        }

        if ($definition->relationLoaded('versions')) {
            return $definition->versions->sortByDesc('effective_from')->first();
        }

        return $definition->activeVersion();
    }

    protected function fallbackVersion(Telemetry $telemetry): ?RegisterDefinitionVersion
    {
        $address = $telemetry->definition->address ?? null;
        if ($address === null) {
            return null;
        }

        $defaults = [
            0 => ['name' => 'Pressure', 'scale' => 0.1, 'offset' => 0.0, 'unit' => 'bar', 'decimals' => 2],
            1 => ['name' => 'Purity', 'scale' => 0.1, 'offset' => 0.0, 'unit' => '%', 'decimals' => 2],
            2 => ['name' => 'Flow Rate', 'scale' => 1.0, 'offset' => 0.0, 'unit' => 'L/min', 'decimals' => 1],
            3 => ['name' => 'Temperature', 'scale' => 1.0, 'offset' => 0.0, 'unit' => '°C', 'decimals' => 1],
            4 => ['name' => 'Tank Level', 'scale' => 1.0, 'offset' => 0.0, 'unit' => '%', 'decimals' => 1],
            5 => ['name' => 'Compressor Status', 'scale' => 1.0, 'offset' => 0.0, 'unit' => null, 'decimals' => 0],
            6 => ['name' => 'Bed A Status', 'scale' => 1.0, 'offset' => 0.0, 'unit' => null, 'decimals' => 0],
            7 => ['name' => 'Bed B Status', 'scale' => 1.0, 'offset' => 0.0, 'unit' => null, 'decimals' => 0],
            8 => ['name' => 'Bed B Status', 'scale' => 1.0, 'offset' => 0.0, 'unit' => null, 'decimals' => 0],
        ];

        if (! array_key_exists($address, $defaults)) {
            return null;
        }

        $fallback = $defaults[$address];
        $version = new RegisterDefinitionVersion();
        $version->name = $fallback['name'];
        $version->scale = $fallback['scale'];
        $version->offset = $fallback['offset'];
        $version->unit = $fallback['unit'];
        $version->decimals = $fallback['decimals'];

        return $version;
    }
}
