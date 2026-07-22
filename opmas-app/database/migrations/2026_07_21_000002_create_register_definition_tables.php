<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('register_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->integer('display_order')->default(0);
            $table->timestamps();
        });

        Schema::create('poll_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->unsignedInteger('interval_seconds')->default(60);
            $table->string('priority')->default('NORMAL');
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });

        Schema::create('register_definitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('register_group_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('poll_profile_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('address');
            $table->string('register_type');
            $table->string('data_type')->nullable();
            $table->integer('display_order')->default(0);
            $table->boolean('enabled')->default(true);
            $table->boolean('graph_enabled')->default(false);
            $table->timestamps();
            $table->unique(['equipment_id', 'address', 'register_type']);
        });

        Schema::create('register_definition_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('register_definition_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('scale', 12, 6)->default(1);
            $table->decimal('offset', 12, 6)->default(0);
            $table->string('unit')->nullable();
            $table->unsignedInteger('decimals')->default(2);
            $table->timestamp('effective_from')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('register_definition_versions');
        Schema::dropIfExists('register_definitions');
        Schema::dropIfExists('poll_profiles');
        Schema::dropIfExists('register_groups');
    }
};
