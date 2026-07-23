<?php

namespace Database\Seeders;

use App\Models\Alarm;
use App\Models\Equipment;

use App\Models\User;
use Database\Seeders\SprintOneSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate([
            'email' => 'system_admin@example.com',
        ], [
            'name' => 'System Admin',
            'password' => 'system_admin_password',
            'role' => 'system_admin',
        ]);

        User::updateOrCreate([
            'email' => 'admin@example.com',
        ], [
            'name' => 'Admin User',
            'password' => 'admin_password',
            'role' => 'admin',
        ]);

        User::updateOrCreate([
            'email' => 'user@example.com',
        ], [
            'name' => 'Normal User',
            'password' => 'user_password',
            'role' => 'user',
        ]);

        $settings = [
            'pressure_low' => ['4.0', 'Critical low threshold for pressure (bar)'],
            'purity_low' => ['90.0', 'Critical low threshold for oxygen purity (%)'],
            'flow_rate_low' => ['1.0', 'Warning low threshold for flow rate (L/min)'],
            'temperature_high' => ['80.0', 'Warning high threshold for temperature (°C)'],
            'tank_level_low' => ['10.0', 'Warning low threshold for tank level (%)'],
            'simulation_mode' => ['1', 'Toggle simulation telemetry generation (1=ON, 0=OFF)'],
        ];

        foreach ($settings as $key => $data) {
            \Illuminate\Support\Facades\DB::table('system_settings')->updateOrInsert(
                ['key' => $key],
                [
                    'value' => $data[0],
                    'description' => $data[1],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        Equipment::updateOrCreate([
            'code' => 'COMP-01',
        ], [
            'name' => 'Oxygen Compressor',
            'status' => 'ONLINE',
            'last_service' => now()->subDays(14)->toDateString(),
            'next_service' => now()->addDays(16)->toDateString(),
            'notes' => 'Primary compressor for oxygen generation.',
        ]);

        Equipment::updateOrCreate([
            'code' => 'PSA-A',
        ], [
            'name' => 'PSA Tower A',
            'status' => 'ONLINE',
            'last_service' => now()->subDays(30)->toDateString(),
            'next_service' => now()->addDays(60)->toDateString(),
            'notes' => 'Primary adsorption bed A.',
        ]);

        Equipment::updateOrCreate([
            'code' => 'PSA-B',
        ], [
            'name' => 'PSA Tower B',
            'status' => 'MAINTENANCE',
            'last_service' => now()->subDays(45)->toDateString(),
            'next_service' => now()->addDays(15)->toDateString(),
            'notes' => 'Secondary adsorption bed B.',
        ]);

        Alarm::updateOrCreate([
            'type' => 'Pressure',
            'severity' => 'CRITICAL',
            'message' => 'System pressure dropped below safe operating threshold.',
        ], [
            'resolved' => false,
            'resolved_at' => null,
        ]);

        Alarm::updateOrCreate([
            'type' => 'Purity',
            'severity' => 'WARNING',
            'message' => 'Oxygen purity is slightly below target.',
        ], [
            'resolved' => false,
            'resolved_at' => null,
        ]);

        Alarm::updateOrCreate([
            'type' => 'Temperature',
            'severity' => 'INFO',
            'message' => 'Temperature stabilized within normal range.',
        ], [
            'resolved' => true,
            'resolved_at' => now(),
        ]);



        $this->call(SprintOneSeeder::class);
        $this->call(SimulatorSeeder::class);
    }
}
