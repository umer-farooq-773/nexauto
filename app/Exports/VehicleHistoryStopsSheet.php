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

class VehicleHistoryStopsSheet implements FromArray, WithHeadings, WithStyles, WithTitle, ShouldAutoSize, Export
{
    use Exportable;
    public function __construct(
        protected array $trackData
    ) {}

    public function array(): array
    {
        $stops = $this->trackData['stops'] ?? [];
        $rows = [];

        foreach ($stops as $idx => $stop) {
            $lat = $stop['lat'] ?? null;
            $lng = $stop['lng'] ?? null;

            $coords = ($lat !== null && $lng !== null)
                ? sprintf('%.5f, %.5f', $lat, $lng)
                : '—';

            $rows[] = [
                $stop['id'] ?? ($idx + 1),
                $stop['start_fmt'] ?? '—',
                $stop['end_fmt'] ?? '—',
                $stop['duration_fmt'] ?? '—',
                $coords,
                $lat ?? '—',
                $lng ?? '—',
            ];
        }

        if (empty($rows)) {
            $rows[] = ['No stationary stops recorded for the selected period', '', '', '', '', '', ''];
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Stop #',
            'Arrival Time',
            'Departure Time',
            'Parked Duration',
            'Coordinates (Lat, Lng)',
            'Latitude',
            'Longitude',
        ];
    }

    public function title(): string
    {
        return 'Stops & Idle Time';
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
