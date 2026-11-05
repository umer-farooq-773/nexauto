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
            if (!Schema::hasColumn('vehicles', 'gps_unit_id')) {
                $table->unsignedBigInteger('gps_unit_id')->nullable()->after('truck_no');
            }
            if (!Schema::hasColumn('vehicles', 'gps_imei')) {
                $table->string('gps_imei', 50)->nullable()->after('gps_unit_id');
            }
            if (!Schema::hasColumn('vehicles', 'gps_provider')) {
                $table->string('gps_provider', 30)->default('wialon')->after('gps_imei');
            }
            if (!Schema::hasColumn('vehicles', 'gps_status')) {
                $table->boolean('gps_status')->default(true)->after('gps_provider');
            }
            if (!Schema::hasColumn('vehicles', 'last_sync_at')) {
                $table->timestamp('last_sync_at')->nullable()->after('gps_status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $cols = [];
            foreach (['gps_unit_id', 'gps_imei', 'gps_provider', 'gps_status', 'last_sync_at'] as $col) {
                if (Schema::hasColumn('vehicles', $col)) {
                    $cols[] = $col;
                }
            }
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
