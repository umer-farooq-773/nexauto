<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class VehicleHistoryExport implements WithMultipleSheets, Export
{
    use Exportable;

    public function __construct(
        protected array $trackData
    ) {}

    public function sheets(): array
    {
        return [
            new VehicleHistorySummarySheet($this->trackData),
            new VehicleHistoryTripsSheet($this->trackData),
            new VehicleHistoryStopsSheet($this->trackData),
            new VehicleHistoryPointsSheet($this->trackData),
        ];
    }
}
