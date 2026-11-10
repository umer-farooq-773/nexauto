{{--
    Vehicle Map Tracking View
    Can display tracking for:
    1. A specific vehicle with an active waybill (shows route)
    2. A specific vehicle without waybill (shows just vehicle position)
--}}
@php
    $embedded   = $embedded ?? request()->boolean('embedded');
    $fleetNo    = $vehicle->fleet_no ?? ($vehicle->truck_no ?? 'Unassigned');
    $unitId     = $vehicle->gps_unit_id ?? null;
    
    // If we have a waybill, use its data
    if ($waybill) {
        $driverName = trim(($waybill->driver->first_name ?? '') . ' ' . ($waybill->driver->last_name ?? '')) ?: '—';
        $statusName = $waybill->status->name ?? ($waybill->status_badge ?? 'Pending');
        $originLat = $waybill->origin_lat;
        $originLng = $waybill->origin_lng;
        $destinationLat = $waybill->destination_lat;
        $destinationLng = $waybill->destination_lng;
        $origin = $waybill->origin;
        $destination = $waybill->destination;
        $waybillNumber = $waybill->waybill_number;
    } else {
        // No active waybill means there is no current driver assignment.
        $driverName = '—';
        $statusName = 'No Active Trip';
        $originLat = null;
        $originLng = null;
        $destinationLat = null;
        $destinationLng = null;
        $origin = null;
        $destination = null;
        $waybillNumber = null;
    }
@endphp

{{-- Use the same waybill map tracking partial, it handles both cases --}}
@include('auth.pages.waybill.partials._map_tracking', [
    'waybill' => $waybill ?? (object)[
        'id' => 'vehicle-' . $vehicle->id,
        'vehicle' => $vehicle,
        'driver' => null,
        'status' => (object)['name' => $statusName],
        'waybill_number' => $waybillNumber,
        'origin' => $origin,
        'destination' => $destination,
        'origin_lat' => $originLat,
        'origin_lng' => $originLng,
        'destination_lat' => $destinationLat,
        'destination_lng' => $destinationLng,
        'cargo_description' => null,
        'cargo_weight_kg' => null,
        'dispatch_date' => null,
        'delivery_date' => null,
        'distance' => null,
        'estimated_duration' => null,
    ],
    'embedded' => $embedded
])
