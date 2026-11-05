<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VehicleHistoryTripsSheet implements FromArray, WithHeadings, WithStyles, WithTitle, ShouldAutoSize, Export
{
    use Exportable;
    public function __construct(
        protected array $trackData
    ) {}

    public function array(): array
    {
        $trips = $this->trackData['trips'] ?? [];
        $rows = [];

        foreach ($trips as $idx => $trip) {
            $startPt = $trip['start_point'] ?? [];
            $endPt   = $trip['end_point'] ?? [];

            $startCoords = (!empty($startPt) && count($startPt) >= 2)
                ? sprintf('%.5f, %.5f', $startPt[1], $startPt[0])
                : '—';

            $endCoords = (!empty($endPt) && count($endPt) >= 2)
                ? sprintf('%.5f, %.5f', $endPt[1], $endPt[0])
                : '—';

            $rows[] = [
                $trip['id'] ?? ($idx + 1),
                $trip['name'] ?? ('Trip ' . ($idx + 1)),
                $trip['start_time_fmt'] ?? ($trip['start_formatted'] ?? '—'),
                $trip['end_time_fmt'] ?? ($trip['end_formatted'] ?? '—'),
                $trip['duration_fmt'] ?? '—',
                $trip['distance_km'] ?? 0,
                $trip['max_speed'] ?? 0,
                $trip['avg_speed'] ?? 0,
                $startCoords,
                $endCoords,
                $trip['points_count'] ?? 0,
            ];
        }

        if (empty($rows)) {
            $rows[] = ['No trips recorded for the selected period', '', '', '', '', '', '', '', '', '', ''];
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Trip #',
            'Trip Label',
            'Start Time',
            'End Time',
            'Duration',
            'Distance (km)',
            'Max Speed (km/h)',
            'Avg Speed (km/h)',
            'Start Location (Lat, Lng)',
            'End Location (Lat, Lng)',
            'GPS Points Count',
        ];
    }

    public function title(): string
    {
        return 'Trips Breakdown';
    }

    public function styles(Worksheet $sheet): ?array
    {
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
