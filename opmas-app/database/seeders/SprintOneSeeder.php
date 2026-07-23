<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SprintOneSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('hospitals')->updateOrInsert(
            ['code' => 'KIJABE'],
            ['name' => 'Kijabe Hospital', 'country' => 'Kenya', 'notes' => 'Initial local test hospital']
        );

        DB::table('sites')->updateOrInsert(
            ['code' => 'KIJABE-ICU'],
            ['hospital_id' => DB::table('hospitals')->where('code', 'KIJABE')->value('id'), 'name' => 'ICU Wing', 'location' => 'Building A', 'enabled' => true]
        );

        DB::table('drivers')->updateOrInsert(
            ['name' => 'Schneider M221 Driver'],
            ['protocol' => 'MODBUS_TCP', 'supports_holding_registers' => true, 'supports_input_registers' => true, 'supports_coils' => true, 'supports_discrete_inputs' => true, 'supports_writes' => false, 'max_registers_per_request' => 125, 'max_concurrent_requests' => 1]
        );

        $siteId = DB::table('sites')->where('code', 'KIJABE-ICU')->value('id');
        $driverId = DB::table('drivers')->where('name', 'Schneider M221 Driver')->value('id');

        $equipmentId = DB::table('equipment')->insertGetId([
            'site_id' => $siteId,
            'driver_id' => $driverId,
            'code' => 'PLC-001',
            'name' => 'ICU PLC',
            'manufacturer' => 'Schneider',
            'model' => 'M221',
            'device_type' => 'PLC',
            'location' => 'ICU Room',
            'ip_address' => '127.0.0.1',
            'port' => 5020,
            'unit_id' => 1,
            'poll_interval' => 5,
            'enabled' => true,
            'status' => 'ONLINE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $groupId = DB::table('register_groups')->insertGetId([
            'equipment_id' => $equipmentId,
            'name' => 'Pressure',
            'display_order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $profileId = DB::table('poll_profiles')->insertGetId([
            'name' => 'Fast',
            'interval_seconds' => 5,
            'priority' => 'CRITICAL',
            'enabled' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $registerId = DB::table('register_definitions')->insertGetId([
            'equipment_id' => $equipmentId,
            'register_group_id' => $groupId,
            'poll_profile_id' => $profileId,
            'address' => 40001,
            'register_type' => 'HOLDING',
            'data_type' => 'INTEGER',
            'display_order' => 1,
            'enabled' => true,
            'graph_enabled' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('register_definition_versions')->insert([
            'register_definition_id' => $registerId,
            'name' => 'Unknown Register 40001',
            'description' => 'Initial version for local testing',
            'scale' => 1,
            'offset' => 0,
            'unit' => 'bar',
            'decimals' => 2,
            'effective_from' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('alarm_rules')->insert([
            'register_definition_id' => $registerId,
            'condition' => '<',
            'threshold' => '5',
            'severity' => 'CRITICAL',
            'enabled' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $pollCycleId = DB::table('poll_cycles')->insertGetId([
            'equipment_id' => $equipmentId,
            'started_at' => now()->subMinute(),
            'finished_at' => now(),
            'status' => 'COMPLETED',
            'duration' => 120,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('telemetry')->insert([
            'poll_cycle_id' => $pollCycleId,
            'register_definition_id' => $registerId,
            'raw_value' => 4.8,
            'device_timestamp' => now()->subSeconds(15),
            'collector_timestamp' => now(),
            'quality' => 'GOOD',
            'poll_duration_ms' => 120,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
