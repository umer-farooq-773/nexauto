<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MapController extends Controller
{
    public function index()
    {
        return view('map.map-tracking', [
            'wialonToken' => config('services.wialon.token'),
            'wialonHost'  => config('services.wialon.host'),
            'embedded'   => request()->boolean('embedded'),
        ]);
    }
}
