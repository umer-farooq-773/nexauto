<?php

namespace App\Http\Controllers;

use App\Models\Accident;
use App\Models\VehicleBreakdown;
use App\Models\VehicleBoggedDown;
use App\Models\Impoundment;
use App\Models\IllegalDetention;
use App\Models\RouteDiversion;
use App\Models\IllegalHaulage;
use App\Models\IllegalShortage;
use App\Models\BreathaliserTest;
use App\Models\DriverMisconduct;
use App\Models\GoodsInspection;
use Illuminate\Http\Request;

class IncidentMapController extends Controller
{
    /**
     * Display the full-width map of all incidents and driver behaviors.
     */
    public function index(Request $request)
    {
        $vehicleId = $request->filled('vehicle_id') ? (string) $request->input('vehicle_id') : null;
        $allEvents = $this->getAllMapIncidents($vehicleId);

        // Calculate KPI Metrics
        $totalCount = count($allEvents);
        $incidentCount = count(array_filter($allEvents, fn($item) => $item['category'] === 'incident'));
        $behaviorCount = count(array_filter($allEvents, fn($item) => $item['category'] === 'driver_behavior'));
        
        $pendingCount = count(array_filter($allEvents, fn($item) => in_array($item['status_raw'], ['0', 'pending', 'stuck', 'open', 'impounded', 'detained'])));
        $inProgressCount = count(array_filter($allEvents, fn($item) => in_array($item['status_raw'], ['2', 'in_progress', 'active', 'reviewed'])));
        $resolvedCount = count(array_filter($allEvents, fn($item) => in_array($item['status_raw'], ['1', 'resolved', 'recovered', 'released', 'completed', 'closed'])));

        $totalDamageCost = array_reduce($allEvents, function ($carry, $item) {
            return $carry + (float) ($item['damage_cost_raw'] ?? 0);
        }, 0);

        // Group counts by type for toolbar chips
        $typeCounts = [];
        foreach ($allEvents as $item) {
            $typeCounts[$item['type_key']] = ($typeCounts[$item['type_key']] ?? 0) + 1;
        }

        return view('auth.pages.incident.map.all_map', [
            'events'          => $allEvents,
            'totalCount'      => $totalCount,
            'incidentCount'   => $incidentCount,
            'behaviorCount'   => $behaviorCount,
            'pendingCount'    => $pendingCount,
            'inProgressCount' => $inProgressCount,
            'resolvedCount'   => $resolvedCount,
            'totalDamageCost' => $totalDamageCost,
            'typeCounts'      => $typeCounts,
            'geoapifyApiKey'  => config('services.geoapify.api_key'),
            'appEnv'          => config('app.env'),
        ]);
    }

    /**
     * Aggregate and format all incidents and driver behavior events across all waybills and vehicles.
     */
    protected function getAllMapIncidents(?string $vehicleId = null): array
    {
        $incidents = [];

        // 1. Accidents
        $accidents = Accident::with(['waybill.driver', 'vehicle.drivers'])->latest()->get();
        foreach ($accidents as $item) {
            $driverName = $this->resolveDriverName($item);
            $fleetNo    = $this->resolveFleetNo($item);
            $truckNo    = $this->resolveTruckNo($item);
            $waybillNo  = $item->waybill?->waybill_number;

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
                'lat'                => $item->accident_location_lat !== null ? (float) $item->accident_location_lat : null,
                'lng'                => $item->accident_location_lng !== null ? (float) $item->accident_location_lng : null,
                'waybill_id'         => $item->waybill_id,
                'waybill_number'     => $waybillNo,
                'waybill_url'        => $item->waybill_id ? route('waybill.show', $item->waybill_id) : null,
                'waybill_map_url'    => $item->waybill_id ? route('waybill.waybill_map_tracking', $item->waybill_id) : null,
                'vehicle_id'         => $item->vehicle_id,
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
        $breakdowns = VehicleBreakdown::with(['waybill.driver', 'vehicle.drivers'])->latest()->get();
        foreach ($breakdowns as $item) {
            $driverName = $this->resolveDriverName($item);
            $fleetNo    = $this->resolveFleetNo($item);
            $truckNo    = $this->resolveTruckNo($item);
            $waybillNo  = $item->waybill?->waybill_number;

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
                'lat'                => $item->breakdown_location_lat !== null ? (float) $item->breakdown_location_lat : null,
                'lng'                => $item->breakdown_location_lng !== null ? (float) $item->breakdown_location_lng : null,
                'waybill_id'         => $item->waybill_id,
                'waybill_number'     => $waybillNo,
                'waybill_url'        => $item->waybill_id ? route('waybill.show', $item->waybill_id) : null,
                'waybill_map_url'    => $item->waybill_id ? route('waybill.waybill_map_tracking', $item->waybill_id) : null,
                'vehicle_id'         => $item->vehicle_id,
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
        $bogged = VehicleBoggedDown::with(['waybill.driver', 'vehicle.drivers'])->latest()->get();
        foreach ($bogged as $item) {
            $driverName = $this->resolveDriverName($item);
            $fleetNo    = $this->resolveFleetNo($item);
            $truckNo    = $this->resolveTruckNo($item);
            $waybillNo  = $item->waybill?->waybill_number;

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
                'lat'                => $item->stuck_location_lat !== null ? (float) $item->stuck_location_lat : null,
                'lng'                => $item->stuck_location_lng !== null ? (float) $item->stuck_location_lng : null,
                'waybill_id'         => $item->waybill_id,
                'waybill_number'     => $waybillNo,
                'waybill_url'        => $item->waybill_id ? route('waybill.show', $item->waybill_id) : null,
                'waybill_map_url'    => $item->waybill_id ? route('waybill.waybill_map_tracking', $item->waybill_id) : null,
                'vehicle_id'         => $item->vehicle_id,
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
        $impoundments = Impoundment::with(['waybill.driver', 'vehicle.drivers'])->latest()->get();
        foreach ($impoundments as $item) {
            $driverName = $this->resolveDriverName($item);
            $fleetNo    = $this->resolveFleetNo($item);
            $truckNo    = $this->resolveTruckNo($item);
            $waybillNo  = $item->waybill?->waybill_number;

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
                'lat'                => $item->impound_location_lat !== null ? (float) $item->impound_location_lat : null,
                'lng'                => $item->impound_location_lng !== null ? (float) $item->impound_location_lng : null,
                'waybill_id'         => $item->waybill_id,
                'waybill_number'     => $waybillNo,
                'waybill_url'        => $item->waybill_id ? route('waybill.show', $item->waybill_id) : null,
                'waybill_map_url'    => $item->waybill_id ? route('waybill.waybill_map_tracking', $item->waybill_id) : null,
                'vehicle_id'         => $item->vehicle_id,
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
        $detentions = IllegalDetention::with(['waybill.driver', 'vehicle.drivers'])->latest()->get();
        foreach ($detentions as $item) {
            $driverName = $this->resolveDriverName($item);
            $fleetNo    = $this->resolveFleetNo($item);
            $truckNo    = $this->resolveTruckNo($item);
            $waybillNo  = $item->waybill?->waybill_number;

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
                'lat'                => $item->detention_location_lat !== null ? (float) $item->detention_location_lat : null,
                'lng'                => $item->detention_location_lng !== null ? (float) $item->detention_location_lng : null,
                'waybill_id'         => $item->waybill_id,
                'waybill_number'     => $waybillNo,
                'waybill_url'        => $item->waybill_id ? route('waybill.show', $item->waybill_id) : null,
                'waybill_map_url'    => $item->waybill_id ? route('waybill.waybill_map_tracking', $item->waybill_id) : null,
                'vehicle_id'         => $item->vehicle_id,
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
        $diversions = RouteDiversion::with(['waybill.driver', 'vehicle.drivers', 'driver'])->latest()->get();
        foreach ($diversions as $item) {
            $driverName = $this->resolveDriverName($item);
            $fleetNo    = $this->resolveFleetNo($item);
            $truckNo    = $this->resolveTruckNo($item);
            $waybillNo  = $item->waybill?->waybill_number;

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
                'lat'                => $item->diversion_location_lat !== null ? (float) $item->diversion_location_lat : null,
                'lng'                => $item->diversion_location_lng !== null ? (float) $item->diversion_location_lng : null,
                'waybill_id'         => $item->waybill_id,
                'waybill_number'     => $waybillNo,
                'waybill_url'        => $item->waybill_id ? route('waybill.show', $item->waybill_id) : null,
                'waybill_map_url'    => $item->waybill_id ? route('waybill.waybill_map_tracking', $item->waybill_id) : null,
                'vehicle_id'         => $item->vehicle_id,
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
        $haulages = IllegalHaulage::with(['waybill.driver', 'vehicle.drivers', 'driver'])->latest()->get();
        foreach ($haulages as $item) {
            $driverName = $this->resolveDriverName($item);
            $fleetNo    = $this->resolveFleetNo($item);
            $truckNo    = $this->resolveTruckNo($item);
            $waybillNo  = $item->waybill?->waybill_number;

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
                'type_key'           => 'illegal_haulage',
                'type_label'         => 'Illegal Haulage',
                'icon'               => 'fa-solid fa-boxes-packing',
                'color'              => '#ea580c',
                'badge_class'        => 'bg-warning text-dark',
                'date'               => $item->haulage_date?->format('d M Y') ?? ($item->created_at?->format('d M Y') ?? '-'),
                'raw_date'           => $item->haulage_date?->format('Y-m-d') ?? ($item->created_at?->format('Y-m-d') ?? ''),
                'location'           => $item->haulage_location ?? 'N/A',
                'lat'                => $item->haulage_location_lat !== null ? (float) $item->haulage_location_lat : null,
                'lng'                => $item->haulage_location_lng !== null ? (float) $item->haulage_location_lng : null,
                'waybill_id'         => $item->waybill_id,
                'waybill_number'     => $waybillNo,
                'waybill_url'        => $item->waybill_id ? route('waybill.show', $item->waybill_id) : null,
                'waybill_map_url'    => $item->waybill_id ? route('waybill.waybill_map_tracking', $item->waybill_id) : null,
                'vehicle_id'         => $item->vehicle_id,
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
        $shortages = IllegalShortage::with(['waybill.driver', 'vehicle.drivers', 'driver'])->latest()->get();
        foreach ($shortages as $item) {
            $driverName = $this->resolveDriverName($item);
            $fleetNo    = $this->resolveFleetNo($item);
            $truckNo    = $this->resolveTruckNo($item);
            $waybillNo  = $item->waybill?->waybill_number;

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
                'type_key'           => 'illegal_shortage',
                'type_label'         => 'Illegal Shortage',
                'icon'               => 'fa-solid fa-boxes-packing',
                'color'              => '#d97706',
                'badge_class'        => 'bg-warning text-dark',
                'date'               => $item->shortage_date?->format('d M Y') ?? ($item->created_at?->format('d M Y') ?? '-'),
                'raw_date'           => $item->shortage_date?->format('Y-m-d') ?? ($item->created_at?->format('Y-m-d') ?? ''),
                'location'           => $item->shortage_location ?? 'N/A',
                'lat'                => $item->shortage_location_lat !== null ? (float) $item->shortage_location_lat : null,
                'lng'                => $item->shortage_location_lng !== null ? (float) $item->shortage_location_lng : null,
                'waybill_id'         => $item->waybill_id,
                'waybill_number'     => $waybillNo,
                'waybill_url'        => $item->waybill_id ? route('waybill.show', $item->waybill_id) : null,
                'waybill_map_url'    => $item->waybill_id ? route('waybill.waybill_map_tracking', $item->waybill_id) : null,
                'vehicle_id'         => $item->vehicle_id,
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
        $tests = BreathaliserTest::with(['waybill.driver', 'vehicle.drivers', 'driver'])->latest()->get();
        foreach ($tests as $item) {
            $driverName = $this->resolveDriverName($item);
            $fleetNo    = $this->resolveFleetNo($item);
            $truckNo    = $this->resolveTruckNo($item);
            $waybillNo  = $item->waybill?->waybill_number;

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
                'lat'                => $item->test_location_lat !== null ? (float) $item->test_location_lat : null,
                'lng'                => $item->test_location_lng !== null ? (float) $item->test_location_lng : null,
                'waybill_id'         => $item->waybill_id,
                'waybill_number'     => $waybillNo,
                'waybill_url'        => $item->waybill_id ? route('waybill.show', $item->waybill_id) : null,
                'waybill_map_url'    => $item->waybill_id ? route('waybill.waybill_map_tracking', $item->waybill_id) : null,
                'vehicle_id'         => $item->vehicle_id,
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
        $misconducts = DriverMisconduct::with(['waybill.driver', 'vehicle.drivers', 'driver'])->latest()->get();
        foreach ($misconducts as $item) {
            $driverName = $this->resolveDriverName($item);
            $fleetNo    = $this->resolveFleetNo($item);
            $truckNo    = $this->resolveTruckNo($item);
            $waybillNo  = $item->waybill?->waybill_number;

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
                'lat'                => $item->misconduct_location_lat !== null ? (float) $item->misconduct_location_lat : null,
                'lng'                => $item->misconduct_location_lng !== null ? (float) $item->misconduct_location_lng : null,
                'waybill_id'         => $item->waybill_id,
                'waybill_number'     => $waybillNo,
                'waybill_url'        => $item->waybill_id ? route('waybill.show', $item->waybill_id) : null,
                'waybill_map_url'    => $item->waybill_id ? route('waybill.waybill_map_tracking', $item->waybill_id) : null,
                'vehicle_id'         => $item->vehicle_id,
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

        if ($vehicleId !== null) {
            $incidents = array_values(array_filter($incidents, function (array $incident) use ($vehicleId) {
                return (string) ($incident['vehicle_id'] ?? '') === $vehicleId;
            }));
        }

        // Sort events chronologically (latest first)
        usort($incidents, function ($a, $b) {
            return strcmp($b['raw_date'], $a['raw_date']);
        });

        return $incidents;
    }

    /**
     * Display a dedicated map view for a specific incident and vehicle live tracking.
     */
    public function singleMap(Request $request, string $type, string $id)
    {
        $normalizedType = str_replace('-', '_', strtolower($type));

        $relations = ['waybill.driver', 'waybill.status', 'waybill.route_trip', 'vehicle.drivers'];

        $model = match ($normalizedType) {
            'accident'            => Accident::with($relations)->find($id),
            'vehicle_breakdown'   => VehicleBreakdown::with($relations)->find($id),
            'vehicle_bogged_down' => VehicleBoggedDown::with($relations)->find($id),
            'impoundment'         => Impoundment::with($relations)->find($id),
            'illegal_detention'   => IllegalDetention::with($relations)->find($id),
            'route_diversion'     => RouteDiversion::with(array_merge($relations, ['driver']))->find($id),
            'illegal_haulage'     => IllegalHaulage::with(array_merge($relations, ['driver']))->find($id),
            'illegal_shortage'    => IllegalShortage::with(array_merge($relations, ['driver']))->find($id),
            'breathaliser_test'   => BreathaliserTest::with(array_merge($relations, ['driver']))->find($id),
            'driver_misconduct'   => DriverMisconduct::with(array_merge($relations, ['driver']))->find($id),
            'goods_inspection'    => GoodsInspection::with($relations)->find($id),
            default               => null,
        };

        if (!$model) {
            abort(404, 'Incident record not found.');
        }

        $formattedIncident = $this->formatSingleIncident($model, $normalizedType);

        $vehicle    = $model->vehicle ?? $model->waybill?->vehicle;
        $waybill    = $model->waybill;
        $driverName = $this->resolveDriverName($model);
        $fleetNo    = $this->resolveFleetNo($model);
        $truckNo    = $this->resolveTruckNo($model);

        $matchedUnitId = $vehicle?->gps_unit_id
            ? (string) $vehicle->gps_unit_id
            : null;

        return view('auth.pages.incident.map.single_map', [
            'wialonToken'    => config('services.wialon.token'),
            'wialonHost'     => config('services.wialon.host'),
            'geoapifyApiKey' => config('services.geoapify.api_key'),
            'appEnv'         => config('app.env'),
            'fleetNo'        => $fleetNo,
            'truckNo'        => $truckNo,
            'matchedUnitId'  => $matchedUnitId,
            'incident'       => $formattedIncident,
            'rawModel'       => $model,
            'waybill'        => $waybill,
            'vehicle'        => $vehicle,
            'driverName'     => $driverName,
            'embedded'       => $request->boolean('embedded'),
        ]);
    }

    /**
     * Format a single incident instance into a consistent data array.
     */
    public function formatSingleIncident($item, string $type): array
    {
        $driverName = $this->resolveDriverName($item);
        $fleetNo    = $this->resolveFleetNo($item);
        $truckNo    = $this->resolveTruckNo($item);
        $waybillNo  = $item->waybill?->waybill_number;

        $showRoute = match ($type) {
            'accident'            => route('accident.show', $item->id),
            'vehicle_breakdown'   => route('vehicle_breakdown.show', $item->id),
            'vehicle_bogged_down' => route('vehicle_bogged_down.show', $item->id),
            'impoundment'         => route('impoundment.show', $item->id),
            'illegal_detention'   => route('illegal_detention.show', $item->id),
            'route_diversion'     => route('route_diversion.show', $item->id),
            'illegal_haulage'     => route('illegal_haulage.show', $item->id),
            'illegal_shortage'    => route('illegal_shortage.show', $item->id),
            'breathaliser_test'   => route('breathaliser_test.show', $item->id),
            'driver_misconduct'   => route('driver_misconduct.show', $item->id),
            'goods_inspection'    => route('goods_inspection.show', $item->id),
            default               => '#',
        };

        return match ($type) {
            'accident' => [
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
                'lat'                => $item->accident_location_lat !== null ? (float) $item->accident_location_lat : null,
                'lng'                => $item->accident_location_lng !== null ? (float) $item->accident_location_lng : null,
                'waybill_id'         => $item->waybill_id,
                'waybill_number'     => $waybillNo,
                'waybill_url'        => $item->waybill_id ? route('waybill.show', $item->waybill_id) : null,
                'waybill_map_url'    => $item->waybill_id ? route('waybill.waybill_map_tracking', $item->waybill_id) : null,
                'vehicle_id'         => $item->vehicle_id,
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
                'status_label'       => match ((string) $item->status) {
                    '1' => 'Resolved',
                    '2' => 'In Progress',
                    default => 'Pending',
                },
                'status_pill_class'  => match ((string) $item->status) {
                    '1' => 'status-resolved',
                    '2' => 'status-in_progress',
                    default => 'status-pending',
                },
                'status_icon'        => match ((string) $item->status) {
                    '1' => 'fa-solid fa-circle-check',
                    '2' => 'fa-solid fa-clock-rotate-left',
                    default => 'fa-solid fa-triangle-exclamation',
                },
                'status_color'       => match ((string) $item->status) {
                    '1' => 'success',
                    '2' => 'warning',
                    default => 'danger',
                },
                'show_url'           => $showRoute,
                'single_map_url'     => route('incident.single_map', ['type' => 'accident', 'id' => $item->id]),
            ],
            'vehicle_breakdown' => [
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
                'lat'                => $item->breakdown_location_lat !== null ? (float) $item->breakdown_location_lat : null,
                'lng'                => $item->breakdown_location_lng !== null ? (float) $item->breakdown_location_lng : null,
                'waybill_id'         => $item->waybill_id,
                'waybill_number'     => $waybillNo,
                'waybill_url'        => $item->waybill_id ? route('waybill.show', $item->waybill_id) : null,
                'waybill_map_url'    => $item->waybill_id ? route('waybill.waybill_map_tracking', $item->waybill_id) : null,
                'vehicle_id'         => $item->vehicle_id,
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
                'status_label'       => match ((string) $item->status) {
                    '1' => 'Resolved',
                    '2' => 'In Progress',
                    default => 'Pending',
                },
                'status_pill_class'  => match ((string) $item->status) {
                    '1' => 'status-resolved',
                    '2' => 'status-in_progress',
                    default => 'status-pending',
                },
                'status_icon'        => match ((string) $item->status) {
                    '1' => 'fa-solid fa-circle-check',
                    '2' => 'fa-solid fa-clock-rotate-left',
                    default => 'fa-solid fa-wrench',
                },
                'status_color'       => match ((string) $item->status) {
                    '1' => 'success',
                    '2' => 'warning',
                    default => 'danger',
                },
                'show_url'           => $showRoute,
                'single_map_url'     => route('incident.single_map', ['type' => 'vehicle_breakdown', 'id' => $item->id]),
            ],
            'vehicle_bogged_down' => [
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
                'lat'                => $item->stuck_location_lat !== null ? (float) $item->stuck_location_lat : null,
                'lng'                => $item->stuck_location_lng !== null ? (float) $item->stuck_location_lng : null,
                'waybill_id'         => $item->waybill_id,
                'waybill_number'     => $waybillNo,
                'waybill_url'        => $item->waybill_id ? route('waybill.show', $item->waybill_id) : null,
                'waybill_map_url'    => $item->waybill_id ? route('waybill.waybill_map_tracking', $item->waybill_id) : null,
                'vehicle_id'         => $item->vehicle_id,
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
                'status_label'       => match ((string) $item->status) {
                    '1' => 'Recovered',
                    '2' => 'Recovery In Progress',
                    default => 'Stuck / Pending',
                },
                'status_pill_class'  => match ((string) $item->status) {
                    '1' => 'status-recovered',
                    '2' => 'status-in_progress',
                    default => 'status-stuck',
                },
                'status_icon'        => match ((string) $item->status) {
                    '1' => 'fa-solid fa-circle-check',
                    '2' => 'fa-solid fa-clock-rotate-left',
                    default => 'fa-solid fa-truck-monster',
                },
                'status_color'       => match ((string) $item->status) {
                    '1' => 'success',
                    '2' => 'warning',
                    default => 'danger',
                },
                'show_url'           => $showRoute,
                'single_map_url'     => route('incident.single_map', ['type' => 'vehicle_bogged_down', 'id' => $item->id]),
            ],
            'impoundment' => [
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
                'lat'                => $item->impound_location_lat !== null ? (float) $item->impound_location_lat : null,
                'lng'                => $item->impound_location_lng !== null ? (float) $item->impound_location_lng : null,
                'waybill_id'         => $item->waybill_id,
                'waybill_number'     => $waybillNo,
                'waybill_url'        => $item->waybill_id ? route('waybill.show', $item->waybill_id) : null,
                'waybill_map_url'    => $item->waybill_id ? route('waybill.waybill_map_tracking', $item->waybill_id) : null,
                'vehicle_id'         => $item->vehicle_id,
                'fleet_no'           => $fleetNo,
                'truck_no'           => $truckNo,
                'driver_name'        => $driverName,
                'detail_type'        => $item->impound_authority ?? 'Impounded',
                'detail_extra'       => $item->release_date ? 'Released: ' . $item->release_date->format('d M Y') : null,
                'description'        => $item->reason ?? $item->remarks ?? '-',
                'damage_cost'        => $item->cost ? number_format((float) $item->cost, 2) : null,
                'damage_cost_raw'    => (float) ($item->cost ?? 0),
                'fatality'           => null,
                'authority'          => $item->impound_authority,
                'reason'             => $item->reason,
                'remarks'            => $item->remarks,
                'release_date'       => $item->release_date?->format('d M Y'),
                'status_raw'         => (string) $item->status,
                'status_label'       => $item->status == '1' ? 'Released' : 'Impounded',
                'status_pill_class'  => $item->status == '1' ? 'status-released' : 'status-impounded',
                'status_icon'        => $item->status == '1' ? 'fa-solid fa-lock-open' : 'fa-solid fa-lock',
                'status_color'       => $item->status == '1' ? 'success' : 'danger',
                'show_url'           => $showRoute,
                'single_map_url'     => route('incident.single_map', ['type' => 'impoundment', 'id' => $item->id]),
            ],
            'illegal_detention' => [
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
                'lat'                => $item->detention_location_lat !== null ? (float) $item->detention_location_lat : null,
                'lng'                => $item->detention_location_lng !== null ? (float) $item->detention_location_lng : null,
                'waybill_id'         => $item->waybill_id,
                'waybill_number'     => $waybillNo,
                'waybill_url'        => $item->waybill_id ? route('waybill.show', $item->waybill_id) : null,
                'waybill_map_url'    => $item->waybill_id ? route('waybill.waybill_map_tracking', $item->waybill_id) : null,
                'vehicle_id'         => $item->vehicle_id,
                'fleet_no'           => $fleetNo,
                'truck_no'           => $truckNo,
                'driver_name'        => $driverName,
                'detail_type'        => $item->detained_by ?? 'Detained',
                'detail_extra'       => $item->release_date ? 'Released: ' . $item->release_date->format('d M Y') : null,
                'description'        => $item->reason ?? $item->remarks ?? '-',
                'damage_cost'        => $item->cost ? number_format((float) $item->cost, 2) : null,
                'damage_cost_raw'    => (float) ($item->cost ?? 0),
                'fatality'           => null,
                'authority'          => $item->detained_by,
                'reason'             => $item->reason,
                'remarks'            => $item->remarks,
                'release_date'       => $item->release_date?->format('d M Y'),
                'status_raw'         => (string) $item->status,
                'status_label'       => $item->status == '1' ? 'Released' : 'Detained',
                'status_pill_class'  => $item->status == '1' ? 'status-released' : 'status-detained',
                'status_icon'        => $item->status == '1' ? 'fa-solid fa-lock-open' : 'fa-solid fa-handcuffs',
                'status_color'       => $item->status == '1' ? 'success' : 'danger',
                'show_url'           => $showRoute,
                'single_map_url'     => route('incident.single_map', ['type' => 'illegal_detention', 'id' => $item->id]),
            ],
            'route_diversion' => [
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
                'lat'                => $item->diversion_location_lat !== null ? (float) $item->diversion_location_lat : null,
                'lng'                => $item->diversion_location_lng !== null ? (float) $item->diversion_location_lng : null,
                'waybill_id'         => $item->waybill_id,
                'waybill_number'     => $waybillNo,
                'waybill_url'        => $item->waybill_id ? route('waybill.show', $item->waybill_id) : null,
                'waybill_map_url'    => $item->waybill_id ? route('waybill.waybill_map_tracking', $item->waybill_id) : null,
                'vehicle_id'         => $item->vehicle_id,
                'fleet_no'           => $fleetNo,
                'truck_no'           => $truckNo,
                'driver_name'        => $driverName,
                'detail_type'        => 'Diverted: ' . ($item->diverted_route ?? 'Unauthorized Route'),
                'detail_extra'       => 'Original: ' . ($item->original_route ?? '-'),
                'description'        => $item->diversion_reason ?? $item->remarks ?? '-',
                'damage_cost'        => null,
                'damage_cost_raw'    => 0,
                'fatality'           => null,
                'diverted_route'     => $item->diverted_route,
                'original_route'     => $item->original_route,
                'status_raw'         => (string) $item->status,
                'status_label'       => match ((string) $item->status) {
                    '1' => 'Completed',
                    '2' => 'Cancelled',
                    default => 'Active Diversion',
                },
                'status_pill_class'  => match ((string) $item->status) {
                    '1' => 'status-completed',
                    '2' => 'status-cancelled',
                    default => 'status-diversion',
                },
                'status_icon'        => match ((string) $item->status) {
                    '1' => 'fa-solid fa-circle-check',
                    '2' => 'fa-solid fa-ban',
                    default => 'fa-solid fa-route',
                },
                'status_color'       => match ((string) $item->status) {
                    '1' => 'success',
                    '2' => 'secondary',
                    default => 'warning',
                },
                'show_url'           => $showRoute,
                'single_map_url'     => route('incident.single_map', ['type' => 'route_diversion', 'id' => $item->id]),
            ],
            'illegal_haulage' => [
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
                'lat'                => $item->haulage_location_lat !== null ? (float) $item->haulage_location_lat : null,
                'lng'                => $item->haulage_location_lng !== null ? (float) $item->haulage_location_lng : null,
                'waybill_id'         => $item->waybill_id,
                'waybill_number'     => $waybillNo,
                'waybill_url'        => $item->waybill_id ? route('waybill.show', $item->waybill_id) : null,
                'waybill_map_url'    => $item->waybill_id ? route('waybill.waybill_map_tracking', $item->waybill_id) : null,
                'vehicle_id'         => $item->vehicle_id,
                'fleet_no'           => $fleetNo,
                'truck_no'           => $truckNo,
                'driver_name'        => $driverName,
                'detail_type'        => $item->haulage_type ?? 'Illegal Haulage',
                'detail_extra'       => $item->severity ? 'Severity: ' . ucfirst($item->severity) : null,
                'description'        => $item->cargo_description ?? $item->remarks ?? $item->action_taken ?? '-',
                'damage_cost'        => null,
                'damage_cost_raw'    => 0,
                'fatality'           => null,
                'cargo_description'  => $item->cargo_description,
                'severity'           => $item->severity,
                'action_taken'       => $item->action_taken,
                'status_raw'         => (string) $item->status,
                'status_label'       => match ((string) $item->status) {
                    '1' => 'Reviewed',
                    '2' => 'Resolved',
                    default => 'Open',
                },
                'status_pill_class'  => match ((string) $item->status) {
                    '1' => 'status-reviewed',
                    '2' => 'status-resolved',
                    default => 'status-open',
                },
                'status_icon'        => match ((string) $item->status) {
                    '1' => 'fa-solid fa-eye',
                    '2' => 'fa-solid fa-circle-check',
                    default => 'fa-solid fa-triangle-exclamation',
                },
                'status_color'       => match ((string) $item->status) {
                    '1' => 'warning',
                    '2' => 'success',
                    default => 'danger',
                },
                'show_url'           => $showRoute,
                'single_map_url'     => route('incident.single_map', ['type' => 'illegal_haulage', 'id' => $item->id]),
            ],
            'illegal_shortage' => [
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
                'lat'                => $item->shortage_location_lat !== null ? (float) $item->shortage_location_lat : null,
                'lng'                => $item->shortage_location_lng !== null ? (float) $item->shortage_location_lng : null,
                'waybill_id'         => $item->waybill_id,
                'waybill_number'     => $waybillNo,
                'waybill_url'        => $item->waybill_id ? route('waybill.show', $item->waybill_id) : null,
                'waybill_map_url'    => $item->waybill_id ? route('waybill.waybill_map_tracking', $item->waybill_id) : null,
                'vehicle_id'         => $item->vehicle_id,
                'fleet_no'           => $fleetNo,
                'truck_no'           => $truckNo,
                'driver_name'        => $driverName,
                'detail_type'        => $item->shortage_type ?? 'Illegal Shortage',
                'detail_extra'       => ($item->shortage_quantity ? 'Qty: ' . $item->shortage_quantity . ' | ' : '') . ($item->severity ? 'Severity: ' . ucfirst($item->severity) : null),
                'description'        => $item->cargo_description ?? $item->remarks ?? $item->action_taken ?? '-',
                'damage_cost'        => null,
                'damage_cost_raw'    => 0,
                'fatality'           => null,
                'cargo_description'  => $item->cargo_description,
                'severity'           => $item->severity,
                'shortage_quantity'  => $item->shortage_quantity,
                'action_taken'       => $item->action_taken,
                'status_raw'         => (string) $item->status,
                'status_label'       => match ((string) $item->status) {
                    '1' => 'Reviewed',
                    '2' => 'Resolved',
                    default => 'Open',
                },
                'status_pill_class'  => match ((string) $item->status) {
                    '1' => 'status-reviewed',
                    '2' => 'status-resolved',
                    default => 'status-open',
                },
                'status_icon'        => match ((string) $item->status) {
                    '1' => 'fa-solid fa-eye',
                    '2' => 'fa-solid fa-circle-check',
                    default => 'fa-solid fa-triangle-exclamation',
                },
                'status_color'       => match ((string) $item->status) {
                    '1' => 'warning',
                    '2' => 'success',
                    default => 'danger',
                },
                'show_url'           => $showRoute,
                'single_map_url'     => route('incident.single_map', ['type' => 'illegal_shortage', 'id' => $item->id]),
            ],
            'breathaliser_test' => [
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
                'lat'                => $item->test_location_lat !== null ? (float) $item->test_location_lat : null,
                'lng'                => $item->test_location_lng !== null ? (float) $item->test_location_lng : null,
                'waybill_id'         => $item->waybill_id,
                'waybill_number'     => $waybillNo,
                'waybill_url'        => $item->waybill_id ? route('waybill.show', $item->waybill_id) : null,
                'waybill_map_url'    => $item->waybill_id ? route('waybill.waybill_map_tracking', $item->waybill_id) : null,
                'vehicle_id'         => $item->vehicle_id,
                'fleet_no'           => $fleetNo,
                'truck_no'           => $truckNo,
                'driver_name'        => $driverName,
                'detail_type'        => 'Result: ' . ucfirst($item->test_result ?? 'Tested') . ($item->bac_level ? ' (BAC: ' . $item->bac_level . ')' : ''),
                'detail_extra'       => $item->device_number ? 'Device: ' . $item->device_number : null,
                'description'        => $item->remarks ?? $item->action_taken ?? '-',
                'damage_cost'        => null,
                'damage_cost_raw'    => 0,
                'fatality'           => null,
                'bac_level'          => $item->bac_level,
                'device_number'      => $item->device_number,
                'test_result'        => $item->test_result,
                'action_taken'       => $item->action_taken,
                'status_raw'         => (string) $item->status,
                'status_label'       => match ((string) $item->status) {
                    '1' => 'Reviewed',
                    '2' => 'Closed',
                    default => 'Open',
                },
                'status_pill_class'  => match ((string) $item->status) {
                    '1' => 'status-reviewed',
                    '2' => 'status-closed',
                    default => 'status-open',
                },
                'status_icon'        => match ((string) $item->status) {
                    '1' => 'fa-solid fa-eye',
                    '2' => 'fa-solid fa-circle-check',
                    default => 'fa-solid fa-wine-bottle',
                },
                'status_color'       => match ((string) $item->status) {
                    '1' => 'warning',
                    '2' => 'success',
                    default => 'danger',
                },
                'show_url'           => $showRoute,
                'single_map_url'     => route('incident.single_map', ['type' => 'breathaliser_test', 'id' => $item->id]),
            ],
            'driver_misconduct' => [
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
                'lat'                => $item->misconduct_location_lat !== null ? (float) $item->misconduct_location_lat : null,
                'lng'                => $item->misconduct_location_lng !== null ? (float) $item->misconduct_location_lng : null,
                'waybill_id'         => $item->waybill_id,
                'waybill_number'     => $waybillNo,
                'waybill_url'        => $item->waybill_id ? route('waybill.show', $item->waybill_id) : null,
                'waybill_map_url'    => $item->waybill_id ? route('waybill.waybill_map_tracking', $item->waybill_id) : null,
                'vehicle_id'         => $item->vehicle_id,
                'fleet_no'           => $fleetNo,
                'truck_no'           => $truckNo,
                'driver_name'        => $driverName,
                'detail_type'        => $item->misconduct_type ?? 'Misconduct',
                'detail_extra'       => $item->severity ? 'Severity: ' . ucfirst($item->severity) : null,
                'description'        => $item->remarks ?? $item->description ?? $item->action_taken ?? '-',
                'damage_cost'        => null,
                'damage_cost_raw'    => 0,
                'fatality'           => null,
                'severity'           => $item->severity,
                'action_taken'       => $item->action_taken,
                'status_raw'         => (string) $item->status,
                'status_label'       => match ((string) $item->status) {
                    '1' => 'Reviewed',
                    '2' => 'Resolved',
                    default => 'Open',
                },
                'status_pill_class'  => match ((string) $item->status) {
                    '1' => 'status-reviewed',
                    '2' => 'status-resolved',
                    default => 'status-open',
                },
                'status_icon'        => match ((string) $item->status) {
                    '1' => 'fa-solid fa-eye',
                    '2' => 'fa-solid fa-circle-check',
                    default => 'fa-solid fa-triangle-exclamation',
                },
                'status_color'       => match ((string) $item->status) {
                    '1' => 'warning',
                    '2' => 'success',
                    default => 'danger',
                },
                'show_url'           => $showRoute,
                'single_map_url'     => route('incident.single_map', ['type' => 'driver_misconduct', 'id' => $item->id]),
            ],
            'goods_inspection' => [
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
                'lat'                => $item->inspection_location_lat !== null ? (float) $item->inspection_location_lat : null,
                'lng'                => $item->inspection_location_lng !== null ? (float) $item->inspection_location_lng : null,
                'waybill_id'         => $item->waybill_id,
                'waybill_number'     => $waybillNo,
                'waybill_url'        => $item->waybill_id ? route('waybill.show', $item->waybill_id) : null,
                'waybill_map_url'    => $item->waybill_id ? route('waybill.waybill_map_tracking', $item->waybill_id) : null,
                'vehicle_id'         => $item->vehicle_id,
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
                'show_url'           => $showRoute,
                'single_map_url'     => route('incident.single_map', ['type' => 'goods_inspection', 'id' => $item->id]),
            ],
            default => [],
        };
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

