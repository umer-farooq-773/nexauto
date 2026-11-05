<?php

namespace App\View\Components\Auth;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class QuickLinkCard extends Component
{
    public $icon;
    public $title;
    public $description;
    public $route;
    public $iconbg;
    public $icontext;

    /**
     * Create a new component instance.
     */
    public function __construct($icon = "fa-bus-alt", $title, $description, $route = null, $iconbg, $icontext)
    {
        $this->icon = $icon;
        $this->title = $title;
        $this->description = $description;
        $this->iconbg = $iconbg;
        $this->icontext = $icontext;

        if ($route == null) {
            $this->route = "#";
        } else {
            $this->route = route($route);
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.auth.quick-link-card');
    }
}
