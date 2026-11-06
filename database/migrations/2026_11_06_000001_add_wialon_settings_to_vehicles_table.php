<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->unsignedTinyInteger('wialon_movement_type')->nullable()->default(1);
            $table->boolean('wialon_gps_correction')->nullable()->default(true);
            $table->unsignedTinyInteger('wialon_min_satellites')->nullable()->default(2);
            $table->unsignedSmallInteger('min_moving_speed_kmh')->nullable()->default(1);
            $table->unsignedInteger('min_parking_seconds')->nullable()->default(300);
            $table->unsignedInteger('max_messages_distance_m')->nullable()->default(10000);
            $table->unsignedInteger('min_trip_seconds')->nullable()->default(60);
            $table->unsignedInteger('min_trip_distance_m')->nullable()->default(100);
            $table->unsignedSmallInteger('speed_limit_kmh')->nullable();
            $table->unsignedSmallInteger('speeding_tolerance_kmh')->nullable();
            $table->unsignedInteger('speeding_min_duration_s')->nullable();
            $table->unsignedTinyInteger('speeding_mode')->nullable()->default(0);
            $table->timestamp('wialon_settings_synced_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn([
                'wialon_movement_type',
                'wialon_gps_correction',
                'wialon_min_satellites',
                'min_moving_speed_kmh',
                'min_parking_seconds',
                'max_messages_distance_m',
                'min_trip_seconds',
                'min_trip_distance_m',
                'speed_limit_kmh',
                'speeding_tolerance_kmh',
                'speeding_min_duration_s',
                'speeding_mode',
                'wialon_settings_synced_at',
            ]);
        });
    }
};
