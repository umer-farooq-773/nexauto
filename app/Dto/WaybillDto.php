<?php

namespace App\Dto;

class WaybillDto
{
    public readonly string $waybill_number;
    public readonly string $vehicle_id;
    public readonly string $driver_id;
    public readonly ?string $status_id;
    public readonly ?string $route_trip_id;
    public readonly ?string $origin;
    public readonly ?string $origin_lat;
    public readonly ?string $origin_lng;
    public readonly ?string $destination;
    public readonly ?string $destination_lat;
    public readonly ?string $destination_lng;
    public readonly ?string $checkpoints;
    public readonly ?string $route_geometry;
    public readonly ?string $distance;
    public readonly ?string $estimated_duration;
    public readonly string $dispatch_date;
    public readonly ?string $estimated_dispatch_time;
    public readonly ?string $delivery_date;
    public readonly ?string $cargo_description;
    public readonly ?string $quantity;
    public readonly ?string $quantity_unit;
    public readonly ?string $cargo_weight_kg;
    public readonly ?string $weight_unit;
    public readonly ?string $volume_unit;
    public readonly ?string $notes;
    public readonly ?string $wialon_route_id;
    public readonly ?string $route_source;
    public readonly ?array $cargos;
    public readonly array $drivers;

    public function __construct($request)
    {
        $this->waybill_number         = $request['waybill_number'];
        $this->vehicle_id             = $request['vehicle_id'];
        $this->drivers                = isset($request['drivers']) && is_array($request['drivers'])
            ? array_values($request['drivers'])
            : [['driver_id' => $request['driver_id'], 'duration_minutes' => null]];
        $this->driver_id              = (string) ($this->drivers[0]['driver_id'] ?? $request['driver_id']);
        $this->status_id              = $request['status_id'] ?? null;
        $this->route_trip_id          = $request['route_trip_id'] ?? null;
        $this->origin                 = $request['origin'] ?? null;
        $this->origin_lat             = $request['origin_lat'] ?? null;
        $this->origin_lng             = $request['origin_lng'] ?? null;
        $this->destination            = $request['destination'] ?? null;
        $this->destination_lat        = $request['destination_lat'] ?? null;
        $this->destination_lng        = $request['destination_lng'] ?? null;
        $this->checkpoints            = $request['checkpoints'] ?? null;
        $this->route_geometry         = $request['route_geometry'] ?? null;
        $this->distance               = $request['distance'] ?? null;
        $this->estimated_duration     = $request['estimated_duration'] ?? null;
        $this->dispatch_date          = $request['dispatch_date'];
        $this->estimated_dispatch_time = $request['estimated_dispatch_time'] ?? null;
        $this->delivery_date          = $request['delivery_date'] ?? null;
        $this->cargo_description      = $request['cargo_description'] ?? null;
        $this->quantity               = $request['quantity'] ?? null;
        $this->quantity_unit          = $request['quantity_unit'] ?? null;
        $this->cargo_weight_kg        = $request['cargo_weight_kg'] ?? null;
        $this->weight_unit             = $request['weight_unit'] ?? null;
        $this->volume_unit             = $request['volume_unit'] ?? null;
        $this->notes                  = $request['notes'] ?? null;
        $this->wialon_route_id        = $request['wialon_route_id'] ?? null;
        $this->route_source           = $request['route_source'] ?? 'geoapify';
        $this->cargos                 = isset($request['cargos']) && is_array($request['cargos']) ? $request['cargos'] : null;
    }

    public static function fromRequest($request)
    {
        return new self($request);
    }

    public function toArray()
    {
        return [
            'waybill_number'          => $this->waybill_number,
            'vehicle_id'              => $this->vehicle_id,
            'driver_id'               => $this->driver_id,
            'status_id'               => $this->status_id,
            'route_trip_id'           => $this->route_trip_id,
            'origin'                  => $this->origin,
            'origin_lat'              => $this->origin_lat,
            'origin_lng'              => $this->origin_lng,
            'destination'             => $this->destination,
            'destination_lat'         => $this->destination_lat,
            'destination_lng'         => $this->destination_lng,
            'checkpoints'             => $this->checkpoints,
            'route_geometry'          => $this->route_geometry,
            'distance'                => $this->distance,
            'estimated_duration'      => $this->estimated_duration,
            'dispatch_date'           => $this->dispatch_date,
            'estimated_dispatch_time' => $this->estimated_dispatch_time,
            'delivery_date'           => $this->delivery_date,
            'cargo_description'       => $this->cargo_description,
            'quantity'                => $this->quantity,
            'quantity_unit'           => $this->quantity_unit,
            'cargo_weight_kg'         => $this->cargo_weight_kg,
            'weight_unit'             => $this->weight_unit,
            'volume_unit'             => $this->volume_unit,
            'notes'                   => $this->notes,
            'wialon_route_id'         => $this->wialon_route_id,
            'route_source'            => $this->route_source,
            'cargos'                  => $this->cargos,
            'drivers'                 => $this->drivers,
        ];
    }

}
