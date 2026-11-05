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
            $table->unsignedInteger('gps_hw_type_id')->nullable()->after('gps_imei');

            $table->enum('gps_sync_status', ['pending', 'creating', 'synced', 'failed'])
                ->default('pending')
                ->after('last_sync_at');

            $table->text('gps_sync_error')->nullable()->after('gps_sync_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn(['gps_hw_type_id', 'gps_sync_status', 'gps_sync_error']);
        });
    }
};
