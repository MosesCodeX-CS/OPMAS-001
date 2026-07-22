<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            if (!Schema::hasColumn('equipment', 'site_id')) {
                $table->foreignId('site_id')->nullable()->after('id')->constrained()->nullOnDelete();
            }
            if (!Schema::hasColumn('equipment', 'driver_id')) {
                $table->foreignId('driver_id')->nullable()->after('site_id')->constrained()->nullOnDelete();
            }
            if (!Schema::hasColumn('equipment', 'manufacturer')) {
                $table->string('manufacturer')->nullable();
            }
            if (!Schema::hasColumn('equipment', 'model')) {
                $table->string('model')->nullable();
            }
            if (!Schema::hasColumn('equipment', 'device_type')) {
                $table->string('device_type')->nullable();
            }
            if (!Schema::hasColumn('equipment', 'location')) {
                $table->string('location')->nullable();
            }
            if (!Schema::hasColumn('equipment', 'ip_address')) {
                $table->string('ip_address')->nullable();
            }
            if (!Schema::hasColumn('equipment', 'port')) {
                $table->integer('port')->nullable();
            }
            if (!Schema::hasColumn('equipment', 'unit_id')) {
                $table->unsignedInteger('unit_id')->nullable();
            }
            if (!Schema::hasColumn('equipment', 'poll_interval')) {
                $table->unsignedInteger('poll_interval')->nullable();
            }
            if (!Schema::hasColumn('equipment', 'enabled')) {
                $table->boolean('enabled')->default(true);
            }
            if (!Schema::hasColumn('equipment', 'last_seen')) {
                $table->timestamp('last_seen')->nullable();
            }
        });

        if (!Schema::hasColumn('equipment', 'status')) {
            Schema::table('equipment', function (Blueprint $table) {
                $table->string('status')->default('UNKNOWN');
            });
        }

        Schema::table('alarms', function (Blueprint $table) {
            if (!Schema::hasColumn('alarms', 'alarm_rule_id')) {
                $table->foreignId('alarm_rule_id')->nullable()->after('id')->constrained()->nullOnDelete();
            }
        });

        Schema::table('equipment', function (Blueprint $table) {
            $table->index('ip_address');
        });
    }

    public function down(): void
    {
        Schema::table('alarms', function (Blueprint $table) {
            $table->dropConstrainedForeignId('alarm_rule_id');
        });

        Schema::table('equipment', function (Blueprint $table) {
            $table->dropConstrainedForeignId('site_id');
            $table->dropConstrainedForeignId('driver_id');
            $table->dropColumn(['manufacturer', 'model', 'device_type', 'location', 'ip_address', 'port', 'unit_id', 'poll_interval', 'enabled', 'last_seen', 'status']);
        });
    }
};
