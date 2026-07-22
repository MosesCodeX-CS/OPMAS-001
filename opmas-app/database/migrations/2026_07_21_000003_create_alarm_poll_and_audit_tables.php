<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alarm_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('register_definition_id')->constrained()->cascadeOnDelete();
            $table->string('condition');
            $table->string('threshold');
            $table->string('severity');
            $table->boolean('enabled')->default(true);
            $table->timestamps();
            $table->index('register_definition_id');
        });

        Schema::create('poll_cycles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained()->cascadeOnDelete();
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('finished_at')->nullable();
            $table->string('status')->default('RUNNING');
            $table->unsignedInteger('duration')->nullable();
            $table->timestamps();
        });

        Schema::create('telemetry', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poll_cycle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('register_definition_id')->constrained()->cascadeOnDelete();
            $table->decimal('raw_value', 16, 6)->nullable();
            $table->timestamp('device_timestamp')->nullable();
            $table->timestamp('collector_timestamp')->useCurrent();
            $table->string('quality')->default('GOOD');
            $table->unsignedInteger('poll_duration_ms')->nullable();
            $table->timestamps();
            $table->index(['register_definition_id', 'collector_timestamp']);
            $table->index('poll_cycle_id');
        });

        Schema::create('config_change_history', function (Blueprint $table) {
            $table->id();
            $table->string('table_name');
            $table->unsignedBigInteger('record_id');
            $table->string('field');
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->timestamp('changed_at')->useCurrent();
            $table->text('reason')->nullable();
            $table->timestamps();
        });

        Schema::create('event_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event_type');
            $table->string('category');
            $table->text('message');
            $table->unsignedBigInteger('related_equipment_id')->nullable();
            $table->unsignedBigInteger('related_user_id')->nullable();
            $table->timestamp('occurred_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_logs');
        Schema::dropIfExists('config_change_history');
        Schema::dropIfExists('telemetry');
        Schema::dropIfExists('poll_cycles');
        Schema::dropIfExists('alarm_rules');
    }
};
