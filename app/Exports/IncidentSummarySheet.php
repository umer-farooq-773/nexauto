<?php

namespace App\Exports;

use App\Services\DriverReportExcelService;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;

class IncidentSummarySheet implements FromArray, WithTitle, WithEvents
{
    protected array $counts = [];

    public function __construct(
        protected DriverReportExcelService $service,
        protected array $filters
    ) {
        $this->counts = $this->service->getTypeCounts($this->filters)->toArray();
    }

    public function array(): array
    {
        $rows = [['Incident Type', 'Count']];

        foreach ($this->counts as $label => $count) {
            $rows[] = [$label, $count];
        }

        if (count($rows) === 1) {
            $rows[] = ['No records for selected filters', 0];
        }

        return $rows;
    }

    public function title(): string
    {
        return 'Summary';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = count($this->counts) + 1;

                if ($lastRow < 2) {
                    return;
                }

                $sheet->getStyle('A1:B1')->getFont()->setBold(true);
                foreach (range('A', 'B') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                $categories = [
                    new DataSeriesValues('String', 'Summary!$A$2:$A$' . $lastRow, null, $lastRow - 1),
                ];
                $values = [
                    new DataSeriesValues('Number', 'Summary!$B$2:$B$' . $lastRow, null, $lastRow - 1),
                ];

                $series = new DataSeries(
                    DataSeries::TYPE_BARCHART,
                    DataSeries::GROUPING_CLUSTERED,
                    range(0, count($values) - 1),
                    [],
                    $categories,
                    $values
                );
                $series->setPlotDirection(DataSeries::DIRECTION_COL);

                $plotArea = new PlotArea(null, [$series]);
                $legend = new Legend(Legend::POSITION_RIGHT, null, false);
                $title = new Title('Incidents by Type');

                $chart = new Chart(
                    'incidents_by_type',
                    $title,
                    $legend,
                    $plotArea
                );

                $chart->setTopLeftPosition('D2');
                $chart->setBottomRightPosition('L20');

                $sheet->addChart($chart);
            },
        ];
    }
}
