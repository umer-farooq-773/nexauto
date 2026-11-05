<?php

namespace App\View\Components\Auth;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class AddRouteTripModal extends Component
{
    public function render(): View|Closure|string
    {
        return view('components.auth.add-route-trip-modal');
    }
}
