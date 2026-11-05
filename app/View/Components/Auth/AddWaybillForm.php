<?php

namespace App\View\Components\Auth;

use App\Models\Status;
use App\Models\Waybill;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class AddWaybillForm extends Component
{
    public $route;
    public $result;
    public $isEdit;
    public $vehicles;
    public $drivers;
    public $routeTrips;
    public $statuses;
    public $nextWaybillNumber;

    public function __construct(
        $routeAction = null,
        $result = null,
        $isEdit = false,
        $vehicles = [],
        $drivers = [],
        $routeTrips = []
    ) {
        $this->route      = $routeAction;
        $this->result     = $result;
        $this->isEdit     = $isEdit;
        $this->vehicles   = $vehicles;
        $this->drivers    = $drivers;
        $this->routeTrips = $routeTrips;
        $this->statuses   = Status::orderByRaw("
            CASE value
                WHEN 0 THEN 0
                WHEN 1 THEN 1
                WHEN 2 THEN 2
                WHEN 5 THEN 3
                WHEN 3 THEN 4
                WHEN 4 THEN 5
                ELSE 99
            END
        ")->get();

        $last = Waybill::orderByRaw('CAST(waybill_number AS UNSIGNED) DESC')->value('waybill_number');
        $this->nextWaybillNumber = $isEdit
            ? $result?->waybill_number
            : str_pad(((int) $last) + 1, 4, '0', STR_PAD_LEFT);
    }

    public function render(): View|Closure|string
    {
        return view('components.auth.add-waybill-form');
    }
}
