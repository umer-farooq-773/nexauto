<?php

namespace App\View\Components\Auth;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class AddRouteTripForm extends Component
{
    public $route;
    public $result;
    public $isEdit;

    public function __construct($routeAction = null, $data = null, $isEdit = false)
    {
        $this->route  = $routeAction;
        $this->result = $data;
        $this->isEdit = $isEdit;
    }

    public function render(): View|Closure|string
    {
        return view('components.auth.add-route-trip-form');
    }
}
