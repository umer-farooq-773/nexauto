<?php

namespace App\Services;

use App\Models\Accident;
use App\Models\BreathaliserTest;
use App\Models\DriverMisconduct;
use App\Models\IllegalDetention;
use App\Models\IllegalHaulage;
use App\Models\IllegalShortage;
use App\Models\Impoundment;
use App\Models\RouteDiversion;
use App\Models\Vehicle;
use App\Models\VehicleBoggedDown;
use App\Models\VehicleBreakdown;
use App\Models\Waybill;
use App\Models\GoodsInspection;
use Illuminate\Support\Collection;

class IncidentTrackingService
{
    /**
     * Known geographic coordinates for Pakistani and regional cities/hubs.
     */
    protected array $knownLocations = [
        'islamabad'     => [33.6844, 73.0479],
        'rawalpindi'    => [33.5651, 73.0169],
        'karachi'       => [24.8607, 67.0011],
        'lahore'        => [31.5204, 74.3587],
        'mianwali'      => [32.5839, 71.5370],
        'faisalabad'    => [31.4504, 73.1350],
        'taxila'        => [33.7463, 72.8397],
        'balochistan'   => [28.4907, 65.0945],
        'sawabi'        => [34.1202, 72.4705],
        'swabi'         => [34.1202, 72.4705],
        'okara'         => [30.8080, 73.4458],
        'mandi ahmedabad'=> [30.6535, 74.0299],
        'fateh jang'    => [33.5663, 72.6456],
        'gulyana'       => [33.2027, 73.2697],
        'lakki marwat'  => [32.6056, 70.9113],
        'hasan abdal'   => [33.8200, 72.6800],
        'kharmang'      => [35.1500, 76.1000],
        'kindarik'      => [35.1500, 76.1000],
        'peshawar'      => [34.0151, 71.5249],
        'quetta'        => [30.1798, 66.9750],
        'multan'        => [30.1575, 71.5249],
        'sahiwal'       => [30.6682, 73.1114],
        'sultanabad'    => [24.8435, 67.0205],
        'gujranwala'    => [32.1877, 74.1945],
        'hyderabad'     => [25.3960, 68.3578],
        'sukkur'        => [27.7052, 68.8574],
        'bahawalpur'    => [29.3544, 71.6911],
        'sialkot'       => [32.4945, 74.5229],
        'abbottabad'    => [34.1688, 73.2215],
        'jehlum'        => [32.9405, 73.7276],
        'nepal'         => [27.7172, 85.3240],
        'qatar'         => [25.2854, 51.5310],
        'al rayyan'     => [25.2854, 51.5310],
        'umama'         => [25.2854, 51.5310],
    ];

    /**
     * Get all aggregated incidents and driver behaviors formatted for the live tracking map.
     *
     * @param array $filters
     * @return array
     */
    public function getAllMapIncidents(array $filters = []): array
    {
        $incidents = [];

        $relationsBasic = ['waybill.driver', 'waybill.vehicle', 'vehicle.drivers'];
        $relationsWithDriver = ['waybill.driver', 'waybill.vehicle', 'vehicle.drivers', 'driver'];

        // 1. Accidents
        $accidents = Accident::with($relationsBasic)->latest()->get();
        foreach ($accidents as $item) {
            $resolvedWaybillId = $item->waybill_id ?: $item->waybill?->id;
            $resolvedVehicleId = $item->vehicle_id ?: ($item->waybill?->vehicle_id ?: $item->waybill?->vehicle?->id);
            $driverName = $this->resolveDriverName($item);
            $fleetNo    = $this->resolveFleetNo($item);
            $truckNo    = $this->resolveTruckNo($item);
            $waybillNo  = $item->waybill?->waybill_number;

            $coords = $this->resolveCoordinates(
                $item->accident_location_lat,
                $item->accident_location_lng,
                $item->accident_location,
                $item->waybill,
                $item->vehicle
            );

            $statusLabel = match ((string) $item->status) {
                '1' => 'Resolved',
                '2' => 'In Progress',
                default => 'Pending',
            };
            $statusClass = match ((string) $item->status) {
                '1' => 'status-resolved',
                '2' => 'status-in_progress',
                default => 'status-pending',
            };
            $statusIcon = match ((string) $item->status) {
                '1' => 'fa-solid fa-circle-check',
                '2' => 'fa-solid fa-clock-rotate-left',
                default => 'fa-solid fa-triangle-exclamation',
            };

            $incidents[] = [
                'id'                 => (string) $item->id,
                'category'           => 'incident',
                'category_label'     => 'Incident Intervention',
                'type_key'           => 'accident',
                'type_label'         => 'Accident',
                'icon'               => 'fa-solid fa-car-burst',
                'color'              => '#ef4444',
                'badge_class'        => 'bg-danger',
                'date'               => $item->accident_date?->format('d M Y') ?? ($item->created_at?->format('d M Y') ?? '-'),
                'raw_date'           => $item->accident_date?->format('Y-m-d') ?? ($item->created_at?->format('Y-m-d') ?? ''),
                'location'           => $item->accident_location ?? 'N/A',
                'lat'                => $coords[0],
                'lng'                => $coords[1],
                'waybill_id'         => $resolvedWaybillId,
                'waybill_number'     => $waybillNo,
                'waybill_url'        => $resolvedWaybillId ? route('waybill.show', $resolvedWaybillId) : null,
                'waybill_map_url'    => $resolvedWaybillId ? route('waybill.waybill_map_tracking', $resolvedWaybillId) : null,
                'vehicle_id'         => $resolvedVehicleId,
                'fleet_no'           => $fleetNo,
                'truck_no'           => $truckNo,
                'driver_name'        => $driverName,
                'detail_type'        => $item->accident_type ?? 'Accident',
                'detail_extra'       => $item->accident_type_detail,
                'description'        => $item->description ?? $item->content ?? '-',
                'damage_cost'        => $item->damage_cost ? number_format((float) $item->damage_cost, 2) : null,
                'damage_cost_raw'    => (float) ($item->damage_cost ?? 0),
                'fatality'           => $item->is_fatality ? ($item->fatality_type ?: 'Yes') : null,
                'status_raw'         => (string) $item->status,
                'status_label'       => $statusLabel,
                'status_pill_class'  => $statusClass,
                'status_icon'        => $statusIcon,
                'status_color'       => match ((string) $item->status) {
                    '1' => 'success',
                    '2' => 'warning',
                    default => 'danger',
                },
                'show_url'           => route('accident.show', $item->id),
            ];
        }

        // 2. Vehicle Breakdowns
        $breakdowns = VehicleBreakdown::with($relationsBasic)->latest()->get();
        foreach ($breakdowns as $item) {
            $resolvedWaybillId = $item->waybill_id ?: $item->waybill?->id;
            $resolvedVehicleId = $item->vehicle_id ?: ($item->waybill?->vehicle_id ?: $item->waybill?->vehicle?->id);
            $driverName = $this->resolveDriverName($item);
            $fleetNo    = $this->resolveFleetNo($item);
            $truckNo    = $this->resolveTruckNo($item);
            $waybillNo  = $item->waybill?->waybill_number;

            $coords = $this->resolveCoordinates(
                $item->breakdown_location_lat,
                $item->breakdown_location_lng,
                $item->breakdown_location,
                $item->waybill,
                $item->vehicle
            );

            $statusLabel = match ((string) $item->status) {
                '1' => 'Resolved',
                '2' => 'In Progress',
                default => 'Pending',
            };
            $statusClass = match ((string) $item->status) {
                '1' => 'status-resolved',
                '2' => 'status-in_progress',
                default => 'status-pending',
            };
            $statusIcon = match ((string) $item->status) {
                '1' => 'fa-solid fa-circle-check',
                '2' => 'fa-solid fa-clock-rotate-left',
                default => 'fa-solid fa-wrench',
            };

            $incidents[] = [
                'id'                 => (string) $item->id,
                'category'           => 'incident',
                'category_label'     => 'Incident Intervention',
                'type_key'           => 'vehicle_breakdown',
                'type_label'         => 'Vehicle Breakdown',
                'icon'               => 'fa-solid fa-wrench',
                'color'              => '#f97316',
                'badge_class'        => 'bg-warning text-dark',
                'date'               => $item->breakdown_date?->format('d M Y') ?? ($item->created_at?->format('d M Y') ?? '-'),
                'raw_date'           => $item->breakdown_date?->format('Y-m-d') ?? ($item->created_at?->format('Y-m-d') ?? ''),
                'location'           => $item->breakdown_location ?? 'N/A',
                'lat'                => $coords[0],
                'lng'                => $coords[1],
                'waybill_id'         => $resolvedWaybillId,
                'waybill_number'     => $waybillNo,
                'waybill_url'        => $resolvedWaybillId ? route('waybill.show', $resolvedWaybillId) : null,
                'waybill_map_url'    => $resolvedWaybillId ? route('waybill.waybill_map_tracking', $resolvedWaybillId) : null,
                'vehicle_id'         => $resolvedVehicleId,
                'fleet_no'           => $fleetNo,
                'truck_no'           => $truckNo,
                'driver_name'        => $driverName,
                'detail_type'        => $item->breakdown_type ?? 'Breakdown',
                'detail_extra'       => null,
                'description'        => $item->description ?? '-',
                'damage_cost'        => $item->repair_cost ? number_format((float) $item->repair_cost, 2) : null,
                'damage_cost_raw'    => (float) ($item->repair_cost ?? 0),
                'fatality'           => null,
                'status_raw'         => (string) $item->status,
                'status_label'       => $statusLabel,
                'status_pill_class'  => $statusClass,
                'status_icon'        => $statusIcon,
                'status_color'       => match ((string) $item->status) {
                    '1' => 'success',
                    '2' => 'warning',
                    default => 'danger',
                },
                'show_url'           => route('vehicle_breakdown.show', $item->id),
            ];
        }

        // 3. Vehicle Bogged Down
        $bogged = VehicleBoggedDown::with($relationsBasic)->latest()->get();
        foreach ($bogged as $item) {
            $resolvedWaybillId = $item->waybill_id ?: $item->waybill?->id;
            $resolvedVehicleId = $item->vehicle_id ?: ($item->waybill?->vehicle_id ?: $item->waybill?->vehicle?->id);
            $driverName = $this->resolveDriverName($item);
            $fleetNo    = $this->resolveFleetNo($item);
            $truckNo    = $this->resolveTruckNo($item);
            $waybillNo  = $item->waybill?->waybill_number;

            $coords = $this->resolveCoordinates(
                $item->stuck_location_lat,
                $item->stuck_location_lng,
                $item->stuck_location,
                $item->waybill,
                $item->vehicle
            );

            $statusLabel = match ((string) $item->status) {
                '1' => 'Recovered',
                '2' => 'Recovery In Progress',
                default => 'Stuck / Pending',
            };
            $statusClass = match ((string) $item->status) {
                '1' => 'status-recovered',
                '2' => 'status-in_progress',
                default => 'status-stuck',
            };
            $statusIcon = match ((string) $item->status) {
                '1' => 'fa-solid fa-circle-check',
                '2' => 'fa-solid fa-clock-rotate-left',
                default => 'fa-solid fa-truck-monster',
            };

            $incidents[] = [
                'id'                 => (string) $item->id,
                'category'           => 'incident',
                'category_label'     => 'Incident Intervention',
                'type_key'           => 'vehicle_bogged_down',
                'type_label'         => 'Bogged Down',
                'icon'               => 'fa-solid fa-truck-monster',
                'color'              => '#d97706',
                'badge_class'        => 'bg-warning text-dark',
                'date'               => $item->bogged_date?->format('d M Y') ?? ($item->created_at?->format('d M Y') ?? '-'),
                'raw_date'           => $item->bogged_date?->format('Y-m-d') ?? ($item->created_at?->format('Y-m-d') ?? ''),
                'location'           => $item->stuck_location ?? 'N/A',
                'lat'                => $coords[0],
                'lng'                => $coords[1],
                'waybill_id'         => $resolvedWaybillId,
                'waybill_number'     => $waybillNo,
                'waybill_url'        => $resolvedWaybillId ? route('waybill.show', $resolvedWaybillId) : null,
                'waybill_map_url'    => $resolvedWaybillId ? route('waybill.waybill_map_tracking', $resolvedWaybillId) : null,
                'vehicle_id'         => $resolvedVehicleId,
                'fleet_no'           => $fleetNo,
                'truck_no'           => $truckNo,
                'driver_name'        => $driverName,
                'detail_type'        => $item->ground_condition ?? 'Bogged Down',
                'detail_extra'       => null,
                'description'        => $item->description ?? '-',
                'damage_cost'        => $item->recovery_cost ? number_format((float) $item->recovery_cost, 2) : null,
                'damage_cost_raw'    => (float) ($item->recovery_cost ?? 0),
                'fatality'           => null,
                'status_raw'         => (string) $item->status,
                'status_label'       => $statusLabel,
                'status_pill_class'  => $statusClass,
                'status_icon'        => $statusIcon,
                'status_color'       => match ((string) $item->status) {
                    '1' => 'success',
                    '2' => 'warning',
                    default => 'danger',
                },
                'show_url'           => route('vehicle_bogged_down.show', $item->id),
            ];
        }

        // 4. Impoundments
        $impoundments = Impoundment::with($relationsBasic)->latest()->get();
        foreach ($impoundments as $item) {
            $resolvedWaybillId = $item->waybill_id ?: $item->waybill?->id;
            $resolvedVehicleId = $item->vehicle_id ?: ($item->waybill?->vehicle_id ?: $item->waybill?->vehicle?->id);
            $driverName = $this->resolveDriverName($item);
            $fleetNo    = $this->resolveFleetNo($item);
            $truckNo    = $this->resolveTruckNo($item);
            $waybillNo  = $item->waybill?->waybill_number;

            $coords = $this->resolveCoordinates(
                $item->impound_location_lat,
                $item->impound_location_lng,
                $item->impound_location,
                $item->waybill,
                $item->vehicle
            );

            $statusLabel = $item->status == '1' ? 'Released' : 'Impounded';
            $statusClass = $item->status == '1' ? 'status-released' : 'status-impounded';
            $statusIcon  = $item->status == '1' ? 'fa-solid fa-lock-open' : 'fa-solid fa-lock';

            $incidents[] = [
                'id'                 => (string) $item->id,
                'category'           => 'incident',
                'category_label'     => 'Incident Intervention',
                'type_key'           => 'impoundment',
                'type_label'         => 'Impoundment',
                'icon'               => 'fa-solid fa-building-shield',
                'color'              => '#8b5cf6',
                'badge_class'        => 'bg-purple text-white',
                'date'               => $item->impound_date?->format('d M Y') ?? ($item->created_at?->format('d M Y') ?? '-'),
                'raw_date'           => $item->impound_date?->format('Y-m-d') ?? ($item->created_at?->format('Y-m-d') ?? ''),
                'location'           => $item->impound_location ?? 'N/A',
                'lat'                => $coords[0],
                'lng'                => $coords[1],
                'waybill_id'         => $resolvedWaybillId,
                'waybill_number'     => $waybillNo,
                'waybill_url'        => $resolvedWaybillId ? route('waybill.show', $resolvedWaybillId) : null,
                'waybill_map_url'    => $resolvedWaybillId ? route('waybill.waybill_map_tracking', $resolvedWaybillId) : null,
                'vehicle_id'         => $resolvedVehicleId,
                'fleet_no'           => $fleetNo,
                'truck_no'           => $truckNo,
                'driver_name'        => $driverName,
                'detail_type'        => $item->impound_authority ?? 'Impounded',
                'detail_extra'       => $item->release_date ? 'Released: ' . $item->release_date->format('d M Y') : null,
                'description'        => $item->reason ?? $item->remarks ?? '-',
                'damage_cost'        => $item->cost ? number_format((float) $item->cost, 2) : null,
                'damage_cost_raw'    => (float) ($item->cost ?? 0),
                'fatality'           => null,
                'status_raw'         => (string) $item->status,
                'status_label'       => $statusLabel,
                'status_pill_class'  => $statusClass,
                'status_icon'        => $statusIcon,
                'status_color'       => $item->status == '1' ? 'success' : 'danger',
                'show_url'           => route('impoundment.show', $item->id),
            ];
        }

        // 5. Illegal Detentions
        $detentions = IllegalDetention::with($relationsBasic)->latest()->get();
        foreach ($detentions as $item) {
            $resolvedWaybillId = $item->waybill_id ?: $item->waybill?->id;
            $resolvedVehicleId = $item->vehicle_id ?: ($item->waybill?->vehicle_id ?: $item->waybill?->vehicle?->id);
            $driverName = $this->resolveDriverName($item);
            $fleetNo    = $this->resolveFleetNo($item);
            $truckNo    = $this->resolveTruckNo($item);
            $waybillNo  = $item->waybill?->waybill_number;

            $coords = $this->resolveCoordinates(
                $item->detention_location_lat,
                $item->detention_location_lng,
                $item->detention_location,
                $item->waybill,
                $item->vehicle
            );

            $statusLabel = $item->status == '1' ? 'Released' : 'Detained';
            $statusClass = $item->status == '1' ? 'status-released' : 'status-detained';
            $statusIcon  = $item->status == '1' ? 'fa-solid fa-lock-open' : 'fa-solid fa-handcuffs';

            $incidents[] = [
                'id'                 => (string) $item->id,
                'category'           => 'incident',
                'category_label'     => 'Incident Intervention',
                'type_key'           => 'illegal_detention',
                'type_label'         => 'Illegal Detention',
                'icon'               => 'fa-solid fa-handcuffs',
                'color'              => '#dc2626',
                'badge_class'        => 'bg-danger',
                'date'               => $item->detention_date?->format('d M Y') ?? ($item->created_at?->format('d M Y') ?? '-'),
                'raw_date'           => $item->detention_date?->format('Y-m-d') ?? ($item->created_at?->format('Y-m-d') ?? ''),
                'location'           => $item->detention_location ?? 'N/A',
                'lat'                => $coords[0],
                'lng'                => $coords[1],
                'waybill_id'         => $resolvedWaybillId,
                'waybill_number'     => $waybillNo,
                'waybill_url'        => $resolvedWaybillId ? route('waybill.show', $resolvedWaybillId) : null,
                'waybill_map_url'    => $resolvedWaybillId ? route('waybill.waybill_map_tracking', $resolvedWaybillId) : null,
                'vehicle_id'         => $resolvedVehicleId,
                'fleet_no'           => $fleetNo,
                'truck_no'           => $truckNo,
                'driver_name'        => $driverName,
                'detail_type'        => $item->detained_by ?? 'Detained',
                'detail_extra'       => $item->release_date ? 'Released: ' . $item->release_date->format('d M Y') : null,
                'description'        => $item->reason ?? $item->remarks ?? '-',
                'damage_cost'        => $item->cost ? number_format((float) $item->cost, 2) : null,
                'damage_cost_raw'    => (float) ($item->cost ?? 0),
                'fatality'           => null,
                'status_raw'         => (string) $item->status,
                'status_label'       => $statusLabel,
                'status_pill_class'  => $statusClass,
                'status_icon'        => $statusIcon,
                'status_color'       => $item->status == '1' ? 'success' : 'danger',
                'show_url'           => route('illegal_detention.show', $item->id),
            ];
        }

        // 6. Route Diversions
        $diversions = RouteDiversion::with($relationsWithDriver)->latest()->get();
        foreach ($diversions as $item) {
            $resolvedWaybillId = $item->waybill_id ?: $item->waybill?->id;
            $resolvedVehicleId = $item->vehicle_id ?: ($item->waybill?->vehicle_id ?: $item->waybill?->vehicle?->id);
            $driverName = $this->resolveDriverName($item);
            $fleetNo    = $this->resolveFleetNo($item);
            $truckNo    = $this->resolveTruckNo($item);
            $waybillNo  = $item->waybill?->waybill_number;

            $coords = $this->resolveCoordinates(
                $item->diversion_location_lat,
                $item->diversion_location_lng,
                $item->diversion_location,
                $item->waybill,
                $item->vehicle
            );

            $statusLabel = match ((string) $item->status) {
                '1' => 'Completed',
                '2' => 'Cancelled',
                default => 'Active Diversion',
            };
            $statusClass = match ((string) $item->status) {
                '1' => 'status-completed',
                '2' => 'status-cancelled',
                default => 'status-diversion',
            };
            $statusIcon = match ((string) $item->status) {
                '1' => 'fa-solid fa-circle-check',
                '2' => 'fa-solid fa-ban',
                default => 'fa-solid fa-route',
            };

            $incidents[] = [
                'id'                 => (string) $item->id,
                'category'           => 'driver_behavior',
                'category_label'     => 'Driver Behavior',
                'type_key'           => 'route_diversion',
                'type_label'         => 'Route Diversion',
                'icon'               => 'fa-solid fa-route',
                'color'              => '#0284c7',
                'badge_class'        => 'bg-info text-white',
                'date'               => $item->diversion_date?->format('d M Y') ?? ($item->created_at?->format('d M Y') ?? '-'),
                'raw_date'           => $item->diversion_date?->format('Y-m-d') ?? ($item->created_at?->format('Y-m-d') ?? ''),
                'location'           => $item->diversion_location ?? 'N/A',
                'lat'                => $coords[0],
                'lng'                => $coords[1],
                'waybill_id'         => $resolvedWaybillId,
                'waybill_number'     => $waybillNo,
                'waybill_url'        => $resolvedWaybillId ? route('waybill.show', $resolvedWaybillId) : null,
                'waybill_map_url'    => $resolvedWaybillId ? route('waybill.waybill_map_tracking', $resolvedWaybillId) : null,
                'vehicle_id'         => $resolvedVehicleId,
                'fleet_no'           => $fleetNo,
                'truck_no'           => $truckNo,
                'driver_name'        => $driverName,
                'detail_type'        => 'Diverted: ' . ($item->diverted_route ?? 'Unauthorized Route'),
                'detail_extra'       => 'Original: ' . ($item->original_route ?? '-'),
                'description'        => $item->diversion_reason ?? $item->remarks ?? '-',
                'damage_cost'        => null,
                'damage_cost_raw'    => 0,
                'fatality'           => null,
                'status_raw'         => (string) $item->status,
                'status_label'       => $statusLabel,
                'status_pill_class'  => $statusClass,
                'status_icon'        => $statusIcon,
                'status_color'       => match ((string) $item->status) {
                    '1' => 'success',
                    '2' => 'secondary',
                    default => 'warning',
                },
                'show_url'           => route('route_diversion.show', $item->id),
            ];
        }

        // 7. Illegal Haulage
        $haulages = IllegalHaulage::with($relationsWithDriver)->latest()->get();
        foreach ($haulages as $item) {
            $resolvedWaybillId = $item->waybill_id ?: $item->waybill?->id;
            $resolvedVehicleId = $item->vehicle_id ?: ($item->waybill?->vehicle_id ?: $item->waybill?->vehicle?->id);
            $driverName = $this->resolveDriverName($item);
            $fleetNo    = $this->resolveFleetNo($item);
            $truckNo    = $this->resolveTruckNo($item);
            $waybillNo  = $item->waybill?->waybill_number;

            $coords = $this->resolveCoordinates(
                $item->haulage_location_lat,
                $item->haulage_location_lng,
                $item->haulage_location,
                $item->waybill,
                $item->vehicle
            );

            $statusLabel = match ((string) $item->status) {
                '1' => 'Reviewed',
                '2' => 'Resolved',
                default => 'Open',
            };
            $statusClass = match ((string) $item->status) {
                '1' => 'status-reviewed',
                '2' => 'status-resolved',
                default => 'status-open',
            };
            $statusIcon = match ((string) $item->status) {
                '1' => 'fa-solid fa-eye',
                '2' => 'fa-solid fa-circle-check',
                default => 'fa-solid fa-boxes-packing',
            };

            $incidents[] = [
                'id'                 => (string) $item->id,
                'category'           => 'driver_behavior',
                'category_label'     => 'Driver Behavior',
                'type_key'           => 'illegal_haulage',
                'type_label'         => 'Illegal Haulage',
                'icon'               => 'fa-solid fa-boxes-packing',
                'color'              => '#ea580c',
                'badge_class'        => 'bg-warning text-dark',
                'date'               => $item->haulage_date?->format('d M Y') ?? ($item->created_at?->format('d M Y') ?? '-'),
                'raw_date'           => $item->haulage_date?->format('Y-m-d') ?? ($item->created_at?->format('Y-m-d') ?? ''),
                'location'           => $item->haulage_location ?? 'N/A',
                'lat'                => $coords[0],
                'lng'                => $coords[1],
                'waybill_id'         => $resolvedWaybillId,
                'waybill_number'     => $waybillNo,
                'waybill_url'        => $resolvedWaybillId ? route('waybill.show', $resolvedWaybillId) : null,
                'waybill_map_url'    => $resolvedWaybillId ? route('waybill.waybill_map_tracking', $resolvedWaybillId) : null,
                'vehicle_id'         => $resolvedVehicleId,
                'fleet_no'           => $fleetNo,
                'truck_no'           => $truckNo,
                'driver_name'        => $driverName,
                'detail_type'        => $item->haulage_type ?? 'Illegal Haulage',
                'detail_extra'       => $item->severity ? 'Severity: ' . ucfirst($item->severity) : null,
                'description'        => $item->cargo_description ?? $item->remarks ?? $item->action_taken ?? '-',
                'damage_cost'        => null,
                'damage_cost_raw'    => 0,
                'fatality'           => null,
                'status_raw'         => (string) $item->status,
                'status_label'       => $statusLabel,
                'status_pill_class'  => $statusClass,
                'status_icon'        => $statusIcon,
                'status_color'       => match ((string) $item->status) {
                    '1' => 'warning',
                    '2' => 'success',
                    default => 'danger',
                },
                'show_url'           => route('illegal_haulage.show', $item->id),
            ];
        }

        // Illegal Shortage
        $shortages = IllegalShortage::with($relationsWithDriver)->latest()->get();
        foreach ($shortages as $item) {
            $resolvedWaybillId = $item->waybill_id ?: $item->waybill?->id;
            $resolvedVehicleId = $item->vehicle_id ?: ($item->waybill?->vehicle_id ?: $item->waybill?->vehicle?->id);
            $driverName = $this->resolveDriverName($item);
            $fleetNo    = $this->resolveFleetNo($item);
            $truckNo    = $this->resolveTruckNo($item);
            $waybillNo  = $item->waybill?->waybill_number;

            $coords = $this->resolveCoordinates(
                $item->shortage_location_lat,
                $item->shortage_location_lng,
                $item->shortage_location,
                $item->waybill,
                $item->vehicle
            );

            $statusLabel = match ((string) $item->status) {
                '1' => 'Reviewed',
                '2' => 'Resolved',
                default => 'Open',
            };
            $statusClass = match ((string) $item->status) {
                '1' => 'status-reviewed',
                '2' => 'status-resolved',
                default => 'status-open',
            };
            $statusIcon = match ((string) $item->status) {
                '1' => 'fa-solid fa-eye',
                '2' => 'fa-solid fa-circle-check',
                default => 'fa-solid fa-boxes-packing',
            };

            $incidents[] = [
                'id'                 => (string) $item->id,
                'category'           => 'driver_behavior',
                'category_label'     => 'Driver Behavior',
                'type_key'           => 'illegal_shortage',
                'type_label'         => 'Illegal Shortage',
                'icon'               => 'fa-solid fa-boxes-packing',
                'color'              => '#d97706',
                'badge_class'        => 'bg-warning text-dark',
                'date'               => $item->shortage_date?->format('d M Y') ?? ($item->created_at?->format('d M Y') ?? '-'),
                'raw_date'           => $item->shortage_date?->format('Y-m-d') ?? ($item->created_at?->format('Y-m-d') ?? ''),
                'location'           => $item->shortage_location ?? 'N/A',
                'lat'                => $coords[0],
                'lng'                => $coords[1],
                'waybill_id'         => $resolvedWaybillId,
                'waybill_number'     => $waybillNo,
                'waybill_url'        => $resolvedWaybillId ? route('waybill.show', $resolvedWaybillId) : null,
                'waybill_map_url'    => $resolvedWaybillId ? route('waybill.waybill_map_tracking', $resolvedWaybillId) : null,
                'vehicle_id'         => $resolvedVehicleId,
                'fleet_no'           => $fleetNo,
                'truck_no'           => $truckNo,
                'driver_name'        => $driverName,
                'detail_type'        => $item->shortage_type ?? 'Illegal Shortage',
                'detail_extra'       => ($item->shortage_quantity ? 'Qty: ' . $item->shortage_quantity . ' | ' : '') . ($item->severity ? 'Severity: ' . ucfirst($item->severity) : null),
                'description'        => $item->cargo_description ?? $item->remarks ?? $item->action_taken ?? '-',
                'damage_cost'        => null,
                'damage_cost_raw'    => 0,
                'fatality'           => null,
                'status_raw'         => (string) $item->status,
                'status_label'       => $statusLabel,
                'status_pill_class'  => $statusClass,
                'status_icon'        => $statusIcon,
                'status_color'       => match ((string) $item->status) {
                    '1' => 'warning',
                    '2' => 'success',
                    default => 'danger',
                },
                'show_url'           => route('illegal_shortage.show', $item->id),
            ];
        }

        // 8. Breathaliser Tests
        $tests = BreathaliserTest::with($relationsWithDriver)->latest()->get();
        foreach ($tests as $item) {
            $resolvedWaybillId = $item->waybill_id ?: $item->waybill?->id;
            $resolvedVehicleId = $item->vehicle_id ?: ($item->waybill?->vehicle_id ?: $item->waybill?->vehicle?->id);
            $driverName = $this->resolveDriverName($item);
            $fleetNo    = $this->resolveFleetNo($item);
            $truckNo    = $this->resolveTruckNo($item);
            $waybillNo  = $item->waybill?->waybill_number;

            $coords = $this->resolveCoordinates(
                $item->test_location_lat,
                $item->test_location_lng,
                $item->test_location,
                $item->waybill,
                $item->vehicle
            );

            $statusLabel = match ((string) $item->status) {
                '1' => 'Reviewed',
                '2' => 'Closed',
                default => 'Open',
            };
            $statusClass = match ((string) $item->status) {
                '1' => 'status-reviewed',
                '2' => 'status-closed',
                default => 'status-open',
            };
            $statusIcon = match ((string) $item->status) {
                '1' => 'fa-solid fa-eye',
                '2' => 'fa-solid fa-circle-check',
                default => 'fa-solid fa-wine-bottle',
            };

            $incidents[] = [
                'id'                 => (string) $item->id,
                'category'           => 'driver_behavior',
                'category_label'     => 'Driver Behavior',
                'type_key'           => 'breathaliser_test',
                'type_label'         => 'Breathaliser Test',
                'icon'               => 'fa-solid fa-wine-bottle',
                'color'              => '#e11d48',
                'badge_class'        => 'bg-danger',
                'date'               => $item->test_date?->format('d M Y') ?? ($item->created_at?->format('d M Y') ?? '-'),
                'raw_date'           => $item->test_date?->format('Y-m-d') ?? ($item->created_at?->format('Y-m-d') ?? ''),
                'location'           => $item->test_location ?? 'N/A',
                'lat'                => $coords[0],
                'lng'                => $coords[1],
                'waybill_id'         => $resolvedWaybillId,
                'waybill_number'     => $waybillNo,
                'waybill_url'        => $resolvedWaybillId ? route('waybill.show', $resolvedWaybillId) : null,
                'waybill_map_url'    => $resolvedWaybillId ? route('waybill.waybill_map_tracking', $resolvedWaybillId) : null,
                'vehicle_id'         => $resolvedVehicleId,
                'fleet_no'           => $fleetNo,
                'truck_no'           => $truckNo,
                'driver_name'        => $driverName,
                'detail_type'        => 'Result: ' . ucfirst($item->test_result ?? 'Tested') . ($item->bac_level ? ' (BAC: ' . $item->bac_level . ')' : ''),
                'detail_extra'       => $item->device_number ? 'Device: ' . $item->device_number : null,
                'description'        => $item->remarks ?? $item->action_taken ?? '-',
                'damage_cost'        => null,
                'damage_cost_raw'    => 0,
                'fatality'           => null,
                'status_raw'         => (string) $item->status,
                'status_label'       => $statusLabel,
                'status_pill_class'  => $statusClass,
                'status_icon'        => $statusIcon,
                'status_color'       => match ((string) $item->status) {
                    '1' => 'warning',
                    '2' => 'success',
                    default => 'danger',
                },
                'show_url'           => route('breathaliser_test.show', $item->id),
            ];
        }

        // 9. Driver Misconduct
        $misconducts = DriverMisconduct::with($relationsWithDriver)->latest()->get();
        foreach ($misconducts as $item) {
            $resolvedWaybillId = $item->waybill_id ?: $item->waybill?->id;
            $resolvedVehicleId = $item->vehicle_id ?: ($item->waybill?->vehicle_id ?: $item->waybill?->vehicle?->id);
            $driverName = $this->resolveDriverName($item);
            $fleetNo    = $this->resolveFleetNo($item);
            $truckNo    = $this->resolveTruckNo($item);
            $waybillNo  = $item->waybill?->waybill_number;

            $coords = $this->resolveCoordinates(
                $item->misconduct_location_lat,
                $item->misconduct_location_lng,
                $item->misconduct_location,
                $item->waybill,
                $item->vehicle
            );

            $statusLabel = match ((string) $item->status) {
                '1' => 'Reviewed',
                '2' => 'Resolved',
                default => 'Open',
            };
            $statusClass = match ((string) $item->status) {
                '1' => 'status-reviewed',
                '2' => 'status-resolved',
                default => 'status-open',
            };
            $statusIcon = match ((string) $item->status) {
                '1' => 'fa-solid fa-eye',
                '2' => 'fa-solid fa-circle-check',
                default => 'fa-solid fa-triangle-exclamation',
            };

            $incidents[] = [
                'id'                 => (string) $item->id,
                'category'           => 'driver_behavior',
                'category_label'     => 'Driver Behavior',
                'type_key'           => 'driver_misconduct',
                'type_label'         => 'Driver Misconduct',
                'icon'               => 'fa-solid fa-triangle-exclamation',
                'color'              => '#b91c1c',
                'badge_class'        => 'bg-danger',
                'date'               => $item->misconduct_date?->format('d M Y') ?? ($item->created_at?->format('d M Y') ?? '-'),
                'raw_date'           => $item->misconduct_date?->format('Y-m-d') ?? ($item->created_at?->format('Y-m-d') ?? ''),
                'location'           => $item->misconduct_location ?? 'N/A',
                'lat'                => $coords[0],
                'lng'                => $coords[1],
                'waybill_id'         => $resolvedWaybillId,
                'waybill_number'     => $waybillNo,
                'waybill_url'        => $resolvedWaybillId ? route('waybill.show', $resolvedWaybillId) : null,
                'waybill_map_url'    => $resolvedWaybillId ? route('waybill.waybill_map_tracking', $resolvedWaybillId) : null,
                'vehicle_id'         => $resolvedVehicleId,
                'fleet_no'           => $fleetNo,
                'truck_no'           => $truckNo,
                'driver_name'        => $driverName,
                'detail_type'        => $item->misconduct_type ?? 'Misconduct',
                'detail_extra'       => $item->severity ? 'Severity: ' . ucfirst($item->severity) : null,
                'description'        => $item->remarks ?? $item->description ?? $item->action_taken ?? '-',
                'damage_cost'        => null,
                'damage_cost_raw'    => 0,
                'fatality'           => null,
                'status_raw'         => (string) $item->status,
                'status_label'       => $statusLabel,
                'status_pill_class'  => $statusClass,
                'status_icon'        => $statusIcon,
                'status_color'       => match ((string) $item->status) {
                    '1' => 'warning',
                    '2' => 'success',
                    default => 'danger',
                },
                'show_url'           => route('driver_misconduct.show', $item->id),
            ];
        }

        // 10. Goods Inspections
        $inspections = GoodsInspection::with($relationsBasic)->latest()->get();
        foreach ($inspections as $item) {
            $resolvedWaybillId = $item->waybill_id ?: $item->waybill?->id;
            $resolvedVehicleId = $item->vehicle_id ?: ($item->waybill?->vehicle_id ?: $item->waybill?->vehicle?->id);
            $driverName = $this->resolveDriverName($item);
            $fleetNo    = $this->resolveFleetNo($item);
            $truckNo    = $this->resolveTruckNo($item);
            $waybillNo  = $item->waybill?->waybill_number;

            $coords = $this->resolveCoordinates(
                $item->inspection_location_lat,
                $item->inspection_location_lng,
                $item->inspection_location,
                $item->waybill,
                $item->vehicle
            );

            $incidents[] = [
                'id'                 => (string) $item->id,
                'category'           => 'goods_inspection',
                'category_label'     => 'Goods Inspection',
                'type_key'           => 'goods_inspection',
                'type_label'         => 'Goods Inspection',
                'icon'               => 'fa-solid fa-clipboard-check',
                'color'              => '#3b82f6',
                'badge_class'        => 'bg-primary',
                'date'               => $item->inspection_date?->format('d M Y') ?? ($item->created_at?->format('d M Y') ?? '-'),
                'raw_date'           => $item->inspection_date?->format('Y-m-d') ?? ($item->created_at?->format('Y-m-d') ?? ''),
                'location'           => $item->inspection_location ?? 'N/A',
                'lat'                => (float) $coords[0],
                'lng'                => (float) $coords[1],
                'waybill_id'         => $resolvedWaybillId,
                'waybill_number'     => $waybillNo,
                'waybill_url'        => $resolvedWaybillId ? route('waybill.show', $resolvedWaybillId) : null,
                'waybill_map_url'    => $resolvedWaybillId ? route('waybill.waybill_map_tracking', $resolvedWaybillId) : null,
                'vehicle_id'         => $resolvedVehicleId,
                'fleet_no'           => $fleetNo,
                'truck_no'           => $truckNo,
                'driver_name'        => $driverName,
                'detail_type'        => 'Inspection: ' . ucfirst($item->inspection_type ?? 'General'),
                'detail_extra'       => 'Status: ' . ucfirst($item->goods_status ?? 'Cleared'),
                'description'        => $item->remarks ?? $item->goods_description ?? '-',
                'damage_cost'        => null,
                'damage_cost_raw'    => 0,
                'fatality'           => null,
                'status_raw'         => (string) $item->status,
                'status_label'       => match ((string) $item->status) {
                    '1' => 'Completed',
                    default => 'Pending',
                },
                'status_pill_class'  => match ((string) $item->status) {
                    '1' => 'status-resolved',
                    default => 'status-pending',
                },
                'status_icon'        => match ((string) $item->status) {
                    '1' => 'fa-solid fa-circle-check',
                    default => 'fa-solid fa-clock-rotate-left',
                },
                'status_color'       => match ((string) $item->status) {
                    '1' => 'success',
                    default => 'warning',
                },
                'show_url'           => route('goods_inspection.show', $item->id),
            ];
        }

        // Apply slight radial jitter to identical coordinates so that all pulsing markers are visible & individually clickable
        $coordCounts = [];
        foreach ($incidents as $idx => $inc) {
            $key = sprintf('%.4f_%.4f', $inc['lat'], $inc['lng']);
            $count = $coordCounts[$key] ?? 0;
            $coordCounts[$key] = $count + 1;

            if ($count > 0) {
                $angle = ($count * 60) * (M_PI / 180);
                $radius = 0.0018 + (floor($count / 6) * 0.0012);
                $incidents[$idx]['lat'] += (float) ($radius * cos($angle));
                $incidents[$idx]['lng'] += (float) ($radius * sin($angle));
            }
        }

        // Apply filters if provided
        if (!empty($filters['vehicle_id'])) {
            $incidents = array_values(array_filter($incidents, fn ($i) => (string) ($i['vehicle_id'] ?? '') === (string) $filters['vehicle_id']));
        }

        if (!empty($filters['waybill_number'])) {
            $searchWb = strtolower(trim($filters['waybill_number']));
            $incidents = array_values(array_filter($incidents, fn ($i) => str_contains(strtolower($i['waybill_number'] ?? ''), $searchWb)));
        }

        if (!empty($filters['fleet_no'])) {
            $searchFleet = strtolower(trim($filters['fleet_no']));
            $incidents = array_values(array_filter($incidents, fn ($i) => str_contains(strtolower($i['fleet_no'] ?? ''), $searchFleet)));
        }

        if (!empty($filters['location'])) {
            $searchLoc = strtolower(trim($filters['location']));
            $incidents = array_values(array_filter($incidents, fn ($i) => str_contains(strtolower($i['location'] ?? ''), $searchLoc)));
        }

        // Sort events chronologically (latest first)
        usort($incidents, function ($a, $b) {
            return strcmp($b['raw_date'], $a['raw_date']);
        });

        return $incidents;
    }

    /**
     * Get all fleet vehicles with telemetry and all linked waybills for live tracking.
     *
     * @param array $allIncidents
     * @return array
     */
    public function getFleetVehiclesForMap(array $allIncidents = []): array
    {
        $vehicles = Vehicle::with(['drivers', 'vehicle_make_model'])
            ->where(function ($q) {
                $q->whereNotNull('gps_unit_id')
                  ->orWhereNotNull('fleet_no')
                  ->orWhereNotNull('truck_no');
            })
            ->get();

        // Get all waybills grouped by vehicle_id
        $allWaybillsGrouped = Waybill::with(['driver', 'status', 'route_trip'])
            ->latest()
            ->get()
            ->groupBy('vehicle_id');

        $fleetData = [];

        foreach ($vehicles as $vehicle) {
            $vehWaybills = $allWaybillsGrouped->get($vehicle->id) ?? collect();
            $latestWaybill = $vehWaybills->first();

            $allWaybillNumbers = $vehWaybills->pluck('waybill_number')->filter()->values()->all();
            $allWaybillIds     = $vehWaybills->pluck('id')->filter()->values()->all();
            $allOrigins        = $vehWaybills->pluck('origin')->filter()->unique()->values()->all();
            $allDestinations   = $vehWaybills->pluck('destination')->filter()->unique()->values()->all();

            // Collect any waybill numbers from incidents linked to this vehicle
            $incidentsForVeh = collect($allIncidents)->filter(fn ($i) => $i['vehicle_id'] === $vehicle->id || ($i['fleet_no'] && $i['fleet_no'] === $vehicle->fleet_no));
            foreach ($incidentsForVeh as $inc) {
                if (!empty($inc['waybill_number']) && !in_array($inc['waybill_number'], $allWaybillNumbers, true)) {
                    $allWaybillNumbers[] = $inc['waybill_number'];
                }
                if (!empty($inc['waybill_id']) && !in_array($inc['waybill_id'], $allWaybillIds, true)) {
                    $allWaybillIds[] = $inc['waybill_id'];
                }
            }

            $driverName = '—';
            if ($latestWaybill && $latestWaybill->driver) {
                $driverName = trim(($latestWaybill->driver->first_name ?? '') . ' ' . ($latestWaybill->driver->last_name ?? ''));
            } elseif ($vehicle->drivers && $vehicle->drivers->isNotEmpty()) {
                $firstDriver = $vehicle->drivers->first();
                $driverName = trim(($firstDriver->first_name ?? '') . ' ' . ($firstDriver->last_name ?? ''));
            }

            $initialLat = null;
            $initialLng = null;

            if ($latestWaybill) {
                if ($latestWaybill->destination_lat && $latestWaybill->destination_lng) {
                    $initialLat = (float) $latestWaybill->destination_lat;
                    $initialLng = (float) $latestWaybill->destination_lng;
                } elseif ($latestWaybill->origin_lat && $latestWaybill->origin_lng) {
                    $initialLat = (float) $latestWaybill->origin_lat;
                    $initialLng = (float) $latestWaybill->origin_lng;
                }
            }

            if ($initialLat === null && !empty($allIncidents)) {
                $vehIncident = collect($allIncidents)->first(fn ($i) => $i['vehicle_id'] === $vehicle->id && $i['lat'] !== null);
                if ($vehIncident) {
                    $initialLat = (float) $vehIncident['lat'];
                    $initialLng = (float) $vehIncident['lng'];
                }
            }

            // Fallback to corridor if no coordinate found
            if ($initialLat === null) {
                $initialLat = 33.6844 + (crc32($vehicle->id) % 100) * 0.005;
                $initialLng = 73.0479 + (crc32($vehicle->fleet_no ?? 'FL') % 100) * 0.005;
            }

            $fleetData[] = [
                'id'                  => (string) $vehicle->id,
                'gps_unit_id'         => $vehicle->gps_unit_id ? (string) $vehicle->gps_unit_id : null,
                'fleet_no'            => $vehicle->fleet_no ?? 'Asset',
                'truck_no'            => $vehicle->truck_no ?? 'N/A',
                'vehicle_type'        => $vehicle->vehicle_type ?? 'Truck',
                'driver_name'         => $driverName,
                'status_label'        => $vehicle->vehicle_status,
                'lat'                 => $initialLat,
                'lng'                 => $initialLng,
                'waybill_id'          => $latestWaybill?->id,
                'waybill_number'      => $latestWaybill?->waybill_number,
                'all_waybill_numbers' => $allWaybillNumbers,
                'all_waybill_ids'     => $allWaybillIds,
                'waybill_origin'      => $latestWaybill?->origin,
                'waybill_dest'        => $latestWaybill?->destination,
                'all_origins'         => $allOrigins,
                'all_destinations'    => $allDestinations,
                'waybill_status'      => $latestWaybill?->status?->badge_label,
                'waybill_map_url'     => $latestWaybill ? route('waybill.waybill_map_tracking', $latestWaybill->id) : null,
                'vehicle_show_url'    => route('vehicle.show', $vehicle->id),
            ];
        }

        return $fleetData;
    }

    /**
     * Get distinct metadata values for filtering menus.
     *
     * @param array $allIncidents
     * @param array $allVehicles
     * @return array
     */
    public function getFilterOptions(array $allIncidents, array $allVehicles): array
    {
        // Collect all waybills from DB + incidents + vehicles
        $dbWaybills = Waybill::select('waybill_number')
            ->whereNotNull('waybill_number')
            ->distinct()
            ->pluck('waybill_number')
            ->filter(fn ($wb) => !empty(trim($wb)))
            ->values()
            ->all();

        $incWaybills = collect($allIncidents)->pluck('waybill_number')->filter()->all();
        $vehWaybills = collect($allVehicles)->pluck('all_waybill_numbers')->flatten()->filter()->all();

        $waybills = collect(array_merge($dbWaybills, $incWaybills, $vehWaybills))
            ->unique()
            ->sort(SORT_NATURAL)
            ->values()
            ->all();

        // Fleets
        $dbFleets = Vehicle::select('fleet_no')
            ->whereNotNull('fleet_no')
            ->distinct()
            ->pluck('fleet_no')
            ->filter(fn ($fl) => !empty(trim($fl)))
            ->values()
            ->all();

        $incFleets = collect($allIncidents)->pluck('fleet_no')->filter(fn ($f) => $f && $f !== '—')->all();
        $vehFleets = collect($allVehicles)->pluck('fleet_no')->filter(fn ($f) => $f && $f !== '—')->all();

        $fleets = collect(array_merge($dbFleets, $incFleets, $vehFleets))
            ->unique()
            ->sort(SORT_NATURAL)
            ->values()
            ->all();

        // Locations
        $locations = collect($allIncidents)
            ->pluck('location')
            ->merge(Waybill::select('origin')->whereNotNull('origin')->pluck('origin'))
            ->merge(Waybill::select('destination')->whereNotNull('destination')->pluck('destination'))
            ->filter(fn ($loc) => $loc && $loc !== 'N/A' && !empty(trim($loc)))
            ->unique()
            ->sort(SORT_NATURAL)
            ->values()
            ->all();

        $typeCounts = [];
        foreach ($allIncidents as $item) {
            $typeCounts[$item['type_key']] = ($typeCounts[$item['type_key']] ?? 0) + 1;
        }

        return [
            'waybills'   => $waybills,
            'fleets'     => $fleets,
            'locations'  => $locations,
            'typeCounts' => $typeCounts,
        ];
    }

    /**
     * Resolve valid latitude & longitude with multi-stage fallback.
     */
    protected function resolveCoordinates($lat, $lng, $location, $waybill = null, $vehicle = null): array
    {
        if ($lat !== null && $lng !== null && !empty($lat) && !empty($lng) && (float)$lat != 0 && (float)$lng != 0) {
            return [(float) $lat, (float) $lng];
        }

        // 1. Waybill Destination
        if ($waybill && $waybill->destination_lat && $waybill->destination_lng) {
            return [(float) $waybill->destination_lat, (float) $waybill->destination_lng];
        }

        // 2. Waybill Origin
        if ($waybill && $waybill->origin_lat && $waybill->origin_lng) {
            return [(float) $waybill->origin_lat, (float) $waybill->origin_lng];
        }

        // 3. Known city dictionary lookup by location string
        if (!empty($location)) {
            $locLower = strtolower($location);
            foreach ($this->knownLocations as $city => $coords) {
                if (str_contains($locLower, $city)) {
                    return $coords;
                }
            }
        }

        // 4. Known city dictionary lookup by waybill destination / origin
        if ($waybill) {
            if (!empty($waybill->destination)) {
                $destLower = strtolower($waybill->destination);
                foreach ($this->knownLocations as $city => $coords) {
                    if (str_contains($destLower, $city)) {
                        return $coords;
                    }
                }
            }
            if (!empty($waybill->origin)) {
                $origLower = strtolower($waybill->origin);
                foreach ($this->knownLocations as $city => $coords) {
                    if (str_contains($origLower, $city)) {
                        return $coords;
                    }
                }
            }
        }

        // 5. Default fallback to Islamabad corridor
        return [33.6844, 73.0479];
    }

    private function resolveDriverName($item): string
    {
        if (isset($item->driver) && $item->driver) {
            $name = trim(($item->driver->first_name ?? '') . ' ' . ($item->driver->last_name ?? ''));
            if (!empty($name)) return $name;
        }

        if (isset($item->waybill) && $item->waybill && $item->waybill->driver) {
            $name = trim(($item->waybill->driver->first_name ?? '') . ' ' . ($item->waybill->driver->last_name ?? ''));
            if (!empty($name)) return $name;
        }

        if (isset($item->vehicle) && $item->vehicle && $item->vehicle->drivers && $item->vehicle->drivers->isNotEmpty()) {
            $firstDriver = $item->vehicle->drivers->first();
            $name = trim(($firstDriver->first_name ?? '') . ' ' . ($firstDriver->last_name ?? ''));
            if (!empty($name)) return $name;
        }

        return '—';
    }

    private function resolveFleetNo($item): string
    {
        return $item->vehicle?->fleet_no ?? ($item->waybill?->vehicle?->fleet_no ?? '—');
    }

    private function resolveTruckNo($item): string
    {
        return $item->vehicle?->truck_no ?? ($item->waybill?->vehicle?->truck_no ?? '—');
    }
}
