@props([
    'incidentType' => null,
    'incidentId' => null,
    'incident' => null,
    'waybillId' => null,
    'vehicleId' => null,
    'size' => 'sm',
    'variant' => 'outline-primary'
])

@php
    $type = $incidentType;
    $id = $incidentId ?? ($incident?->id ?? null);

    if (!$type && $incident) {
        $className = get_class($incident);
        $type = match($className) {
            \App\Models\Accident::class => 'accident',
            \App\Models\VehicleBreakdown::class => 'vehicle_breakdown',
            \App\Models\VehicleBoggedDown::class => 'vehicle_bogged_down',
            \App\Models\Impoundment::class => 'impoundment',
            \App\Models\IllegalDetention::class => 'illegal_detention',
            \App\Models\RouteDiversion::class => 'route_diversion',
            \App\Models\IllegalHaulage::class => 'illegal_haulage',
            \App\Models\IllegalShortage::class => 'illegal_shortage',
            \App\Models\BreathaliserTest::class => 'breathaliser_test',
            \App\Models\DriverMisconduct::class => 'driver_misconduct',
            \App\Models\GoodsInspection::class => 'goods_inspection',
            default => null,
        };
    }

    $url = null;
    if ($type && $id) {
        $url = route('incident.single_map', ['type' => $type, 'id' => $id]);
    } elseif ($waybillId) {
        $url = route('waybill.waybill_map_tracking', $waybillId);
    } elseif ($vehicleId) {
        $url = route('vehicle.vehicle_map_tracking', $vehicleId);
    }
@endphp

@if($url)
    <a href="{{ $url }}"
       target="_blank"
       class="btn btn-{{ $size }} btn-{{ $variant }} shadow-sm"
       title="{{ __('View live tracking and incident location on map') }}">
        <i class="fas fa-map-marked-alt me-1"></i>
        {{ __('View Map') }}
    </a>
@endif
