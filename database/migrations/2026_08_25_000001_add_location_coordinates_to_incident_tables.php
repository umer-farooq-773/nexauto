<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds lat/lng columns to all incident tables for Geoapify location tracking
     */
    public function up(): void
    {
        // Impoundments
        Schema::table('impoundments', function (Blueprint $table) {
            $table->decimal('impound_location_lat', 10, 7)->nullable()->after('impound_location');
            $table->decimal('impound_location_lng', 10, 7)->nullable()->after('impound_location_lat');
        });

        // Accidents
        Schema::table('accidents', function (Blueprint $table) {
            $table->decimal('accident_location_lat', 10, 7)->nullable()->after('accident_location');
            $table->decimal('accident_location_lng', 10, 7)->nullable()->after('accident_location_lat');
        });

        // Vehicle Breakdowns
        Schema::table('vehicle_breakdowns', function (Blueprint $table) {
            $table->decimal('breakdown_location_lat', 10, 7)->nullable()->after('breakdown_location');
            $table->decimal('breakdown_location_lng', 10, 7)->nullable()->after('breakdown_location_lat');
        });

        // Illegal Detentions
        Schema::table('illegal_detentions', function (Blueprint $table) {
            $table->decimal('detention_location_lat', 10, 7)->nullable()->after('detention_location');
            $table->decimal('detention_location_lng', 10, 7)->nullable()->after('detention_location_lat');
        });

        // Vehicle Bogged Down
        Schema::table('vehicle_bogged_downs', function (Blueprint $table) {
            $table->decimal('stuck_location_lat', 10, 7)->nullable()->after('stuck_location');
            $table->decimal('stuck_location_lng', 10, 7)->nullable()->after('stuck_location_lat');
        });

        // Route Diversions
        Schema::table('route_diversions', function (Blueprint $table) {
            $table->decimal('diversion_location_lat', 10, 7)->nullable()->after('diversion_location');
            $table->decimal('diversion_location_lng', 10, 7)->nullable()->after('diversion_location_lat');
        });

        // Illegal Haulages
        Schema::table('illegal_haulages', function (Blueprint $table) {
            $table->decimal('haulage_location_lat', 10, 7)->nullable()->after('haulage_location');
            $table->decimal('haulage_location_lng', 10, 7)->nullable()->after('haulage_location_lat');
        });

        // Breathaliser Tests
        Schema::table('breathaliser_tests', function (Blueprint $table) {
            $table->decimal('test_location_lat', 10, 7)->nullable()->after('test_location');
            $table->decimal('test_location_lng', 10, 7)->nullable()->after('test_location_lat');
        });

        // Driver Misconducts
        Schema::table('driver_misconducts', function (Blueprint $table) {
            $table->decimal('misconduct_location_lat', 10, 7)->nullable()->after('misconduct_location');
            $table->decimal('misconduct_location_lng', 10, 7)->nullable()->after('misconduct_location_lat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('impoundments', function (Blueprint $table) {
            $table->dropColumn(['impound_location_lat', 'impound_location_lng']);
        });

        Schema::table('accidents', function (Blueprint $table) {
            $table->dropColumn(['accident_location_lat', 'accident_location_lng']);
        });

        Schema::table('vehicle_breakdowns', function (Blueprint $table) {
            $table->dropColumn(['breakdown_location_lat', 'breakdown_location_lng']);
        });

        Schema::table('illegal_detentions', function (Blueprint $table) {
            $table->dropColumn(['detention_location_lat', 'detention_location_lng']);
        });

        Schema::table('vehicle_bogged_downs', function (Blueprint $table) {
            $table->dropColumn(['stuck_location_lat', 'stuck_location_lng']);
        });

        Schema::table('route_diversions', function (Blueprint $table) {
            $table->dropColumn(['diversion_location_lat', 'diversion_location_lng']);
        });

        Schema::table('illegal_haulages', function (Blueprint $table) {
            $table->dropColumn(['haulage_location_lat', 'haulage_location_lng']);
        });

        Schema::table('breathaliser_tests', function (Blueprint $table) {
            $table->dropColumn(['test_location_lat', 'test_location_lng']);
        });

        Schema::table('driver_misconducts', function (Blueprint $table) {
            $table->dropColumn(['misconduct_location_lat', 'misconduct_location_lng']);
        });
    }
};
