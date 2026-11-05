<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class VehicleDetail extends Component
{
    /**
     * Create a new component instance.
     */
    public $vehicle;
    public $triggerId;
    public $triggerText;
    public $modalId;


    public function __construct($vehicle = null, $triggerId = null, $triggerText = null, $modalId = null)
    {
        $this->vehicle = $vehicle;
        $this->triggerId = $triggerId ?? 'vehicle_detail_' . ($vehicle?->id ?? 'null');
        $this->triggerText = $triggerText ?? ($vehicle?->fleet_no ?? '-');
        $this->modalId = $modalId ?? 'vehicleModal_' . ($vehicle?->id ?? 'null');
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.vehicle-detail');
    }
}
