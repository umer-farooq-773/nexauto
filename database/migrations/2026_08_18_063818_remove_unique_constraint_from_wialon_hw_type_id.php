<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Remove the UNIQUE constraint from wialon_hw_type_id because:
     * - Multiple GPS devices can share the same Wialon hardware type ID
     * - Example: All Teltonika FM-series devices use type 12
     */
    public function up(): void
    {
        Schema::table('gps_device_types', function (Blueprint $table) {
            // Drop the unique constraint
            $table->dropUnique('gps_device_types_wialon_hw_type_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gps_device_types', function (Blueprint $table) {
            // Re-add unique constraint if rolling back
            $table->unique('wialon_hw_type_id', 'gps_device_types_wialon_hw_type_id_unique');
        });
    }
};
