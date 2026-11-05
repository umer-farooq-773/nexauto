<?php

namespace App\View\Components\Auth\Admin;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use App\Models\Impoundment;
use App\Models\VehicleBreakdown;
use App\Models\VehicleBoggedDown;
use App\Models\IllegalDetention;
use App\Models\IllegalHaulage;
use App\Models\IllegalShortage;
use App\Models\BreathaliserTest;
use App\Models\Accident;
use App\Models\RouteDiversion;
use App\Models\DriverMisconduct;
use App\Models\RouteTrip;
use App\Models\GoodsInspection;
use App\Models\Vehicle;
use App\Models\VehicleMaintenance;
use App\Models\Waybill;
use Illuminate\Support\Facades\DB;

class Dashboard extends Component
{
    public $result;
    public $statusCounts;
    public $chartData;
    /**
     * Create a new component instance.
    */
    public function __construct() {
        $this->result['total_vehicle'] = count(app()->make('App\Interfaces\VehicleInterface')->all());
        $this->result['total_driver_mate'] = count(app()->make('App\Interfaces\DriverMateInterface')->all());
        $this->result['total_incident'] = Impoundment::count()
            + VehicleBreakdown::count()
            + VehicleBoggedDown::count()
            + IllegalDetention::count()
            + IllegalHaulage::count()
            + IllegalShortage::count()
            + BreathaliserTest::count()
            + Accident::count()
            + RouteDiversion::count()
            + DriverMisconduct::count();
        $this->result['total_trips'] = RouteTrip::count();
        $this->result['total_inspection'] = GoodsInspection::count();
        $this->result['total_gps'] = VehicleMaintenance::where('status', '1')->count();
        $this->statusCounts = $this->getWaybillStatusBreakdown();
        $this->chartData = $this->getAssetsAndIncidentsData('weekly');
    }

    private function getAssetsAndIncidentsData(string $period = 'weekly'): array
    {
        $labels = [];
        $assetsData = [];
        $incidentsData = [];

        switch ($period) {
            case 'weekly':
                for ($i = 6; $i >= 0; $i--) {
                    $day = now()->subDays($i);
                    $labels[] = $day->format('D, d M');
                    $assetsData[] = Vehicle::whereDate('created_at', $day->toDateString())->count();
                    $incidentsData[] = $this->countIncidentsForDate($day->toDateString());
                }
                break;

            case 'monthly':
                for ($i = 11; $i >= 0; $i--) {
                    $month = now()->subMonths($i);
                    $labels[] = $month->format('M Y');
                    $assetsData[] = Vehicle::whereYear('created_at', $month->year)
                                ->whereMonth('created_at', $month->month)->count();
                    $incidentsData[] = $this->countIncidentsForMonth($month->year, $month->month);
                }
                break;

            case 'yearly':
                for ($i = 4; $i >= 0; $i--) {
                    $year = now()->subYears($i)->year;
                    $labels[] = (string) $year;
                    $assetsData[] = Vehicle::whereYear('created_at', $year)->count();
                    $incidentsData[] = $this->countIncidentsForYear($year);
                }
                break;
        }

        return [
            'labels'    => $labels,
            'assets'    => $assetsData,
            'incidents' => $incidentsData,
        ];
    }

    private function countIncidentsForDate(string $date): int
    {
        return Accident::whereDate('created_at', $date)->count()
            + VehicleBreakdown::whereDate('created_at', $date)->count()
            + VehicleBoggedDown::whereDate('created_at', $date)->count()
            + Impoundment::whereDate('created_at', $date)->count()
            + RouteDiversion::whereDate('created_at', $date)->count()
            + IllegalDetention::whereDate('created_at', $date)->count()
            + IllegalHaulage::whereDate('created_at', $date)->count()
            + IllegalShortage::whereDate('created_at', $date)->count()
            + BreathaliserTest::whereDate('created_at', $date)->count()
            + DriverMisconduct::whereDate('created_at', $date)->count();
    }

    private function countIncidentsForMonth(int $year, int $month): int
    {
        return Accident::whereYear('created_at', $year)->whereMonth('created_at', $month)->count()
            + VehicleBreakdown::whereYear('created_at', $year)->whereMonth('created_at', $month)->count()
            + VehicleBoggedDown::whereYear('created_at', $year)->whereMonth('created_at', $month)->count()
            + Impoundment::whereYear('created_at', $year)->whereMonth('created_at', $month)->count()
            + RouteDiversion::whereYear('created_at', $year)->whereMonth('created_at', $month)->count()
            + IllegalDetention::whereYear('created_at', $year)->whereMonth('created_at', $month)->count()
            + IllegalHaulage::whereYear('created_at', $year)->whereMonth('created_at', $month)->count()
            + IllegalShortage::whereYear('created_at', $year)->whereMonth('created_at', $month)->count()
            + BreathaliserTest::whereYear('created_at', $year)->whereMonth('created_at', $month)->count()
            + DriverMisconduct::whereYear('created_at', $year)->whereMonth('created_at', $month)->count();
    }

    private function countIncidentsForYear(int $year): int
    {
        return Accident::whereYear('created_at', $year)->count()
            + VehicleBreakdown::whereYear('created_at', $year)->count()
            + VehicleBoggedDown::whereYear('created_at', $year)->count()
            + Impoundment::whereYear('created_at', $year)->count()
            + RouteDiversion::whereYear('created_at', $year)->count()
            + IllegalDetention::whereYear('created_at', $year)->count()
            + IllegalHaulage::whereYear('created_at', $year)->count()
            + IllegalShortage::whereYear('created_at', $year)->count()
            + BreathaliserTest::whereYear('created_at', $year)->count()
            + DriverMisconduct::whereYear('created_at', $year)->count();
    }

    /**
     * Returns waybill counts grouped by status for the Fleet Health chart.
     */
    private function getWaybillStatusBreakdown(): array
    {
        $statuses = DB::table('statuses')->orderBy('value')->get(['id', 'name', 'badge_class']);

        $counts = Waybill::selectRaw('status_id, COUNT(*) as total')
            ->groupBy('status_id')
            ->pluck('total', 'status_id');

        $labels = [];
        $data   = [];
        $colors = [];

        foreach ($statuses as $status) {
            $labels[] = $status->name;
            $data[]   = (int) ($counts[$status->id] ?? 0);
            $colors[] = $this->resolveChartColor($status->badge_class);
        }

        return [
            'labels' => $labels,
            'data'   => $data,
            'colors' => $colors,
            'total'  => array_sum($data),
        ];
    }

    /**
     * Maps Bootstrap badge class to a hex colour for the chart.
     */
    private function resolveChartColor(?string $badgeClass): string
    {
        $map = [
            'bg-warning'   => '#ff9f1c',
            'bg-info'      => '#3a86ff',
            'bg-primary'   => '#4361ee',
            'bg-secondary' => '#6c757d',
            'bg-success'   => '#2ec4b6',
            'bg-danger'    => '#e71d36',
            'bg-dark'      => '#1a1a2e',
            'bg-light'     => '#e9ecef',
        ];

        foreach ($map as $class => $hex) {
            if ($badgeClass && str_contains($badgeClass, $class)) {
                return $hex;
            }
        }

        return '#adb5bd';
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.auth.admin.dashboard');
    }
}
