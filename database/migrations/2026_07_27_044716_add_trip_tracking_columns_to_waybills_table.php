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
        Schema::table('waybills', function (Blueprint $table) {
            $table->timestamp('trip_started_at')->nullable();
            $table->timestamp('trip_completed_at')->nullable()->after('trip_started_at');

            $table->decimal('start_odometer', 10, 2)->nullable()->after('trip_completed_at');
            $table->decimal('end_odometer', 10, 2)->nullable()->after('start_odometer');

            $table->decimal('actual_distance_km', 10, 2)->nullable()->after('end_odometer');

            $table->boolean('gps_enabled')->default(true)->after('actual_distance_km');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('waybills', function (Blueprint $table) {

            $table->dropColumn([
                'trip_started_at',
                'trip_completed_at',
                'start_odometer',
                'end_odometer',
                'actual_distance_km',
                'gps_enabled'
            ]);
        });
    }
};
