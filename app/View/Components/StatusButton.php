<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class StatusButton extends Component
{
    public string $status;
    public int $count;
    public string $label;
    public string $icon;


    public function __construct(
        string $status = 'success',
        int $count = 0,
        string $label = 'Status',
        string $icon = 'fa-solid fa-circle-info'
    ) {
        $this->status = $status;
        $this->count = $count;
        $this->label = $label;
        $this->icon = $icon;
    }


    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.status-button');
    }
}
