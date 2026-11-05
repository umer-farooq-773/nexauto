<?php

namespace App\View\Components\Auth;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class VehicleMakeModelList extends Component
{
    public $list;
    public $existingId;

    /**
     * Create a new component instance.
     */
    public function __construct($existingId = null)
    {
        $this->list = app()->make('App\Interfaces\VehicleMakeModelInterface')->all();
        $this->existingId = $existingId;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.auth.vehicle-make-model-list');
    }
}
