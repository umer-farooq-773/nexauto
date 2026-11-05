<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;

class ToolController extends Controller
{
    /**
     * Clear all application caches.
     */
    public function clearCache()
    {
        Artisan::call('config:cache');
        Artisan::call('cache:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');

        return 'Cache cleared successfully';
    }

    /**
     * Run migrate:fresh with seed.
     */
    public function migrateFresh()
    {
        Artisan::call('migrate:fresh --seed');

        return 'Database migrated and seeded successfully';
    }
}
