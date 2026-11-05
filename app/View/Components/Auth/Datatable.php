<?php

namespace App\View\Components\Auth;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Datatable extends Component
{
    public $id;
    public $search;
    public $init;

    /**
     * Create a new component instance.
     */
    public function __construct($id = 'myTable', $search = true, $init = true)
    {
        $this->id = $id;
        $this->search = $search;
        $this->init = $init;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.auth.datatable');
    }
}
