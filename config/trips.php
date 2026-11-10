<?php

return [
    // Driver trip monitoring runtime. These settings define alert/timer behavior;
    // vehicle-specific speed and parking rules remain on each Vehicle record.
    'auto_start_timer' => (bool) env('TRIP_AUTO_START_TIMER', false),
    'monitor_tick_seconds' => max(1, (int) env('TRIP_MONITOR_TICK_SECONDS', 10)),
    'stale_message_seconds' => max(1, (int) env('TRIP_STALE_MESSAGE_SECONDS', 120)),
    'default_speeding_tolerance_kmh' => max(0, (int) env('TRIP_DEFAULT_SPEEDING_TOLERANCE_KMH', 0)),
    'default_speeding_min_duration_s' => max(1, (int) (env('TRIP_DEFAULT_SPEEDING_MIN_DURATION_S') ?: 30)),
    'speeding_clear_seconds' => max(1, (int) env('TRIP_SPEEDING_CLEAR_SECONDS', 10)),
    'default_pause_limit_seconds' => max(0, (int) env('TRIP_DEFAULT_PAUSE_LIMIT_SECONDS', 300)),
    'pause_repeat_minutes' => max(1, (int) env('TRIP_PAUSE_REPEAT_MINUTES', 10)),
    'time_warning_percent' => min(100, max(1, (int) env('TRIP_TIME_WARNING_PERCENT', 80))),
    'time_alert_repeat_minutes' => max(1, (int) env('TRIP_TIME_ALERT_REPEAT_MINUTES', 15)),
    'heartbeat_interval_seconds' => max(5, (int) env('TRIP_HEARTBEAT_INTERVAL_SECONDS', 60)),
    'heartbeat_stale_minutes' => max(1, (int) env('TRIP_HEARTBEAT_STALE_MINUTES', 5)),
    'no_driver_alert_repeat_minutes' => max(1, (int) env('TRIP_NO_DRIVER_ALERT_REPEAT_MINUTES', 15)),
    'qr_token_length' => min(64, max(32, (int) env('TRIP_QR_TOKEN_LENGTH', 40))),

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

    // Backward-compatible settings retained for existing trip UI consumers.
    'warning_percent' => (int) env('TRIP_WARNING_PERCENT', 80),
    'switch_mode' => env('TRIP_SWITCH_MODE', 'next_stop'),
    'offline_minutes' => (int) env('TRIP_OFFLINE_MINUTES', 5),
    'tick_seconds' => (int) env('TRIP_TICK_SECONDS', 10),
];
