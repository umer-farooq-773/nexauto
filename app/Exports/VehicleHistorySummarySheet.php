<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VehicleHistorySummarySheet implements FromArray, WithTitle, WithStyles, Export
{
    use Exportable;
    public function __construct(
        protected array $trackData
    ) {}

    public function array(): array
    {
        $vehicle = $this->trackData['vehicle'] ?? [];
        $period  = $this->trackData['period'] ?? [];
        $summary = $this->trackData['summary'] ?? [];

        return [
            ['VEHICLE GPS HISTORY & TRIP ANALYTICS REPORT', ''],
            ['Generated At', date('d M Y, H:i:s')],
            ['', ''],
            ['VEHICLE INFORMATION', ''],
            ['Fleet Number', $vehicle['fleet_no'] ?? '—'],
            ['Truck / Plate No', $vehicle['truck_no'] ?? '—'],
            ['Driver', $vehicle['driver_name'] ?? '—'],
            ['Make & Model', $vehicle['make_model'] ?? '—'],
            ['Vehicle Type', $vehicle['type'] ?? '—'],
            ['GPS Unit ID', (string) ($this->trackData['unit_id'] ?? '—')],
            ['GPS IMEI', $vehicle['imei'] ?? '—'],
            ['', ''],
            ['REPORT PERIOD', ''],
            ['Start Time', $period['from_formatted'] ?? '—'],
            ['End Time', $period['to_formatted'] ?? '—'],
            ['Total Duration', $period['duration_formatted'] ?? '—'],
            ['', ''],
            ['MOVEMENT & TELEMETRY SUMMARY', ''],
            ['Total Distance / Mileage', number_format($summary['total_mileage_km'] ?? 0, 2) . ' km'],
            ['Total Duration', $summary['total_duration_fmt'] ?? '0h 0m'],
            ['Moving Time', $summary['moving_duration_fmt'] ?? '0h 0m'],
            ['Stop / Parking Time', $summary['stop_duration_fmt'] ?? '0h 0m'],
            ['Maximum Speed', ($summary['max_speed'] ?? 0) . ' km/h'],
            ['Average Speed', ($summary['avg_speed'] ?? 0) . ' km/h'],
            ['Total Trips Count', $summary['trips_count'] ?? 0],
            ['Total Stops Count', $summary['stops_count'] ?? 0],
            ['Total GPS Data Points', $summary['points_count'] ?? 0],
        ];
    }

    public function title(): string
    {
        return 'Overview & Summary';
    }

    public function styles(Worksheet $sheet): ?array
    {
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(45);

        return [
            1 => [
                'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '1E3A8A']],
            ],
            4 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '2563EB']],
            ],
            13 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '2563EB']],
            ],
            18 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '2563EB']],
            ],
        ];
    }
}
