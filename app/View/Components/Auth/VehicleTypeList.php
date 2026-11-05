<?php

namespace App\View\Components\Auth;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class VehicleTypeList extends Component
{
    public $list;
    public $existingId;

    /**
     * Create a new component instance.
     */
    public function __construct($existingId = null)
    {
        $listItems = [];

        try {
            $wialonService = app()->make(\App\Services\WialonService::class);
            if (method_exists($wialonService, 'getWialonVehicleTypes')) {
                $wialonTypes = $wialonService->getWialonVehicleTypes();
                foreach ($wialonTypes as $type) {
                    $listItems[] = (object) [
                        'id' => $type['id'],
                        'name' => $type['name']
                    ];
                }
            }
        } catch (\Throwable $e) {
            // Fallback if WialonService unavailable
        }

        try {
            $dbTypes = app()->make('App\Interfaces\VehicleTypeInterface')->all();
            foreach ($dbTypes as $dbType) {
                $exists = false;
                foreach ($listItems as $li) {
                    if (strtolower($li->name) === strtolower($dbType->name) || $li->id === $dbType->id) {
                        $exists = true;
                        break;
                    }
                }
                if (!$exists) {
                    $listItems[] = (object) [
                        'id' => $dbType->id,
                        'name' => $dbType->name
                    ];
                }
            }
        } catch (\Throwable $e) {
            // Ignore DB fetch errors
        }

        $this->list = $listItems;
        $this->existingId = $existingId;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.auth.vehicle-type-list');
    }
}
