<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            // Add GPS-related fields if they don't exist
            if (!Schema::hasColumn('vehicles', 'gps_provider')) {
                $table->string('gps_provider')->nullable()->after('status');
            }
            if (!Schema::hasColumn('vehicles', 'gps_imei')) {
                $table->string('gps_imei', 15)->nullable()->unique()->after('gps_provider');
            }
            if (!Schema::hasColumn('vehicles', 'gps_unit_id')) {
                $table->string('gps_unit_id')->nullable()->after('gps_imei');
            }
            if (!Schema::hasColumn('vehicles', 'gps_hw_type_id')) {
                $table->integer('gps_hw_type_id')->nullable()->after('gps_unit_id');
            }
            if (!Schema::hasColumn('vehicles', 'gps_sync_status')) {
                $table->enum('gps_sync_status', ['pending', 'synced', 'failed'])->default('pending')->after('gps_hw_type_id');
            }
            if (!Schema::hasColumn('vehicles', 'gps_sync_error')) {
                $table->text('gps_sync_error')->nullable()->after('gps_sync_status');
            }
            if (!Schema::hasColumn('vehicles', 'last_sync_at')) {
                $table->timestamp('last_sync_at')->nullable()->after('gps_sync_error');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn([
                'gps_provider',
                'gps_imei',
                'gps_unit_id',
                'gps_hw_type_id',
                'gps_sync_status',
                'gps_sync_error',
                'last_sync_at',
            ]);
        });
    }
};
