<?php

namespace Tests\Feature;

use Tests\TestCase;

class SprintOneDatabaseTest extends TestCase
{
    public function test_core_sprint_one_schema_and_seed_data_exist(): void
    {
        $this->artisan('migrate:fresh', [
            '--seed' => true,
        ])->assertSuccessful();

        $this->assertDatabaseHas('hospitals', ['code' => 'KIJABE']);
        $this->assertDatabaseHas('sites', ['code' => 'KIJABE-ICU']);
        $this->assertDatabaseHas('drivers', ['name' => 'Schneider M221 Driver']);
        $this->assertDatabaseHas('register_definitions', ['address' => 40001, 'register_type' => 'HOLDING']);
        $this->assertDatabaseHas('telemetry', ['quality' => 'GOOD']);
    }
}
