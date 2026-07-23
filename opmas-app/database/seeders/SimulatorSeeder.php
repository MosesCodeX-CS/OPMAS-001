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

        $now = now();
        for ($i = 48; $i >= 0; $i--) {
            $timestamp = $now->copy()->subMinutes($i * 30);
            
            $cycle = PollCycle::updateOrCreate([
                'equipment_id' => $equipment->id,
                'started_at' => $timestamp,
            ], [
                'finished_at' => $timestamp->copy()->addSeconds(5),
                'status' => 'COMPLETED',
                'duration' => 5000,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);

            if (! DB::table('telemetry')->where('poll_cycle_id', $cycle->id)->exists()) {
                // Simulate realistic fluctuating values
                $pressure = 6.0 + sin($i / 5.0) * 0.5 + (rand(-10, 10) / 200.0);
                $purity = 93.5 + cos($i / 8.0) * 1.5 + (rand(-10, 10) / 100.0);
                $flowRate = 120 + rand(-5, 5);
                $temperature = 37 + rand(-2, 2);
                $tankLevel = 75 - ($i % 10) * 1.5 + rand(-2, 2);

                $telemetryData = [
                    ['address' => 1, 'raw' => (int)round($pressure * 10)],
                    ['address' => 2, 'raw' => (int)round($purity * 10)],
                    ['address' => 3, 'raw' => (int)round($flowRate)],
                    ['address' => 4, 'raw' => (int)round($temperature)],
                    ['address' => 5, 'raw' => (int)round($tankLevel)],
                    ['address' => 6, 'raw' => 1],
                    ['address' => 7, 'raw' => $i % 2 === 0 ? 1 : 0],
                    ['address' => 8, 'raw' => $i % 2 === 0 ? 0 : 1],
                ];

                $insertData = [];
                foreach ($telemetryData as $data) {
                    $regId = RegisterDefinition::where('equipment_id', $equipment->id)->where('address', $data['address'])->value('id');
                    if ($regId) {
                        $insertData[] = [
                            'poll_cycle_id' => $cycle->id,
                            'register_definition_id' => $regId,
                            'raw_value' => $data['raw'],
                            'device_timestamp' => $timestamp,
                            'collector_timestamp' => $timestamp,
                            'quality' => 'GOOD',
                            'poll_duration_ms' => 100,
                            'created_at' => $timestamp,
                            'updated_at' => $timestamp,
                        ];
                    }
                }

                if (!empty($insertData)) {
                    DB::table('telemetry')->insert($insertData);
                }
            }
        }
    }
}
