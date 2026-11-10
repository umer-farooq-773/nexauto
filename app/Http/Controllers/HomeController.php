<?php

namespace App\Http\Controllers;

use App\Models\DriverMate;
use App\Models\Vehicle;
use App\Models\VehicleMaintenance;
use App\Models\Waybill;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        if (auth()->check()) {
            $user = auth()->user();
            if ($user->hasRole(\App\Constants\Constants::DRIVER) || ($user->driverDetail && !$user->hasAnyRole([
                \App\Constants\Constants::SUPERADMIN,
                \App\Constants\Constants::ADMIN,
                \App\Constants\Constants::CHAMPS_MANAGEMENT,
                \App\Constants\Constants::CLIENT_DIRECTORS,
                \App\Constants\Constants::CLIENT_MANAGEMENT,
            ]))) {
                return redirect()->route('driver.portal');
            }
        }

        $result = [
            'total_vehicle'      => Vehicle::count(),
            'total_driver_mate'  => DriverMate::count(),
            'total_incident'     => $this->countTotalIncidents(),
            'total_trips'        => Waybill::whereHas('status', function ($q) {
                $q->where('value', 2);
            })->count(),
            'total_inspection'   => 0,
            'total_gps'          => Waybill::whereHas('status', function ($q) {
                $q->where('value', 4);
            })->count(),
        ];
        $statusCounts = $this->getWaybillStatusBreakdown();
        // Explicitly get weekly data for initial load
        $chartData = $this->getAssetsAndIncidentsData('weekly');
        return view('auth.pages.dashboard', compact('result', 'statusCounts','chartData'));
    }

    private function getAssetsAndIncidentsData(string $period = 'weekly'): array
    {
        $labels            = [];
        $completedWaybills = [];
        $incidentsData     = [];

        // Get the status_id for "Completed" (value = 4) once
        $completedStatusId = DB::table('statuses')->where('value', 4)->value('id');

        switch ($period) {
            case 'weekly':
                for ($i = 6; $i >= 0; $i--) {
                    $day       = now()->subDays($i);
                    $labels[]  = $day->format('D, d M');
                    $completedWaybills[] = $completedStatusId
                        ? Waybill::where('status_id', $completedStatusId)
                            ->whereDate('created_at', $day->toDateString())
                            ->count()
                        : 0;
                    $incidentsData[] = $this->countIncidentsForDate($day->toDateString());
                }
                break;

            case 'monthly':
                for ($i = 11; $i >= 0; $i--) {
                    $month    = now()->subMonths($i);
                    $labels[] = $month->format('M Y');
                    $completedWaybills[] = $completedStatusId
                        ? Waybill::where('status_id', $completedStatusId)
                            ->whereYear('created_at', $month->year)
                            ->whereMonth('created_at', $month->month)
                            ->count()
                        : 0;
                    $incidentsData[] = $this->countIncidentsForMonth($month->year, $month->month);
                }
                break;

            case 'yearly':
                for ($i = 4; $i >= 0; $i--) {
                    $year     = now()->subYears($i)->year;
                    $labels[] = (string) $year;
                    $completedWaybills[] = $completedStatusId
                        ? Waybill::where('status_id', $completedStatusId)
                            ->whereYear('created_at', $year)
                            ->count()
                        : 0;
                    $incidentsData[] = $this->countIncidentsForYear($year);
                }
                break;
        }

        return [
            'labels'    => $labels,
            'assets'    => $completedWaybills,   // key kept as 'assets' so AJAX response is compatible
            'incidents' => $incidentsData,
        ];
    }

    /**
     * Count incidents for a specific date
     */
    private function countIncidentsForDate(string $date): int
    {
        return \App\Models\Accident::whereDate('created_at', $date)->count()
            + \App\Models\VehicleBreakdown::whereDate('created_at', $date)->count()
            + \App\Models\VehicleBoggedDown::whereDate('created_at', $date)->count()
            + \App\Models\Impoundment::whereDate('created_at', $date)->count()
            + \App\Models\RouteDiversion::whereDate('created_at', $date)->count()
            + \App\Models\IllegalDetention::whereDate('created_at', $date)->count()
            + \App\Models\IllegalHaulage::whereDate('created_at', $date)->count()
            + \App\Models\IllegalShortage::whereDate('created_at', $date)->count()
            + \App\Models\BreathaliserTest::whereDate('created_at', $date)->count()
            + \App\Models\DriverMisconduct::whereDate('created_at', $date)->count();
    }

    /**
     * Count incidents for a specific month
     */
    private function countIncidentsForMonth(int $year, int $month): int
    {
        return \App\Models\Accident::whereYear('created_at', $year)->whereMonth('created_at', $month)->count()
            + \App\Models\VehicleBreakdown::whereYear('created_at', $year)->whereMonth('created_at', $month)->count()
            + \App\Models\VehicleBoggedDown::whereYear('created_at', $year)->whereMonth('created_at', $month)->count()
            + \App\Models\Impoundment::whereYear('created_at', $year)->whereMonth('created_at', $month)->count()
            + \App\Models\RouteDiversion::whereYear('created_at', $year)->whereMonth('created_at', $month)->count()
            + \App\Models\IllegalDetention::whereYear('created_at', $year)->whereMonth('created_at', $month)->count()
            + \App\Models\IllegalHaulage::whereYear('created_at', $year)->whereMonth('created_at', $month)->count()
            + \App\Models\IllegalShortage::whereYear('created_at', $year)->whereMonth('created_at', $month)->count()
            + \App\Models\BreathaliserTest::whereYear('created_at', $year)->whereMonth('created_at', $month)->count()
            + \App\Models\DriverMisconduct::whereYear('created_at', $year)->whereMonth('created_at', $month)->count();
    }

    /**
     * Count incidents for a specific year
     */
    private function countIncidentsForYear(int $year): int
    {
        return \App\Models\Accident::whereYear('created_at', $year)->count()
            + \App\Models\VehicleBreakdown::whereYear('created_at', $year)->count()
            + \App\Models\VehicleBoggedDown::whereYear('created_at', $year)->count()
            + \App\Models\Impoundment::whereYear('created_at', $year)->count()
            + \App\Models\RouteDiversion::whereYear('created_at', $year)->count()
            + \App\Models\IllegalDetention::whereYear('created_at', $year)->count()
            + \App\Models\IllegalHaulage::whereYear('created_at', $year)->count()
            + \App\Models\IllegalShortage::whereYear('created_at', $year)->count()
            + \App\Models\BreathaliserTest::whereYear('created_at', $year)->count()
            + \App\Models\DriverMisconduct::whereYear('created_at', $year)->count();
    }

    /**
     * Sums incidents across every incident type table.
     */
    private function countTotalIncidents(): int
    {
        return \App\Models\Accident::count()
            + \App\Models\VehicleBreakdown::count()
            + \App\Models\VehicleBoggedDown::count()
            + \App\Models\Impoundment::count()
            + \App\Models\RouteDiversion::count()
            + \App\Models\IllegalDetention::count()
            + \App\Models\IllegalHaulage::count()
            + \App\Models\IllegalShortage::count()
            + \App\Models\BreathaliserTest::count()
            + \App\Models\DriverMisconduct::count();
    }

    /**
     * Returns waybill counts grouped by status for the Fleet Health chart.
     */
    private function getWaybillStatusBreakdown(): array
    {
        $statuses = DB::table('statuses')
            ->orderBy('value')
            ->get(['id', 'name', 'badge_class']);

        $counts = Waybill::selectRaw('status_id, COUNT(*) as total')
            ->groupBy('status_id')
            ->pluck('total', 'status_id');

        $labels = [];
        $data = [];
        $colors = [];

        foreach ($statuses as $status) {
            $labels[] = $status->name;
            $data[] = (int) ($counts[$status->id] ?? 0);
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
     * Maps Bootstrap badge class to hex color.
     */
    private function resolveChartColor(?string $badgeClass): string
    {
        $map = [
            'bg-warning'   => '#ff9f1c',
            'bg-info'      => '#3a86ff',
            'bg-primary'   => '#4361ee',
            'bg-secondary' => '#2dc653',  // Completed — green
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

    public function logout()
    {
        Auth::logout();
        return redirect()->route('login');
    }

    /**
     * AJAX – returns Operation Performance chart data for the chosen period.
     * period: weekly | monthly | yearly
     */
    public function chartFilterData(Request $request): \Illuminate\Http\JsonResponse
    {
        $period = $request->input('period', 'weekly');
        $chartData = $this->getAssetsAndIncidentsData($period);

        return response()->json($chartData);
    }
}
