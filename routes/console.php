<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// The server scheduler must run for this task to execute automatically.
Schedule::command('wialon:sync-vehicles --no-interaction')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->onOneServer();
