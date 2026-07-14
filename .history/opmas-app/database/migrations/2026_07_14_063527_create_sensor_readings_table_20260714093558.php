<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('sensor_readings', function (Blueprint $table) {
            $table->id();
            $table->decimal('pressure', 8, 2)->nullable()->comment('bar');
            $table->decimal('purity', 5, 2)->nullable()->comment('percentage 0-100');
            $table->decimal('flow_rate', 8, 2)->nullable()->comment('L/min');
            $table->decimal('temperature', 6, 2)->nullable()->comment('Celsius');
            $table->decimal('tank_level', 5, 2)->nullable()->comment('percentage 0-100');
            $table->tinyInteger('compressor_status')->default(0)->comment('0=OFF 1=RUNNING 2=FAULT');
            $table->tinyInteger('bed_a_status')->default(0)->comment('0=Idle 1=Active');
            $table->tinyInteger('bed_b_status')->default(0)->comment('0=Idle 1=Active');
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('sensor_readings');
    }
};