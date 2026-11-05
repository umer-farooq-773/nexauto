<?php

namespace App\View\Components\Auth;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class AddMaintenanceForm extends Component
{
    public $route;
    public $result;
    public $isEdit;
    public $vehicles;
    public $maintenanceTypes;

    public function __construct($routeAction = null, $data = null, $isEdit = false, $vehicles = null, $maintenanceTypes = null)
    {
        $this->route            = $routeAction;
        $this->result           = $data;
        $this->isEdit           = $isEdit;
        $this->vehicles         = $vehicles ?? collect();
        $this->maintenanceTypes = $maintenanceTypes ?? collect();
    }

    public function render(): View|Closure|string
    {
        return view('components.auth.add-maintenance-form');
    }
}
