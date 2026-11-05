<?php

namespace App\View\Components\Auth;

use App\Models\DriverDetail;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class AddDriverMateForm extends Component
{
    public $route;
    public $result;
    public $isEdit;
    public $drivers;

    public function __construct($routeAction = "route('driver_mate.store')", $data = null, $isEdit = false)
    {
        $this->route    = $routeAction;
        $this->result   = $data;
        $this->isEdit   = $isEdit;
        $this->drivers  = DriverDetail::query()
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'phone_number']);
    }

    public function render(): View|Closure|string
    {
        return view('components.auth.add-driver-mate-form');
    }
}
