<?php

namespace Database\Seeders;

use App\Models\Alarm;
use App\Models\Equipment;
use App\Models\SensorReading;
use App\Models\User;
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
            'email' => 'admin@example.com',
        ], [
            'name' => 'Admin User',
            'password' => 'password',
            'role' => 'admin',
        ]);

        User::updateOrCreate([
            'email' => 'operator@example.com',
        ], [
            'name' => 'Operator User',
            'password' => 'password',
            'role' => 'operator',
        ]);

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

        Alarm::create([
            'type' => 'Purity',
            'severity' => 'WARNING',
            'message' => 'Oxygen purity is slightly below target.',
            'resolved' => false,
            'resolved_at' => null,
        ]);

        Alarm::create([
            'type' => 'Temperature',
            'severity' => 'INFO',
            'message' => 'Temperature stabilized within normal range.',
            'resolved' => true,
            'resolved_at' => now(),
        ]);

        foreach (range(1, 10) as $index) {
            SensorReading::create([
                'pressure' => 6.5 + $index * 0.1,
                'purity' => 92.0 + $index * 0.2,
                'flow_rate' => 110 + $index * 5,
                'temperature' => 42 + $index * 0.5,
                'tank_level' => 65 + $index * 2,
                'compressor_status' => $index % 3 === 0 ? 2 : 1,
                'bed_a_status' => $index % 2 === 0 ? 1 : 0,
                'bed_b_status' => $index % 2 === 1 ? 1 : 0,
                'created_at' => now()->subHours(10 - $index),
                'updated_at' => now()->subHours(10 - $index),
            ]);
        }
    }
}
