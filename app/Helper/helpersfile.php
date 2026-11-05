<?php

use App\Helper\Helpers;
use Carbon\Carbon;

if (!function_exists('fileUrl')) {
    function fileUrl($path = 'users/avatar.png')
    {
        $helper = new Helpers();
        return $helper->fileUrl($path);
    }
}

if (!function_exists('dateFormat')) {
    function dateFormat($date, $format = 'd M Y')
    {
        if (!$date) {
            return null;
        }

        return Carbon::parse($date)->format($format);
    }
}