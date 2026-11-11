<?php

namespace App\Http\Controllers;

use App\Dto\WaybillDto;
use App\Interfaces\WaybillInterface;
use App\Http\Requests\WaybillRequest;
use App\Services\IncidentWaybillNotificationService;
use App\Services\WialonService;
use App\Models\Waybill;
use App\Models\Impoundment;
use App\Models\VehicleBreakdown;
use App\Models\VehicleBoggedDown;
use App\Models\IllegalDetention;
use App\Models\Accident;
use App\Models\User;
use App\Notifications\WaybillStatusChangedNotification;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Validation\ValidationException;
use App\Constants\Constants;
use App\Models\RouteDiversion;
use App\Models\DriverMisconduct;
use App\Models\IllegalHaulage;
use App\Models\IllegalShortage;
use App\Models\BreathaliserTest;
use App\Models\GoodsInspection;
use App\Models\Vehicle;
use Barryvdh\DomPDF\Facade\Pdf;

class WaybillController extends BaseController
{
    protected $vehicle_model;
    public function __construct(
        WaybillInterface $repo,
        protected IncidentWaybillNotificationService $incidentWaybillNotificationService,
        protected WialonService $wialonService
    ) {
        $this->setRepo($repo, 'auth.pages.waybill', 'waybill');
        $this->vehicle_model = Vehicle::class;
    }

    public function index()
    {
        $data['all'] = $this->_repo->index();

        // Use efficient count queries instead of loading all records
        $data['total_count']     = \App\Models\Waybill::count();
        $data['awaiting_count']  = \App\Models\Waybill::whereHas('status', fn($q) => $q->where('value', 0))->count();
        $data['allocated_count'] = \App\Models\Waybill::whereHas('status', fn($q) => $q->where('value', 1))->count();
        $data['delivered_count'] = \App\Models\Waybill::whereHas('status', fn($q) => $q->where('value', 3))->count();

        $data['vehicles']    = app()->make('App\Interfaces\VehicleInterface')->all();
        $data['drivers']     = app()->make('App\Interfaces\DriverDetailInterface')->all();
        $data['route_trips'] = app()->make('App\Interfaces\RouteTripInterface')->all();
        return view($this->_directory . '.all', compact('data'));
    }

    public function printFull($id)
    {
        $data = $this->resolveWaybillForPdf($id);

        $incidents = collect([
            'accidents'            => $data->accidents ?? null,
            'vehicle_breakdowns'   => $data->vehicle_breakdowns ?? null,
            'vehicle_bogged_downs' => $data->vehicle_bogged_downs ?? null,
            'impoundments'         => $data->impoundments ?? null,
            'route_diversions'     => $data->route_diversions ?? null,
            'illegal_detentions'   => $data->illegal_detentions ?? null,
            'illegal_haulages'     => $data->illegal_haulages ?? null,
            'illegal_shortages'    => $data->illegal_shortages ?? null,
            'breathaliser_tests'   => $data->breathaliser_tests ?? null,
            'driver_misconducts'   => $data->driver_misconducts ?? null,
            'goods_inspections'    => $data->goods_inspections ?? null,
        ])->reject(fn ($collection) => $collection === null || $collection->isEmpty());

        $pdf = Pdf::loadView($this->_directory . '.pdf.full', compact('data', 'incidents'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('waybill-' . $data->waybill_number . '.pdf');
    }

    public function printDetail($id)
    {
        $data = $this->resolveWaybillForPdf($id);

        $pdf = Pdf::loadView($this->_directory . '.pdf.detail', compact('data'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('waybill-' . $data->waybill_number . '-detail.pdf');
    }

    private function resolveWaybillForPdf($id)
    {
        $paginator = $this->_repo->showWithIncidents($id);
        $data = $paginator->first();

        if ($data === null) {
            abort(404);
        }

        return $data;
    }

    public function vehicleTracking(Waybill $waybill)
    {
        $waybill->load(['vehicle', 'driver', 'status', 'route_trip']);

        $vehicle = $waybill->vehicle;

        // gps_unit_id is Wialon's numeric system ID. It is populated by the
        // wialon:sync-vehicles command and is the ID the JavaScript SDK uses.
        $matchedUnitId = $vehicle?->gps_unit_id
            ? (string) $vehicle->gps_unit_id
            : null;

        $mapIncidents = $this->getWaybillMapIncidents($waybill);

        return view('auth.pages.waybill.partials._map_tracking', [
            'wialonToken'    => config('services.wialon.token'),
            'wialonHost'     => config('services.wialon.host'),
            'geoapifyApiKey' => config('services.geoapify.api_key'),
            'appEnv'         => config('app.env'),
            'fleetNo'        => $vehicle?->fleet_no,
            'matchedUnitId'  => $matchedUnitId,
            'waybill'        => $waybill,
            'mapIncidents'   => $mapIncidents,
            'embedded'       => request()->boolean('embedded'),
        ]);
    }

    /**
     * Aggregate and format all incidents and driver behavior events linked to a waybill for map visualization.
     */
    protected function getWaybillMapIncidents(Waybill $waybill): array
    {
        $incidents = [];

        // 1. Accidents - Show only incidents linked to this waybill
        $accidents = Accident::where('waybill_id', $waybill->id)->get();
        foreach ($accidents as $item) {
            $incidents[] = [
                'id'             => (string) $item->id,
                'category'       => 'incident',
                'category_label' => 'Incident Intervention',
                'type_key'       => 'accident',
                'type_label'     => 'Accident',
                'icon'           => 'fa-solid fa-car-burst',
                'color'          => '#ef4444',
                'badge_class'    => 'bg-danger',
                'date'           => $item->accident_date?->format('d M Y') ?? '-',
                'location'       => $item->accident_location ?? 'N/A',
                'lat'            => $item->accident_location_lat !== null ? (float) $item->accident_location_lat : null,
                'lng'            => $item->accident_location_lng !== null ? (float) $item->accident_location_lng : null,
                'detail_type'    => $item->accident_type ?? 'Accident',
                'detail_extra'   => $item->accident_type_detail,
                'description'    => $item->description ?? $item->content ?? '-',
                'damage_cost'    => $item->damage_cost ? number_format((float) $item->damage_cost, 2) : null,
                'fatality'       => $item->is_fatality,
                'status'         => (string) $item->status,
                'status_label'   => match ((string) $item->status) {
                    '1' => 'Resolved',
                    '2' => 'In Progress',
                    default => 'Pending',
                },
                'status_color'   => match ((string) $item->status) {
                    '1' => 'success',
                    '2' => 'warning',
                    default => 'danger',
                },
                'show_url'       => route('accident.show', $item->id),
            ];
        }

        // 2. Vehicle Breakdowns - Show only incidents linked to this waybill
        $breakdowns = VehicleBreakdown::where('waybill_id', $waybill->id)->get();
        foreach ($breakdowns as $item) {
            $incidents[] = [
                'id'             => (string) $item->id,
                'category'       => 'incident',
                'category_label' => 'Incident Intervention',
                'type_key'       => 'vehicle_breakdown',
                'type_label'     => 'Vehicle Breakdown',
                'icon'           => 'fa-solid fa-wrench',
                'color'          => '#f97316',
                'badge_class'    => 'bg-warning text-dark',
                'date'           => $item->breakdown_date?->format('d M Y') ?? '-',
                'location'       => $item->breakdown_location ?? 'N/A',
                'lat'            => $item->breakdown_location_lat !== null ? (float) $item->breakdown_location_lat : null,
                'lng'            => $item->breakdown_location_lng !== null ? (float) $item->breakdown_location_lng : null,
                'detail_type'    => $item->breakdown_type ?? 'Breakdown',
                'detail_extra'   => null,
                'description'    => $item->description ?? '-',
                'damage_cost'    => $item->repair_cost ? number_format((float) $item->repair_cost, 2) : null,
                'fatality'       => null,
                'status'         => (string) $item->status,
                'status_label'   => match ((string) $item->status) {
                    '1' => 'Resolved',
                    '2' => 'In Progress',
                    default => 'Pending',
                },
                'status_color'   => match ((string) $item->status) {
                    '1' => 'success',
                    '2' => 'warning',
                    default => 'danger',
                },
                'show_url'       => route('vehicle_breakdown.show', $item->id),
            ];
        }

        // 3. Vehicle Bogged Downs - Show only incidents linked to this waybill
        $bogged = VehicleBoggedDown::where('waybill_id', $waybill->id)->get();
        foreach ($bogged as $item) {
            $incidents[] = [
                'id'             => (string) $item->id,
                'category'       => 'incident',
                'category_label' => 'Incident Intervention',
                'type_key'       => 'vehicle_bogged_down',
                'type_label'     => 'Bogged Down',
                'icon'           => 'fa-solid fa-truck-monster',
                'color'          => '#d97706',
                'badge_class'    => 'bg-warning text-dark',
                'date'           => $item->bogged_date?->format('d M Y') ?? '-',
                'location'       => $item->stuck_location ?? 'N/A',
                'lat'            => $item->stuck_location_lat !== null ? (float) $item->stuck_location_lat : null,
                'lng'            => $item->stuck_location_lng !== null ? (float) $item->stuck_location_lng : null,
                'detail_type'    => $item->ground_condition ?? 'Bogged Down',
                'detail_extra'   => null,
                'description'    => $item->description ?? '-',
                'damage_cost'    => $item->recovery_cost ? number_format((float) $item->recovery_cost, 2) : null,
                'fatality'       => null,
                'status'         => (string) $item->status,
                'status_label'   => match ((string) $item->status) {
                    '1' => 'Recovered',
                    '2' => 'Recovery In Progress',
                    default => 'Stuck / Pending',
                },
                'status_color'   => match ((string) $item->status) {
                    '1' => 'success',
                    '2' => 'warning',
                    default => 'danger',
                },
                'show_url'       => route('vehicle_bogged_down.show', $item->id),
            ];
        }

        // 4. Impoundments - Show only incidents linked to this waybill
        $impoundments = Impoundment::where('waybill_id', $waybill->id)->get();
        foreach ($impoundments as $item) {
            $incidents[] = [
                'id'             => (string) $item->id,
                'category'       => 'incident',
                'category_label' => 'Incident Intervention',
                'type_key'       => 'impoundment',
                'type_label'     => 'Impoundment',
                'icon'           => 'fa-solid fa-building-shield',
                'color'          => '#8b5cf6',
                'badge_class'    => 'bg-purple text-white',
                'date'           => $item->impound_date?->format('d M Y') ?? '-',
                'location'       => $item->impound_location ?? 'N/A',
                'lat'            => $item->impound_location_lat !== null ? (float) $item->impound_location_lat : null,
                'lng'            => $item->impound_location_lng !== null ? (float) $item->impound_location_lng : null,
                'detail_type'    => $item->impound_authority ?? 'Impounded',
                'detail_extra'   => $item->release_date ? 'Released: ' . $item->release_date->format('d M Y') : null,
                'description'    => $item->reason ?? $item->remarks ?? '-',
                'damage_cost'    => $item->cost ? number_format((float) $item->cost, 2) : null,
                'fatality'       => null,
                'status'         => (string) $item->status,
                'status_label'   => $item->status == '1' ? 'Released' : 'Impounded',
                'status_color'   => $item->status == '1' ? 'success' : 'danger',
                'show_url'       => route('impoundment.show', $item->id),
            ];
        }

        // 5. Illegal Detentions - Show only incidents linked to this waybill
        $detentions = IllegalDetention::where('waybill_id', $waybill->id)->get();
        foreach ($detentions as $item) {
            $incidents[] = [
                'id'             => (string) $item->id,
                'category'       => 'incident',
                'category_label' => 'Incident Intervention',
                'type_key'       => 'illegal_detention',
                'type_label'     => 'Illegal Detention',
                'icon'           => 'fa-solid fa-handcuffs',
                'color'          => '#dc2626',
                'badge_class'    => 'bg-danger',
                'date'           => $item->detention_date?->format('d M Y') ?? '-',
                'location'       => $item->detention_location ?? 'N/A',
                'lat'            => $item->detention_location_lat !== null ? (float) $item->detention_location_lat : null,
                'lng'            => $item->detention_location_lng !== null ? (float) $item->detention_location_lng : null,
                'detail_type'    => $item->detained_by ?? 'Detained',
                'detail_extra'   => $item->release_date ? 'Released: ' . $item->release_date->format('d M Y') : null,
                'description'    => $item->reason ?? $item->remarks ?? '-',
                'damage_cost'    => $item->cost ? number_format((float) $item->cost, 2) : null,
                'fatality'       => null,
                'status'         => (string) $item->status,
                'status_label'   => $item->status == '1' ? 'Released' : 'Detained',
                'status_color'   => $item->status == '1' ? 'success' : 'danger',
                'show_url'       => route('illegal_detention.show', $item->id),
            ];
        }

        // 6. Route Diversions - Show only incidents linked to this waybill
        $diversions = RouteDiversion::where('waybill_id', $waybill->id)->get();
        foreach ($diversions as $item) {
            $incidents[] = [
                'id'             => (string) $item->id,
                'category'       => 'driver_behavior',
                'category_label' => 'Driver Behavior',
                'type_key'       => 'route_diversion',
                'type_label'     => 'Route Diversion',
                'icon'           => 'fa-solid fa-route',
                'color'          => '#0284c7',
                'badge_class'    => 'bg-info text-white',
                'date'           => $item->diversion_date?->format('d M Y') ?? '-',
                'location'       => $item->diversion_location ?? 'N/A',
                'lat'            => $item->diversion_location_lat !== null ? (float) $item->diversion_location_lat : null,
                'lng'            => $item->diversion_location_lng !== null ? (float) $item->diversion_location_lng : null,
                'detail_type'    => 'Diverted: ' . ($item->diverted_route ?? 'Unauthorized Route'),
                'detail_extra'   => 'Original: ' . ($item->original_route ?? '-'),
                'description'    => $item->diversion_reason ?? $item->remarks ?? '-',
                'damage_cost'    => null,
                'fatality'       => null,
                'status'         => (string) $item->status,
                'status_label'   => match ((string) $item->status) {
                    '1' => 'Completed',
                    '2' => 'Cancelled',
                    default => 'Active Diversion',
                },
                'status_color'   => match ((string) $item->status) {
                    '1' => 'success',
                    '2' => 'secondary',
                    default => 'warning',
                },
                'show_url'       => route('route_diversion.show', $item->id),
            ];
        }

        // 7. Illegal Haulages - Show only incidents linked to this waybill
        $haulages = IllegalHaulage::where('waybill_id', $waybill->id)->get();
        foreach ($haulages as $item) {
            $incidents[] = [
                'id'             => (string) $item->id,
                'category'       => 'driver_behavior',
                'category_label' => 'Driver Behavior',
                'type_key'       => 'illegal_haulage',
                'type_label'     => 'Illegal Haulage',
                'icon'           => 'fa-solid fa-boxes-packing',
                'color'          => '#ea580c',
                'badge_class'    => 'bg-warning text-dark',
                'date'           => $item->haulage_date?->format('d M Y') ?? '-',
                'location'       => $item->haulage_location ?? 'N/A',
                'lat'            => $item->haulage_location_lat !== null ? (float) $item->haulage_location_lat : null,
                'lng'            => $item->haulage_location_lng !== null ? (float) $item->haulage_location_lng : null,
                'detail_type'    => $item->haulage_type ?? 'Illegal Haulage',
                'detail_extra'   => $item->severity ? 'Severity: ' . ucfirst($item->severity) : null,
                'description'    => $item->cargo_description ?? $item->remarks ?? $item->action_taken ?? '-',
                'damage_cost'    => null,
                'fatality'       => null,
                'status'         => (string) $item->status,
                'status_label'   => match ((string) $item->status) {
                    '1' => 'Reviewed',
                    '2' => 'Resolved',
                    default => 'Open',
                },
                'status_color'   => match ((string) $item->status) {
                    '1' => 'warning',
                    '2' => 'success',
                    default => 'danger',
                },
                'show_url'       => route('illegal_haulage.show', $item->id),
            ];
        }

        // Illegal Shortages - Show only incidents linked to this waybill
        $shortages = IllegalShortage::where('waybill_id', $waybill->id)->get();
        foreach ($shortages as $item) {
            $incidents[] = [
                'id'             => (string) $item->id,
                'category'       => 'driver_behavior',
                'category_label' => 'Driver Behavior',
                'type_key'       => 'illegal_shortage',
                'type_label'     => 'Illegal Shortage',
                'icon'           => 'fa-solid fa-boxes-packing',
                'color'          => '#d97706',
                'badge_class'    => 'bg-warning text-dark',
                'date'           => $item->shortage_date?->format('d M Y') ?? '-',
                'location'       => $item->shortage_location ?? 'N/A',
                'lat'            => $item->shortage_location_lat !== null ? (float) $item->shortage_location_lat : null,
                'lng'            => $item->shortage_location_lng !== null ? (float) $item->shortage_location_lng : null,
                'detail_type'    => $item->shortage_type ?? 'Illegal Shortage',
                'detail_extra'   => ($item->shortage_quantity ? 'Qty: ' . $item->shortage_quantity . ' | ' : '') . ($item->severity ? 'Severity: ' . ucfirst($item->severity) : null),
                'description'    => $item->cargo_description ?? $item->remarks ?? $item->action_taken ?? '-',
                'damage_cost'    => null,
                'fatality'       => null,
                'status'         => (string) $item->status,
                'status_label'   => match ((string) $item->status) {
                    '1' => 'Reviewed',
                    '2' => 'Resolved',
                    default => 'Open',
                },
                'status_color'   => match ((string) $item->status) {
                    '1' => 'warning',
                    '2' => 'success',
                    default => 'danger',
                },
                'show_url'       => route('illegal_shortage.show', $item->id),
            ];
        }

        // 8. Breathaliser Tests - Show only incidents linked to this waybill
        $tests = BreathaliserTest::where('waybill_id', $waybill->id)->get();
        foreach ($tests as $item) {
            $incidents[] = [
                'id'             => (string) $item->id,
                'category'       => 'driver_behavior',
                'category_label' => 'Driver Behavior',
                'type_key'       => 'breathaliser_test',
                'type_label'     => 'Breathaliser Test',
                'icon'           => 'fa-solid fa-wine-bottle',
                'color'          => '#e11d48',
                'badge_class'    => 'bg-danger',
                'date'           => $item->test_date?->format('d M Y') ?? '-',
                'location'       => $item->test_location ?? 'N/A',
                'lat'            => $item->test_location_lat !== null ? (float) $item->test_location_lat : null,
                'lng'            => $item->test_location_lng !== null ? (float) $item->test_location_lng : null,
                'detail_type'    => 'Result: ' . ucfirst($item->test_result ?? 'Tested') . ($item->bac_level ? ' (BAC: ' . $item->bac_level . ')' : ''),
                'detail_extra'   => $item->device_number ? 'Device: ' . $item->device_number : null,
                'description'    => $item->remarks ?? $item->action_taken ?? '-',
                'damage_cost'    => null,
                'fatality'       => null,
                'status'         => (string) $item->status,
                'status_label'   => match ((string) $item->status) {
                    '1' => 'Reviewed',
                    '2' => 'Closed',
                    default => 'Open',
                },
                'status_color'   => match ((string) $item->status) {
                    '1' => 'warning',
                    '2' => 'success',
                    default => 'danger',
                },
                'show_url'       => route('breathaliser_test.show', $item->id),
            ];
        }

        // 9. Driver Misconducts - Show only incidents linked to this waybill
        $misconducts = DriverMisconduct::where('waybill_id', $waybill->id)->get();
        foreach ($misconducts as $item) {
            $incidents[] = [
                'id'             => (string) $item->id,
                'category'       => 'driver_behavior',
                'category_label' => 'Driver Behavior',
                'type_key'       => 'driver_misconduct',
                'type_label'     => 'Driver Misconduct',
                'icon'           => 'fa-solid fa-triangle-exclamation',
                'color'          => '#b91c1c',
                'badge_class'    => 'bg-danger',
                'date'           => $item->misconduct_date?->format('d M Y') ?? '-',
                'location'       => $item->misconduct_location ?? 'N/A',
                'lat'            => $item->misconduct_location_lat !== null ? (float) $item->misconduct_location_lat : null,
                'lng'            => $item->misconduct_location_lng !== null ? (float) $item->misconduct_location_lng : null,
                'detail_type'    => $item->misconduct_type ?? 'Misconduct',
                'detail_extra'   => $item->severity ? 'Severity: ' . ucfirst($item->severity) : null,
                'description'    => $item->remarks ?? $item->description ?? $item->action_taken ?? '-',
                'damage_cost'    => null,
                'fatality'       => null,
                'status'         => (string) $item->status,
                'status_label'   => match ((string) $item->status) {
                    '1' => 'Reviewed',
                    '2' => 'Resolved',
                    default => 'Open',
                },
                'status_color'   => match ((string) $item->status) {
                    '1' => 'warning',
                    '2' => 'success',
                    default => 'danger',
                },
                'show_url'       => route('driver_misconduct.show', $item->id),
            ];
        }

        // 10. Goods Inspections
        $goodsInspections = GoodsInspection::where('waybill_id', $waybill->id)->get();
        foreach ($goodsInspections as $item) {
            $incidents[] = [
                'id'             => (string) $item->id,
                'category'       => 'goods_inspection',
                'category_label' => 'Goods Inspection',
                'type_key'       => 'goods_inspection',
                'type_label'     => 'Goods Inspection',
                'icon'           => 'fa-solid fa-clipboard-check',
                'color'          => '#3b82f6',
                'badge_class'    => 'bg-primary',
                'date'           => $item->inspection_date?->format('d M Y') ?? '-',
                'location'       => $item->inspection_location ?? 'N/A',
                'lat'            => $item->inspection_location_lat !== null ? (float) $item->inspection_location_lat : null,
                'lng'            => $item->inspection_location_lng !== null ? (float) $item->inspection_location_lng : null,
                'detail_type'    => 'Inspection: ' . ucfirst($item->inspection_type ?? 'General'),
                'detail_extra'   => 'Status: ' . ucfirst($item->goods_status ?? 'Cleared'),
                'description'    => $item->remarks ?? $item->goods_description ?? '-',
                'damage_cost'    => null,
                'fatality'       => null,
                'status'         => (string) $item->status,
                'status_label'   => match ((string) $item->status) {
                    '1' => 'Completed',
                    default => 'Pending',
                },
                'status_color'   => match ((string) $item->status) {
                    '1' => 'success',
                    default => 'warning',
                },
                'show_url'       => route('goods_inspection.show', $item->id),
                'single_map_url' => route('incident.single_map', ['type' => 'goods_inspection', 'id' => $item->id]),
            ];
        }

        return $incidents;
    }

    private function getIncidentsByVehicle($vehicleIds)
    {
        $incidents = [];
        $types = [
            'impoundment'       => [Impoundment::class,     'impoundment.index',       ['1']],
            'vehicle_breakdown' => [VehicleBreakdown::class, 'vehicle_breakdown.index', ['1']],
            'vehicle_bogged_down' => [VehicleBoggedDown::class, 'vehicle_bogged_down.index', ['1']],
            'illegal_detention' => [IllegalDetention::class, 'illegal_detention.index', ['1']],
            'accident'          => [Accident::class,         'accident.index',          ['1']],
        ];

        foreach ($types as $key => [$model, $route, $resolvedStatuses]) {
            $model::whereIn('vehicle_id', $vehicleIds)
                ->whereNotIn('status', $resolvedStatuses)
                ->get()
                ->each(function ($record) use (&$incidents, $key, $route) {
                    $incidents[$record->vehicle_id][] = [
                        'type'  => $key,
                        'label' => ucwords(str_replace('_', ' ', $key)),
                        'route' => route($route),
                    ];
                });
        }

        return $incidents;
    }

    public function awaiting()
    {
        $data['all']            = $this->_repo->awaiting();
        $data['awaiting_count'] = $data['all']->total();

        $data['vehicles']    = app()->make('App\Interfaces\VehicleInterface')->all();
        $data['drivers']     = app()->make('App\Interfaces\DriverDetailInterface')->all();
        $data['route_trips'] = app()->make('App\Interfaces\RouteTripInterface')->all();

        return view($this->_directory . '.awaiting', compact('data'));
    }

    public function showWithIncRec($id)
    {
        $paginator = $this->_repo->showWithIncidents($id);

        $data = $paginator->first();

        if ($data === null) {
            abort(404);
        }

        $perPage = 10;
        $currentPage = request()->get('page', 1);

        $incidents = collect([
            'accidents'            => $this->manualPaginate($data->accidents, $perPage, $currentPage, 'accidents_page'),
            'vehicle_breakdowns'   => $this->manualPaginate($data->vehicle_breakdowns, $perPage, $currentPage, 'breakdowns_page'),
            'vehicle_bogged_downs' => $this->manualPaginate($data->vehicle_bogged_downs, $perPage, $currentPage, 'bogged_page'),
            'impoundments'         => $this->manualPaginate($data->impoundments, $perPage, $currentPage, 'impound_page'),
            'route_diversions'     => $this->manualPaginate($data->route_diversions, $perPage, $currentPage, 'diversion_page'),
            'illegal_detentions'   => $this->manualPaginate($data->illegal_detentions, $perPage, $currentPage, 'detention_page'),
            'illegal_haulages'     => $this->manualPaginate($data->illegal_haulages, $perPage, $currentPage, 'haulage_page'),
            'illegal_shortages'    => $this->manualPaginate($data->illegal_shortages, $perPage, $currentPage, 'shortage_page'),
            'breathaliser_tests'   => $this->manualPaginate($data->breathaliser_tests, $perPage, $currentPage, 'breath_page'),
            'driver_misconducts'   => $this->manualPaginate($data->driver_misconducts, $perPage, $currentPage, 'misconduct_page'),
            'goods_inspections'    => $this->manualPaginate($data->goods_inspections, $perPage, $currentPage, 'goods_page'),
        ])->reject(fn($collection) => $collection === null || $collection->isEmpty());
        // return $incidents;
        return view($this->_directory . '.show', compact('data', 'incidents'));
    }

    private function manualPaginate($collection, $perPage, $currentPage, $pageName)
    {
        if ($collection === null || $collection->isEmpty()) {
            return $collection;
        }

        $page = request()->get($pageName, 1);
        $items = $collection->forPage($page, $perPage);

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $collection->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'pageName' => $pageName]
        );
    }

    public function store(WaybillRequest $request)
    {
        try {
            $waybill = $this->_repo->store(WaybillDto::fromRequest($request->validated()));
            $waybill->loadMissing('vehicle');
            $warnings = (array) $request->attributes->get('waybill_driver_warnings', []);

            if ($request->hasFile('document')) {
                $uploaded = $waybill->uploadFile($request->file('document'), 'waybills/documents/');
                $waybill->files()->create([
                    'type'   => 'document',
                    'name'   => $uploaded['name'],
                    'path'   => $uploaded['path'],
                    'status' => 1,
                ]);
            }

            $routeSync = null;
            try {
                $routeSync = $this->syncWialonRouteForWaybill($waybill);
            } catch (\Throwable $syncEx) {
                $routeSync = ['success' => false, 'unit_assigned' => false, 'error' => $syncEx->getMessage()];
                \Illuminate\Support\Facades\Log::warning('Wialon route sync failed during waybill store', [
                    'waybill_id' => $waybill->id,
                    'error' => $syncEx->getMessage(),
                ]);
            }

            try {
                $this->incidentWaybillNotificationService->notifyForWaybill($waybill);
            } catch (\Throwable $notifyException) {
                \Illuminate\Support\Facades\Log::warning('Waybill notification failed after creation', [
                    'waybill_id' => $waybill->id,
                    'message'    => $notifyException->getMessage(),
                ]);
            }

            $redirect = redirect()->route($this->_route . '.index');
            if (($routeSync['unit_assigned'] ?? true) !== true) {
                $warnings[] = 'The vehicle could not be assigned to the Wialon route: ' . ($routeSync['error'] ?? 'Unknown Wialon sync error.');
            }

            if ($warnings !== []) {
                return $redirect->with('warning', 'Waybill saved. ' . implode(' ', array_unique($warnings)));
            }

            return $redirect->with('success', __('language.successfully_created'));
        } catch (\Throwable $th) {
            if ($th instanceof ValidationException) {
                throw $th;
            }
            \Illuminate\Support\Facades\Log::error('Waybill store exception', [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);
            return redirect()->route($this->_route . '.index')->with('error', __('language.something_went_wrong'));
        }
    }

    public function update(WaybillRequest $request, $id)
    {
        try {
            $this->_repo->update($id, WaybillDto::fromRequest($request->validated()));
            $waybill = $this->_repo->show($id);
            $waybill->loadMissing('vehicle');
            $warnings = (array) $request->attributes->get('waybill_driver_warnings', []);

            if ($request->hasFile('document')) {
                $uploaded = $waybill->uploadFile($request->file('document'), 'waybills/documents/');

                $existing = $waybill->files()->where('type', 'document')->first();
                if ($existing && !empty($existing->path)) {
                    $waybill->deleteFile($existing->path);
                    $existing->update([
                        'name' => $uploaded['name'],
                        'path' => $uploaded['path'],
                    ]);
                } else {
                    $waybill->files()->create([
                        'type'   => 'document',
                        'name'   => $uploaded['name'],
                        'path'   => $uploaded['path'],
                        'status' => 1,
                    ]);
                }
            }

            $routeSync = null;
            try {
                $routeSync = $this->syncWialonRouteForWaybill($waybill);
            } catch (\Throwable $syncEx) {
                $routeSync = ['success' => false, 'unit_assigned' => false, 'error' => $syncEx->getMessage()];
                \Illuminate\Support\Facades\Log::warning('Wialon route sync failed during waybill update', [
                    'waybill_id' => $waybill->id,
                    'error' => $syncEx->getMessage(),
                ]);
            }

            $redirect = redirect()->route($this->_route . '.index');
            if (($routeSync['unit_assigned'] ?? true) !== true) {
                $warnings[] = 'The vehicle could not be assigned to the Wialon route: ' . ($routeSync['error'] ?? 'Unknown Wialon sync error.');
            }

            if ($warnings !== []) {
                return $redirect->with('warning', 'Waybill saved. ' . implode(' ', array_unique($warnings)));
            }

            return $redirect->with('success', __('language.updated_successfully'));
        } catch (\Throwable $th) {
            if ($th instanceof ValidationException) {
                throw $th;
            }
            if ($th instanceof NotFoundHttpException) {
                return redirect()->route($this->_route . '.index')->with('error', $th->getMessage());
            }
            \Illuminate\Support\Facades\Log::error('Waybill update exception', [
                'waybill_id' => $id,
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);
            return redirect()->route($this->_route . '.index')->with('error', __('language.something_went_wrong'));
        }
    }

    public function destroy($id)
    {
        try {
            $waybill = $this->_repo->show($id);   // fetched BEFORE delete, so wialon_route_id is still available
            $this->_repo->destroy($id);            // DB delete happens

            if (!empty($waybill->wialon_route_id)) {
                try {
                    $wialonService = app(\App\Services\WialonService::class);
                    $resourceId = $wialonService->getWialonResourceId();
                    $wialonService->deleteWialonRoute($resourceId, (int) $waybill->wialon_route_id);
                } catch (\Throwable $syncEx) {
                    \Illuminate\Support\Facades\Log::warning('Wialon route delete failed during waybill destroy', [
                        'waybill_id' => $id,
                        'error' => $syncEx->getMessage(),
                    ]);
                }
            }

            return redirect()->route($this->_route . '.index')->with('success', __('language.deleted_successfully'));
        } catch (\Throwable $th) {
            if ($th instanceof NotFoundHttpException) {
                return redirect()->route($this->_route . '.index')->with('error', $th->getMessage());
            }

            \Illuminate\Support\Facades\Log::error('Waybill destroy exception', [
                'waybill_id' => $id,
                'error' => $th->getMessage(),
            ]);

            return redirect()->route($this->_route . '.index')->with('error', __('language.something_went_wrong'));
        }
    }

    protected function syncWialonRouteForWaybill($waybill): ?array
    {
        if (empty($waybill->vehicle) || empty($waybill->vehicle->gps_unit_id)) {
            return null;
        }

        $wialonService = app(\App\Services\WialonService::class);

        $waybillData = [
            'waybill_number'   => $waybill->waybill_number,
            'origin'           => $waybill->origin,
            'origin_lat'       => $waybill->origin_lat,
            'origin_lng'       => $waybill->origin_lng,
            'destination'      => $waybill->destination,
            'destination_lat'  => $waybill->destination_lat,
            'destination_lng'  => $waybill->destination_lng,
            'checkpoints'      => $waybill->checkpoints,
            'wialon_route_id'  => $waybill->wialon_route_id,
            'gps_imei'         => $waybill->vehicle->gps_imei,
        ];

        $result = $wialonService->syncWaybillRoute($waybill->vehicle->gps_unit_id, $waybillData);

        if (!empty($result['wialon_route_id'])) {
            $waybill->wialon_route_id = $result['wialon_route_id'];
            $waybill->save();
        }

        return $result;
    }

    public function edit($id)
    {
        $data = $this->_repo->show($id);

        if ($data == null) {
            abort(404);
        }

        $vehicles    = app()->make('App\Interfaces\VehicleInterface')->all();
        $drivers     = app()->make('App\Interfaces\DriverDetailInterface')->all();
        $route_trips = app()->make('App\Interfaces\RouteTripInterface')->all();

        return view($this->_directory . '.edit', compact('data', 'vehicles', 'drivers', 'route_trips'));
    }

    public function getVehicleDrivers($vehicleId)
    {
        $vehicle = app()->make('App\Interfaces\VehicleInterface')->show($vehicleId);
        if (!$vehicle) {
            return response()->json([]);
        }

        $drivers = app()->make('App\Interfaces\DriverDetailInterface')->all()->map(fn($d) => [
            'id' => $d->id,
            'name' => $d->full_name,
            'assigned' => false,
        ]);
        return response()->json($drivers);
    }


    public function getVehicleRoute($vehicleId)
    {
        $vehicle = Vehicle::find($vehicleId);

        if (!$vehicle) {
            return response()->json([
                'success' => false,
                'message' => 'Vehicle not found.',
            ], 404);
        }

        if (empty($vehicle->gps_unit_id)) {
            return response()->json([
                'success' => false,
                'message' => 'This vehicle is not linked to a Wialon unit. Please enter the route manually.',
            ]);
        }

        if (!empty($vehicle->gps_provider) && strtolower($vehicle->gps_provider) !== 'wialon') {
            return response()->json([
                'success' => false,
                'message' => 'This vehicle is not configured to use Wialon GPS. Please enter the route manually.',
            ]);
        }

        try {
            $routeData = $this->wialonService->getVehicleRouteData(
                $vehicle->gps_unit_id,
                $vehicle->gps_imei,
                [
                    'laravel_vehicle_id' => (string) $vehicle->id,
                    'fleet_no' => $vehicle->fleet_no,
                ],
            );

            if (empty($routeData['route'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'No Wialon route is assigned to this vehicle. Please enter the route manually.',
                    'vehicle_position' => $routeData['unit_position'] ?? null,
                ]);
            }

            return response()->json([
                'success' => true,
                'route_assigned' => true,
                'message' => empty($routeData['has_sufficient_geographic_checkpoints'])
                    ? 'Route is assigned to this vehicle, but Wialon returned fewer than two geographic checkpoints.'
                    : null,
                'vehicle' => [
                    'id' => (string) $vehicle->id,
                    'fleet_no' => $vehicle->fleet_no,
                    'truck_no' => $vehicle->truck_no,
                    'gps_unit_id' => (string) $vehicle->gps_unit_id,
                    'gps_imei' => $vehicle->gps_imei,
                    'wialon_unit_id' => $routeData['unit']['id'] ?? null,
                    'url' => route('map'),
                ],
                'route' => $routeData['route'],
                'checkpoints' => $routeData['checkpoints'],
                'vehicle_position' => $routeData['unit_position'],
                'route_coordinates' => $routeData['route_coordinates'],
                'has_sufficient_geographic_checkpoints' => $routeData['has_sufficient_geographic_checkpoints'],
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Failed to fetch Wialon route for vehicle', [
                'vehicle_id' => $vehicleId,
                'gps_unit_id' => $vehicle->gps_unit_id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve Wialon route at this time. Please enter the route manually.',
            ]);
        }
    }

    public function updateStatus(Request $request, Waybill $waybill)
    {
        $validated = $request->validate([
            'status'            => ['required'],
            'despatch_time'     => ['nullable', 'date'],
            'delivery_document' => ['nullable', 'file', 'max:10240'],
        ]);

        $statusInput = $validated['status'];
        $statusValue = null;

        if (is_numeric($statusInput)) {
            $statusValue = (int) $statusInput;
        } else {
            $statusValue = match ((string) $statusInput) {
                'pending', 'awaiting' => 0,
                'allocated'           => 1,
                'despatch', 'dispatch', 'in_transit' => 2,
                'return_transit'      => 5,
                'delivered'           => 3,
                'cancelled', 'canceled' => 4,
                default               => null,
            };
        }

        if ($statusValue === null) {
            return back()->with('error', 'Invalid status selected.');
        }

        // Get the Status record by value
        $status = \App\Models\Status::firstWhere('value', $statusValue);

        if (!$status) {
            $status = \App\Models\Status::create([
                'value'       => $statusValue,
                'name'        => is_string($statusInput) ? ucfirst($statusInput) : "Status {$statusValue}",
                'badge_label' => is_string($statusInput) ? ucfirst($statusInput) : "Status {$statusValue}",
                'badge_class' => 'bg-secondary',
            ]);
        }

        // Check if waybill has unresolved incidents requiring client director approval
        $activeIncidents = $this->getIncidentStatus($waybill);
        $isClientDirector = auth()->user()->hasRole(\App\Constants\Constants::CLIENT_DIRECTORS);
        if (!empty($activeIncidents) && !$isClientDirector) {
            $incidentList = implode(', ', $activeIncidents);
            return back()->with('error', "Cannot change waybill status. Active incident intervention requires Client Director approval. Active incidents: {$incidentList}.");
        }

        $oldStatusLabel = $waybill->status?->badge_label ?? 'Unknown';

        \Illuminate\Support\Facades\DB::transaction(function () use ($waybill, $status, $statusValue): void {
            $this->_repo->lockVehicleForWaybill(
                (string) $waybill->vehicle_id,
                (string) $waybill->id,
                in_array($statusValue, Waybill::ACTIVE_STATUS_VALUES, true),
            );

            $lockedWaybill = Waybill::query()->lockForUpdate()->findOrFail($waybill->id);
            $lockedWaybill->status_id = $status->id;
            $lockedWaybill->save();
        });
        $waybill->refresh();

        if ($request->hasFile('delivery_document')) {
            $uploaded = $waybill->uploadFile($request->file('delivery_document'), 'waybills/pod/');

            $existing = $waybill->files()->whereIn('type', ['pod', 'delivery_document'])->first();
            if ($existing && !empty($existing->path)) {
                $waybill->deleteFile($existing->path);
            }

            $waybill->files()->updateOrCreate(
                ['type' => 'pod'],
                [
                    'name'   => $uploaded['name'],
                    'path'   => $uploaded['path'],
                    'status' => 1,
                ]
            );
        }

        // Send DB notification to Admin, SuperAdmin, Champs, Client Management
        try {
            $waybill->loadMissing('vehicle');
            $newStatusLabel = $waybill->fresh()->status?->badge_label ?? $status->badge_label ?? 'Unknown';
            $showUrl        = route('waybill.show', $waybill->id);
            $notification   = new WaybillStatusChangedNotification($waybill, $oldStatusLabel, $newStatusLabel, $showUrl);

            $recipients = User::role([
                \App\Constants\Constants::ADMIN,
                \App\Constants\Constants::SUPERADMIN,
                \App\Constants\Constants::CHAMPS_MANAGEMENT,
                \App\Constants\Constants::CLIENT_MANAGEMENT,
            ])->get()->unique('id');

            foreach ($recipients as $recipient) {
                $recipient->notifyNow($notification, ['database']);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('WaybillStatusChanged DB notification failed', [
                'waybill_id' => $waybill->id,
                'error'      => $e->getMessage(),
            ]);
        }

        return back()->with('success', 'Waybill status updated.');
    }
    /**
     * All incident types with their resolved status values.
     * Key   = human-readable label
     * Value = [ModelClass, [resolved_status_values]]
     */
    private function incidentTypeMap(): array
    {
        return [
            'Impoundment'       => [Impoundment::class,       ['1']],       // 1 = Released
            'Accident'          => [Accident::class,           ['1']],       // 1 = Resolved
            'Vehicle Breakdown' => [VehicleBreakdown::class,   ['1']],       // 1 = Resolved
            'Vehicle Bogged Down' => [VehicleBoggedDown::class, ['1']],      // 1 = Recovered
            'Illegal Detention' => [IllegalDetention::class,   ['1']],       // 1 = Released
            'Illegal Haulage'   => [IllegalHaulage::class,     ['2']],       // 2 = Resolved (1 = Reviewed)
            'Driver Misconduct' => [DriverMisconduct::class,   ['2']],       // 2 = Resolved (1 = Reviewed)
            'Breathaliser Test' => [BreathaliserTest::class,   ['2']],       // 2 = Closed   (1 = Reviewed)
            'Route Diversion'   => [RouteDiversion::class,     ['1', '2']], // 1 = Completed, 2 = Cancelled
        ];
    }

    /**
     * Check if waybill has any unresolved incidents (by waybill_id or vehicle_id)
     */
    private function hasUnresolvedIncidents(Waybill $waybill): bool
    {
        foreach ($this->incidentTypeMap() as [$model, $resolvedStatuses]) {
            $query = $model::whereNotIn('status', $resolvedStatuses);

            if ((clone $query)->where('waybill_id', $waybill->id)->exists()) {
                return true;
            }

            if (!empty($waybill->vehicle_id) &&
                (clone $query)->where('vehicle_id', $waybill->vehicle_id)->exists()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get list of unresolved incident type labels for a waybill (for UI display)
     */
    public function getIncidentStatus(Waybill $waybill): array
    {
        $active = [];

        foreach ($this->incidentTypeMap() as $label => [$model, $resolvedStatuses]) {
            $query = $model::whereNotIn('status', $resolvedStatuses);

            $found = (clone $query)->where('waybill_id', $waybill->id)->exists();

            if (!$found && !empty($waybill->vehicle_id)) {
                $found = (clone $query)->where('vehicle_id', $waybill->vehicle_id)->exists();
            }

            if ($found) {
                $active[] = $label;
            }
        }

        return $active;
    }

    /**
     * AJAX: Check if waybill has active incidents (for UI warning)
     */
    public function checkIncidentStatus(Waybill $waybill)
    {
        $activeIncidents = $this->getIncidentStatus($waybill);
        $isClientDirector = auth()->user()->hasRole(\App\Constants\Constants::CLIENT_DIRECTORS);

        return response()->json([
            'has_incidents'     => !empty($activeIncidents),
            'incidents'         => $activeIncidents,
            'is_client_director' => $isClientDirector,
            'can_update'        => empty($activeIncidents) || $isClientDirector,
        ]);
    }

    /**
     * AJAX: Get in-transit waybills for a vehicle, with route and trip info
     */
    public function getInTransitWaybills($vehicleId)
    {
        // Assuming 'in_transit' status is 2 (as per updateStatus method)
        $waybills = \App\Models\Waybill::where('vehicle_id', $vehicleId)
            ->whereHas('status', function($q) {
                $q->where('value', 2); // 2 = in_transit
            })
            ->with(['route_trip', 'vehicle'])
            ->get();

        $result = $waybills->map(function($wb) {
            return [
                'id' => $wb->id,
                'waybill_number' => $wb->waybill_number ?? $wb->id,
                'fleet_no' => $wb->vehicle?->fleet_no ?? '',
                'truck_no' => $wb->vehicle?->truck_no ?? '',
                'route_trip' => $wb->route_trip?->trip_name ?? '',
                'origin' => $wb->origin ?? '',
                'destination' => $wb->destination ?? '',
            ];
        });

        return response()->json(['waybills' => $result]);
    }
}
