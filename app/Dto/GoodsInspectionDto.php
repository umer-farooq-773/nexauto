<?php

namespace App\Dto;

class GoodsInspectionDto
{
    public readonly ?string $inspection_id;
    public readonly ?string $vehicle_id;
    public readonly ?string $waybill_id;
    public readonly string $fleet_no;
    public readonly ?string $vehicle_lorry_number;
    public readonly string $inspection_date;
    public readonly ?string $inspection_time;
    public readonly ?string $inspection_type;
    public readonly ?string $inspection_location;
    public readonly ?float $inspection_location_lat;
    public readonly ?float $inspection_location_lng;
    public readonly string $inspector_name;
    public readonly ?string $route;
    public readonly ?string $driver_name;
    public readonly ?string $driver_phone_id;
    public readonly ?string $goods_description;
    public readonly ?string $waybill_number;
    public readonly ?string $declared_quantity;
    public readonly ?string $observed_quantity;
    public readonly ?string $cargo_secured;
    public readonly ?string $goods_match_waybill;
    public readonly ?string $unauthorised_items;
    public readonly ?string $doors_tarpaulin_secure;
    public readonly ?string $visible_damage;
    public readonly ?string $route_diversion;
    public readonly ?string $illegal_haulage;
    public readonly ?string $illegal_shortage;
    public readonly ?string $breathaliser_test;
    public readonly ?string $driver_misconduct;
    public readonly ?string $issues_found;
    public readonly string $goods_status;
    public readonly string $status;
    public readonly ?string $remarks;
    public readonly ?string $created_by;
    public readonly ?array $cargos;

    public function __construct($request)
    {
        $this->inspection_id            = !empty($request['inspection_id']) ? (string) $request['inspection_id'] : null;
        $this->vehicle_id               = $request['vehicle_id'] ?? null;
        $this->waybill_id               = $request['waybill_id'] ?? null;
        $this->fleet_no                 = $request['fleet_no'];
        $this->vehicle_lorry_number     = $request['vehicle_lorry_number'] ?? null;
        $this->inspection_date          = $request['inspection_date'];
        $this->inspection_time          = $request['inspection_time'] ?? null;
        $this->inspection_type          = $request['inspection_type'] ?? 'Pre-Departure';
        $this->inspection_location      = $request['inspection_location'] ?? null;
        $this->inspection_location_lat  = isset($request['inspection_location_lat']) && $request['inspection_location_lat'] !== '' ? (float) $request['inspection_location_lat'] : null;
        $this->inspection_location_lng  = isset($request['inspection_location_lng']) && $request['inspection_location_lng'] !== '' ? (float) $request['inspection_location_lng'] : null;
        $this->inspector_name           = $request['inspector_name'];
        $this->route                    = $request['route'] ?? null;
        $this->driver_name              = $request['driver_name'] ?? null;
        $this->driver_phone_id          = $request['driver_phone_id'] ?? null;
        $this->goods_description        = $request['goods_description'] ?? null;
        $this->waybill_number           = $request['waybill_number'] ?? null;
        $this->declared_quantity        = $request['declared_quantity'] ?? null;
        $this->observed_quantity        = $request['observed_quantity'] ?? null;
        $this->cargo_secured            = $request['cargo_secured'] ?? null;
        $this->goods_match_waybill      = $request['goods_match_waybill'] ?? null;
        $this->unauthorised_items       = $request['unauthorised_items'] ?? null;
        $this->doors_tarpaulin_secure   = $request['doors_tarpaulin_secure'] ?? null;
        $this->visible_damage           = $request['visible_damage'] ?? null;
        $this->route_diversion          = $request['route_diversion'] ?? 'No';
        $this->illegal_haulage          = $request['illegal_haulage'] ?? 'No';
        $this->illegal_shortage         = $request['illegal_shortage'] ?? 'No';
        $this->breathaliser_test        = $request['breathaliser_test'] ?? 'Passed';
        $this->driver_misconduct        = $request['driver_misconduct'] ?? 'No';
        $this->issues_found             = $request['issues_found'] ?? null;
        $this->goods_status             = $request['goods_status'];
        $this->status                   = $request['status'] ?? '0';
        $this->remarks                  = $request['remarks'] ?? null;
        $this->created_by               = $request['created_by'] ?? \Illuminate\Support\Facades\Auth::id();
        $this->cargos                   = isset($request['cargos']) && is_array($request['cargos']) ? $request['cargos'] : null;
    }

    public static function fromRequest($request)
    {
        return new self($request);
    }

    public function toArray()
    {
        return [
            'inspection_id'           => $this->inspection_id,
            'vehicle_id'              => $this->vehicle_id,
            'waybill_id'              => $this->waybill_id,
            'fleet_no'                => $this->fleet_no,
            'vehicle_lorry_number'    => $this->vehicle_lorry_number,
            'inspection_date'         => $this->inspection_date,
            'inspection_time'         => $this->inspection_time,
            'inspection_type'         => $this->inspection_type,
            'inspection_location'     => $this->inspection_location,
            'inspection_location_lat' => $this->inspection_location_lat,
            'inspection_location_lng' => $this->inspection_location_lng,
            'inspector_name'          => $this->inspector_name,
            'route'                   => $this->route,
            'driver_name'             => $this->driver_name,
            'driver_phone_id'         => $this->driver_phone_id,
            'goods_description'       => $this->goods_description,
            'waybill_number'          => $this->waybill_number,
            'declared_quantity'       => $this->declared_quantity,
            'observed_quantity'       => $this->observed_quantity,
            'cargo_secured'           => $this->cargo_secured,
            'goods_match_waybill'     => $this->goods_match_waybill,
            'unauthorised_items'      => $this->unauthorised_items,
            'doors_tarpaulin_secure'  => $this->doors_tarpaulin_secure,
            'visible_damage'          => $this->visible_damage,
            'route_diversion'         => $this->route_diversion,
            'illegal_haulage'         => $this->illegal_haulage,
            'illegal_shortage'        => $this->illegal_shortage,
            'breathaliser_test'       => $this->breathaliser_test,
            'driver_misconduct'       => $this->driver_misconduct,
            'issues_found'            => $this->issues_found,
            'goods_status'            => $this->goods_status,
            'status'                  => $this->status,
            'remarks'                 => $this->remarks,
            'created_by'              => $this->created_by,
            'cargos'                  => $this->cargos,
        ];
    }

}
