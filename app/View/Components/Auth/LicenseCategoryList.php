<?php

namespace App\View\Components\Auth;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class LicenseCategoryList extends Component
{
    public $list;
    public $existingVal;

    public function __construct($existingVal = null)
    {
        $this->existingVal = $existingVal;
        $this->list = app()->make('App\Interfaces\LicenseCategoryInterface')->all();

        $this->list = collect($this->list)->map(function ($item) {
            return (object)[
                'id'   => $item->id,
                'name' => $item->name,
            ];
        });
    }

    public function render(): View|Closure|string
    {
        return view('components.auth.license-category-list');
    }
}
