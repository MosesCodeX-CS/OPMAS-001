<?php

namespace Database\Seeders;

use App\Models\Driver;
use App\Models\Equipment;
use App\Models\PollCycle;
use App\Models\PollProfile;
use App\Models\RegisterDefinition;
use App\Models\RegisterDefinitionVersion;
use App\Models\RegisterGroup;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SimulatorSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $driver = Driver::updateOrCreate([
            'name' => 'Local Modbus Simulator Driver',
        ], [
            'protocol' => 'MODBUS_TCP',
            'supports_holding_registers' => 1,
            'supports_input_registers' => 1,
            'supports_coils' => 1,
            'supports_discrete_inputs' => 1,
            'supports_writes' => 0,
            'max_registers_per_request' => 125,
            'max_concurrent_requests' => 4,
        ]);

        $siteId = DB::table('sites')->where('code', 'KIJABE-ICU')->value('id') ?? 1;

        $equipment = Equipment::updateOrCreate([
            'code' => 'SIM-127-5020',
        ], [
            'site_id' => $siteId,
            'driver_id' => $driver->id,
            'name' => 'Local Modbus Simulator',
            'manufacturer' => 'Simulator',
            'model' => 'LocalSim',
            'device_type' => 'SIMULATOR',
            'location' => 'Localhost',
            'ip_address' => '127.0.0.1',
            'port' => 5020,
            'unit_id' => 1,
            'poll_interval' => 5,
            'enabled' => 1,
            'status' => 'ONLINE',
        ]);

        $group = RegisterGroup::updateOrCreate([
            'equipment_id' => $equipment->id,
            'name' => 'Simulator Telemetry',
        ], [
            'display_order' => 1,
        ]);

        $profile = PollProfile::updateOrCreate([
            'name' => 'Simulator Fast',
        ], [
            'interval_seconds' => 5,
            'priority' => 'CRITICAL',
            'enabled' => true,
        ]);

        $registers = [
            ['name' => 'pressure', 'address' => 1, 'register_type' => 'HOLDING', 'data_type' => 'INTEGER', 'unit' => 'bar', 'scale' => 0.1, 'decimals' => 2],
            ['name' => 'purity', 'address' => 2, 'register_type' => 'HOLDING', 'data_type' => 'INTEGER', 'unit' => '%', 'scale' => 0.1, 'decimals' => 2],
            ['name' => 'flow_rate', 'address' => 3, 'register_type' => 'HOLDING', 'data_type' => 'INTEGER', 'unit' => 'L/min', 'scale' => 1, 'decimals' => 1],
            ['name' => 'temperature', 'address' => 4, 'register_type' => 'HOLDING', 'data_type' => 'INTEGER', 'unit' => '°C', 'scale' => 1, 'decimals' => 1],
            ['name' => 'tank_level', 'address' => 5, 'register_type' => 'HOLDING', 'data_type' => 'INTEGER', 'unit' => '%', 'scale' => 1, 'decimals' => 1],
            ['name' => 'compressor_status', 'address' => 6, 'register_type' => 'HOLDING', 'data_type' => 'INTEGER', 'unit' => null, 'scale' => 1, 'decimals' => 0],
            ['name' => 'bed_a_status', 'address' => 7, 'register_type' => 'HOLDING', 'data_type' => 'INTEGER', 'unit' => null, 'scale' => 1, 'decimals' => 0],
            ['name' => 'bed_b_status', 'address' => 8, 'register_type' => 'HOLDING', 'data_type' => 'INTEGER', 'unit' => null, 'scale' => 1, 'decimals' => 0],
        ];

        foreach ($registers as $register) {
            $definition = RegisterDefinition::updateOrCreate([
                'equipment_id' => $equipment->id,
                'address' => $register['address'],
                'register_type' => $register['register_type'],
            ], [
                'register_group_id' => $group->id,
                'poll_profile_id' => $profile->id,
                'data_type' => $register['data_type'],
                'display_order' => $register['address'] + 1,
                'enabled' => true,
                'graph_enabled' => in_array($register['name'], ['pressure', 'purity', 'flow_rate', 'temperature', 'tank_level']),
            ]);

            RegisterDefinitionVersion::updateOrCreate([
                'register_definition_id' => $definition->id,
                'effective_from' => now()->startOfDay(),
            ], [
                'name' => ucfirst(str_replace('_', ' ', $register['name'])),
                'description' => 'Live simulator register for ' . str_replace('_', ' ', $register['name']),
                'scale' => $register['scale'],
                'offset' => 0,
                'unit' => $register['unit'],
                'decimals' => $register['decimals'],
            ]);
        }

        $latestCycle = PollCycle::updateOrCreate([
            'equipment_id' => $equipment->id,
            'started_at' => now()->subSeconds(5),
        ], [
            'finished_at' => now(),
            'status' => 'COMPLETED',
            'duration' => 5000,
        ]);

        if (! DB::table('telemetry')->where('poll_cycle_id', $latestCycle->id)->exists()) {
            DB::table('telemetry')->insert([
                [
                    'poll_cycle_id' => $latestCycle->id,
                    'register_definition_id' => RegisterDefinition::where('equipment_id', $equipment->id)->where('address', 1)->value('id'),
                    'raw_value' => 60,
                    'device_timestamp' => now()->subSeconds(5),
                    'collector_timestamp' => now(),
                    'quality' => 'GOOD',
                    'poll_duration_ms' => 100,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'poll_cycle_id' => $latestCycle->id,
                    'register_definition_id' => RegisterDefinition::where('equipment_id', $equipment->id)->where('address', 2)->value('id'),
                    'raw_value' => 930,
                    'device_timestamp' => now()->subSeconds(5),
                    'collector_timestamp' => now(),
                    'quality' => 'GOOD',
                    'poll_duration_ms' => 100,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'poll_cycle_id' => $latestCycle->id,
                    'register_definition_id' => RegisterDefinition::where('equipment_id', $equipment->id)->where('address', 3)->value('id'),
                    'raw_value' => 125,
                    'device_timestamp' => now()->subSeconds(5),
                    'collector_timestamp' => now(),
                    'quality' => 'GOOD',
                    'poll_duration_ms' => 100,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'poll_cycle_id' => $latestCycle->id,
                    'register_definition_id' => RegisterDefinition::where('equipment_id', $equipment->id)->where('address', 4)->value('id'),
                    'raw_value' => 38,
                    'device_timestamp' => now()->subSeconds(5),
                    'collector_timestamp' => now(),
                    'quality' => 'GOOD',
                    'poll_duration_ms' => 100,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'poll_cycle_id' => $latestCycle->id,
                    'register_definition_id' => RegisterDefinition::where('equipment_id', $equipment->id)->where('address', 5)->value('id'),
                    'raw_value' => 72,
                    'device_timestamp' => now()->subSeconds(5),
                    'collector_timestamp' => now(),
                    'quality' => 'GOOD',
                    'poll_duration_ms' => 100,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'poll_cycle_id' => $latestCycle->id,
                    'register_definition_id' => RegisterDefinition::where('equipment_id', $equipment->id)->where('address', 6)->value('id'),
                    'raw_value' => 1,
                    'device_timestamp' => now()->subSeconds(5),
                    'collector_timestamp' => now(),
                    'quality' => 'GOOD',
                    'poll_duration_ms' => 100,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'poll_cycle_id' => $latestCycle->id,
                    'register_definition_id' => RegisterDefinition::where('equipment_id', $equipment->id)->where('address', 7)->value('id'),
                    'raw_value' => 1,
                    'device_timestamp' => now()->subSeconds(5),
                    'collector_timestamp' => now(),
                    'quality' => 'GOOD',
                    'poll_duration_ms' => 100,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'poll_cycle_id' => $latestCycle->id,
                    'register_definition_id' => RegisterDefinition::where('equipment_id', $equipment->id)->where('address', 8)->value('id'),
                    'raw_value' => 0,
                    'device_timestamp' => now()->subSeconds(5),
                    'collector_timestamp' => now(),
                    'quality' => 'GOOD',
                    'poll_duration_ms' => 100,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }
    }
}
