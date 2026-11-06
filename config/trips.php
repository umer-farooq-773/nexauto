<?php

return [
    // Runtime tracking settings now live on each vehicle. These values are defaults for new vehicles.
    'defaults' => [
        'wialon_movement_type' => (int) env('TRIP_DEFAULT_MOVEMENT_TYPE', 1),
        'wialon_gps_correction' => (bool) env('TRIP_DEFAULT_GPS_CORRECTION', true),
        'wialon_min_satellites' => (int) env('TRIP_DEFAULT_MIN_SATELLITES', 2),
        'min_moving_speed_kmh' => (int) env('TRIP_DEFAULT_MIN_MOVING_SPEED_KMH', 1),
        'min_parking_seconds' => (int) env('TRIP_DEFAULT_MIN_PARKING_SECONDS', 300),
        'max_messages_distance_m' => (int) env('TRIP_DEFAULT_MAX_MESSAGES_DISTANCE_M', 10000),
        'min_trip_seconds' => (int) env('TRIP_DEFAULT_MIN_TRIP_SECONDS', 60),
        'min_trip_distance_m' => (int) env('TRIP_DEFAULT_MIN_TRIP_DISTANCE_M', 100),
        'speed_limit_kmh' => (int) env('TRIP_DEFAULT_SPEED_LIMIT_KMH', 80),
        'speeding_tolerance_kmh' => env('TRIP_DEFAULT_SPEEDING_TOLERANCE_KMH'),
        'speeding_min_duration_s' => env('TRIP_DEFAULT_SPEEDING_MIN_DURATION_S'),
        'speeding_mode' => (int) env('TRIP_DEFAULT_SPEEDING_MODE', 0),
    ],

    // Used by the driver module later.
    'warning_percent' => (int) env('TRIP_WARNING_PERCENT', 80),
    'switch_mode' => env('TRIP_SWITCH_MODE', 'next_stop'),
    'offline_minutes' => (int) env('TRIP_OFFLINE_MINUTES', 5),
    'tick_seconds' => (int) env('TRIP_TICK_SECONDS', 10),
];
