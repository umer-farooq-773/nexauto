<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VehicleHistoryPointsSheet implements FromArray, WithHeadings, WithStyles, WithTitle, Export
{
    use Exportable;

    public function __construct(
        protected array $trackData
    ) {}

    public function array(): array
    {
        $points = $this->trackData['points'] ?? [];
        $totalPoints = count($points);
        $rows = [];

        // If dataset is large (> 2500 points), sample evenly to prevent memory exhaustion and Excel lag
        $maxPoints = 2500;
        $step = ($totalPoints > $maxPoints) ? (int) ceil($totalPoints / $maxPoints) : 1;

        for ($i = 0; $i < $totalPoints; $i += $step) {
            $pt = $points[$i];
            $sensors = $pt['sensors'] ?? [];

            $rows[] = [
                $i + 1,
                $pt['time_formatted'] ?? ($pt['time'] ?? (isset($pt['t']) ? date('Y-m-d H:i:s', $pt['t']) : '—')),
                $pt['lat'] ?? 0,
                $pt['lng'] ?? 0,
                $pt['speed'] ?? 0,
                $pt['altitude'] ?? ($pt['alt'] ?? 0),
                $pt['course'] ?? '—',
                $sensors['Ignition'] ?? '—',
                $sensors['Fuel Level'] ?? '—',
                $sensors['Engine Hours'] ?? '—',
                $sensors['Mileage'] ?? '—',
                $sensors['Voltage'] ?? '—',
            ];
        }

        // Ensure the last point is always included if step > 1
        if ($step > 1 && $totalPoints > 0 && ($totalPoints - 1) % $step !== 0) {
            $lastPt = end($points);
            $sensors = $lastPt['sensors'] ?? [];
            $rows[] = [
                $totalPoints,
                $lastPt['time_formatted'] ?? ($lastPt['time'] ?? (isset($lastPt['t']) ? date('Y-m-d H:i:s', $lastPt['t']) : '—')),
                $lastPt['lat'] ?? 0,
                $lastPt['lng'] ?? 0,
                $lastPt['speed'] ?? 0,
                $lastPt['altitude'] ?? ($lastPt['alt'] ?? 0),
                $lastPt['course'] ?? '—',
                $sensors['Ignition'] ?? '—',
                $sensors['Fuel Level'] ?? '—',
                $sensors['Engine Hours'] ?? '—',
                $sensors['Mileage'] ?? '—',
                $sensors['Voltage'] ?? '—',
            ];
        }

        if (empty($rows)) {
            $rows[] = ['No GPS coordinates found for the selected period', '', '', '', '', '', '', '', '', '', '', ''];
        }

        return $rows;
    }

    public function headings(): array
    {
        $pointsCount = count($this->trackData['points'] ?? []);
        $sub = ($pointsCount > 2500) ? ' (Sampled ' . number_format($pointsCount) . ' total points)' : '';

        return [
            '#' . $sub,
            'GPS Time',
            'Latitude',
            'Longitude',
            'Speed (km/h)',
            'Altitude (m)',
            'Course / Heading',
            'Ignition',
            'Fuel Level',
            'Engine Hours',
            'Odometer / Mileage',
            'Battery Voltage',
        ];
    }

    public function title(): string
    {
        return 'GPS Telemetry Log';
    }

    public function styles(Worksheet $sheet): ?array
    {
        // Fixed column widths (super fast, near 0 memory calculation compared to ShouldAutoSize)
        $sheet->getColumnDimension('A')->setWidth(10);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(14);
        $sheet->getColumnDimension('D')->setWidth(14);
        $sheet->getColumnDimension('E')->setWidth(14);
        $sheet->getColumnDimension('F')->setWidth(14);
        $sheet->getColumnDimension('G')->setWidth(16);
        $sheet->getColumnDimension('H')->setWidth(12);
        $sheet->getColumnDimension('I')->setWidth(14);
        $sheet->getColumnDimension('J')->setWidth(14);
        $sheet->getColumnDimension('K')->setWidth(18);
        $sheet->getColumnDimension('L')->setWidth(16);

        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1E3A8A'],
                ],
            ],
        ];
    }
}
