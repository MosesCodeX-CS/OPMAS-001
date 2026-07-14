<?php

namespace Database\Seeders;

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
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        
        \App\Models\Equipment::create([
            'code' => 'COMP-01',
            'name' => 'Oxygen Compressor',
            'status' => 'ONLINE',
            'last_service' => now()->subDays(14)->toDateString(),
            'next_service' => now()->addDays(16)->toDateString(),
            'notes' => 'Primary compressor for oxygen generation.',
        ]);

        \App\Models\Equipment::create([
            'code' => 'PSA-A',
            'name' => 'PSA Tower A',
            'status' => 'ONLINE',
            'last_service' => now()->subDays(30)->toDateString(),
            'next_service' => now()->addDays(60)->toDateString(),
            'notes' => 'Primary adsorption bed A.',
        ]);

        \App\Models\Equipment::create([
            'code' => 'PSA-B',
            'name' => 'PSA Tower B',
            'status' => 'MAINTENANCE',
            'last_service' => now()->subDays(45)->toDateString(),
            'next_service' => now()->addDays(15)->toDateString(),
            'notes' => 'Secondary adsorption bed B.',
        ]);
    }
}
