<?php

namespace App\Providers;

use App\Events\IncidentStatusChanged;
use App\Listeners\SendIncidentStatusChangedNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     */
    protected $listen = [
        IncidentStatusChanged::class => [
            SendIncidentStatusChangedNotification::class,
        ],
    ];

    public function boot(): void
    {
        parent::boot();
    }
}
