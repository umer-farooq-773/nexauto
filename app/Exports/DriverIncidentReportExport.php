<?php

namespace App\Exports;

use App\Services\DriverReportExcelService;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\Export;

class DriverIncidentReportExport implements WithMultipleSheets, Export
{
    use Exportable;
    public function __construct(
        protected DriverReportExcelService $service,
        protected array $filters
    ) {
    }

    public function sheets(): array
    {
        return [
            new IncidentSummarySheet($this->service, $this->filters),
            new IncidentRecordsSheet($this->service, $this->filters),
        ];
    }
}
