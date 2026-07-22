<?php

namespace Tests\Feature;

use App\Models\RegisterDefinition;
use App\Models\RegisterDefinitionVersion;
use App\Models\Telemetry;
use App\Services\TelemetryInterpreter;
use Illuminate\Support\Collection;
use Tests\TestCase;

class TelemetryInterpretationTest extends TestCase
{
    public function test_raw_telemetry_can_be_interpreted_using_versioned_metadata(): void
    {
        $version = new RegisterDefinitionVersion([
            'name' => 'Pressure',
            'scale' => 1.5,
            'offset' => 2.0,
            'unit' => 'bar',
            'decimals' => 2,
            'effective_from' => now()->subDay(),
        ]);

        $definition = new RegisterDefinition();
        $definition->setRelation('versions', new Collection([$version]));

        $telemetry = new Telemetry([
            'raw_value' => 4.8,
            'quality' => 'GOOD',
        ]);
        $telemetry->setRelation('definition', $definition);

        $result = (new TelemetryInterpreter())->interpret($telemetry);

        $this->assertSame('Pressure', $result['name']);
        $this->assertSame(9.2, $result['value']);
        $this->assertSame('bar', $result['unit']);
        $this->assertSame(4.8, $result['raw_value']);
    }
}
