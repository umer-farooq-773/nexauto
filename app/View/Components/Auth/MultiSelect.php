<?php

namespace App\View\Components\Auth;

use Illuminate\View\Component;

class MultiSelect extends Component
{
    public $name;
    public $id;
    public $label;
    public $placeholder;
    public $loopData;
    public $existingId;
    public $selectclass;

    public function __construct($name, $id, $label = null, $placeholder = null, $loopData = null, $existingId = null, $selectclass = '')
    {
        $this->name = $name;
        $this->id = $id;
        $this->label = $label;
        $this->placeholder = $placeholder;
        $this->loopData = $loopData;
        $this->existingId = $existingId;
        $this->selectclass = $selectclass;
    }

    public function render()
    {
        return view('components.auth.multi-select');
    }
}
