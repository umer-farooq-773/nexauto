<?php

namespace App\View\Components\Auth;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class VehicleList extends Component
{
    public $list;
    public $existingId;

    public function __construct($existingId = null)
    {
        $this->existingId = $existingId;
        $this->list = app()->make('App\Interfaces\VehicleInterface')->all(['vehicle_make_model']);

        $this->list = collect($this->list)->map(function ($item) {
            return (object)[
                'id' => $item->id,
                'name' => $item->fleet_no . ' (' . $item->truck_no . ') ' . ($item->vehicle_make_model->name ?? '')
            ];
        });
    }

    public function render(): View|Closure|string
    {
        return view('components.auth.vehicle-list');
    }
}
