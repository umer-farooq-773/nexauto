<?php

namespace App\View\Components\Auth;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class AddDriverForm extends Component
{
    public $route;
    public $result;
    public $isEdit;

    /**
     * Create a new component instance.
     */
    public function __construct($routeAction = "route('driver.store')", $data = null, $isEdit = false)
    {
        $this->route = $routeAction;
        $this->result = $data;
        $this->isEdit = $isEdit;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.auth.add-driver-form');
    }
}
