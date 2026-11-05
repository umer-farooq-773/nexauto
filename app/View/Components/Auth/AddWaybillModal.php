<?php

namespace App\View\Components\Auth;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class AddWaybillModal extends Component
{
    public $vehicles;
    public $drivers;
    public $routeTrips;

    public function __construct($vehicles = [], $drivers = [], $routeTrips = [])
    {
        $this->vehicles   = $vehicles;
        $this->drivers    = $drivers;
        $this->routeTrips = $routeTrips;
    }

    public function render(): View|Closure|string
    {
        return view('components.auth.add-waybill-modal');
    }
}
