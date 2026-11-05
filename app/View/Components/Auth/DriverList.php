<?php

namespace App\View\Components\Auth;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class DriverList extends Component
{
    public $list;
    public $existingIds;

    public function __construct($existingIds = [])
    {
        $this->existingIds = $existingIds;
        $this->list = app()->make('App\Interfaces\DriverDetailInterface')->all();

        $this->list = collect($this->list)->map(function ($d) {
            return (object)[
                'id' => $d->id,
                'name' => $d->first_name . ' ' . $d->last_name . ' (' . $d->drivers_license_number . ')'
            ];
        });
    }

    public function render(): View|Closure|string
    {
        return view('components.auth.driver-list');
    }
}
