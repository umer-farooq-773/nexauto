<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'wialon' => [
        'token' => env('WIALON_TOKEN'),
        'host' => env('WIALON_HOST', 'https://hst-api.wialon.eu'),
        'creator_id' => env('WIALON_CREATOR_ID'),
        'test_mode' => env('WIALON_TEST_MODE', false), // Enable to bypass API calls for testing
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],

    'smtp' => [
        'mailer' => env('MAIL_MAILER'),
        'host' => env('MAIL_HOST'),
        'port' => env('MAIL_PORT'),
        'username' => env('MAIL_USERNAME'),
        'password' => env('MAIL_PASSWORD'),
    ],

    'app' => [
        'name' => env('APP_NAME'),
        'url' => env('APP_URL'),
        'email' => env('ADMIN_MAIL')
    ],

    'geoapify' => [
        'api_key' => env('GEOAPIFY_API_KEY') ?: env('GEO_APIFY'),
    ],

    'openweather' => [
        'base_url' => env('OPENWEATHER_BASE_URL', 'https://api.openweathermap.org'),
        'tile_base_url' => env('OPENWEATHER_TILE_BASE_URL', 'https://tile.openweathermap.org'),
        'key' => env('OPENWEATHER_API_KEY') ?: env('OPEN_WEATHER_API_KEY'),
        'ttl' => env('OPENWEATHER_TTL', 600),
    ],

    'tomtom' => [
        'base_url' => env('TOMTOM_BASE_URL', 'https://api.tomtom.com'),
        'key' => env('TOMTOM_API_KEY') ?: (env('TOM_TOM_ROAD_API_KEY') ?: env('TOM_TOM_API_KEY')),
        'ttl' => env('TOMTOM_TTL', 300),
    ],
];
