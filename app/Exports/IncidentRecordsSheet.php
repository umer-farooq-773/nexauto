<?php

namespace App\Exports;

use App\Services\DriverReportExcelService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class IncidentRecordsSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    public function __construct(
        protected DriverReportExcelService $service,
        protected array $filters
    ) {
    }

    public function collection(): \Illuminate\Support\Collection
    {
        return $this->service->getRecords($this->filters);
    }

    public function headings(): array
    {
        return [
            'Incident Type',
            'Driver Name',
            'Fleet No',
            'Truck No',
            'Date',
            'Location',
            'Reference / Authority',
            'Cost',
            'Status',
        ];
    }

    public function map($row): array
    {
        return [
            $row->incident_type,
            $row->driver_name ?: 'Unassigned',
            $row->fleet_no,
            $row->truck_no,
            $row->incident_date,
            $row->location,
            $row->reference,
            $row->cost !== null ? (float) $row->cost : null,
            $row->status,
        ];
    }

    public function title(): string
    {
        return 'Incident Records';
    }

    public function styles(Worksheet $sheet): ?array
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1F2937'],
            ]],
        ];
    }
}
