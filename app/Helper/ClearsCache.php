<?php

namespace App\Helper;

use Illuminate\Support\Facades\Cache;

trait ClearsCache
{
    public static function bootClearsCache()
    {
        static::saved(function ($model) {
            static::flushCache($model);
        });

        static::deleted(function ($model) {
            static::flushCache($model);
        });
    }

    protected static function flushCache($model)
    {
        Cache::flush();
    }
}
