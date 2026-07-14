<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('alarms', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50);
            $table->enum('severity', ['CRITICAL', 'WARNING', 'INFO'])->default('INFO');
            $table->text('message');
            $table->boolean('resolved')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('alarms');
    }
};