<?php

namespace App\View\Components\Auth;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class InputField extends Component
{
    public $type;
    public $name;
    public $id;
    public $place;
    public $val;
    public $required;
    public $extraclasses;
    public $label;
    public $readonly;
    public $disabled;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($type = 'text', $name = '', $id = '', $place = null, $val = null, $required = null, $extraclasses = null, $label = null, $readonly = false, $disabled = false)
    {
        $this->type = $type;
        $this->name = $name;
        $this->id = $id;
        $this->place = $place;
        $this->val = $val;
        $this->required = $required;
        $this->extraclasses = $extraclasses;
        $this->label = $label;
        $this->readonly = $readonly;
        $this->disabled = $disabled;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.auth.input-field');
    }
}
