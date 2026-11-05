<?php

namespace App\View\Components\Auth;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class AddMaintenanceModal extends Component
{
    public $vehicles;
    public $maintenanceTypes;

    public function __construct($vehicles = null, $maintenanceTypes = null)
    {
        $this->vehicles = $vehicles ?? collect();
        $this->maintenanceTypes = $maintenanceTypes ?? collect();
    }

    public function render(): View|Closure|string
    {
        return view('components.auth.add-maintenance-modal');
    }
}
