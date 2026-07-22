<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hospitals', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('country')->default('Kenya');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('location')->nullable();
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });

        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('protocol');
            $table->boolean('supports_holding_registers')->default(true);
            $table->boolean('supports_input_registers')->default(true);
            $table->boolean('supports_coils')->default(true);
            $table->boolean('supports_discrete_inputs')->default(true);
            $table->boolean('supports_writes')->default(false);
            $table->unsignedInteger('max_registers_per_request')->default(125);
            $table->unsignedInteger('max_concurrent_requests')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drivers');
        Schema::dropIfExists('sites');
        Schema::dropIfExists('hospitals');
    }
};
