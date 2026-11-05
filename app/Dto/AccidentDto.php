<?php

namespace App\Dto;

use Illuminate\Support\Facades\Auth;

class AccidentDto
{
    public readonly string $waybill_id;
    public readonly ?string $vehicle_id;
    public readonly string $accident_date;
    public readonly string $accident_location;
    public readonly ?string $accident_location_lat;
    public readonly ?string $accident_location_lng;
    public readonly ?string $accident_type;
    public readonly ?string $accident_type_detail;
    public readonly ?string $description;
    public readonly ?string $damage_cost;
    public readonly ?string $resolved_date;
    public readonly string $status;
    public readonly string $driver_status;
    public readonly string $motor_mate_status;
    public readonly ?string $content;
    public readonly ?string $damage_extend;
    public readonly string $is_fatality;
    public readonly ?string $fatality_type;
    public readonly ?string $accident_nature_detail;
    public readonly string $is_damage_asset;
    public readonly string $is_injury;
    public readonly string $authority;
    public readonly string $created_by;

    public function __construct($request)
    {
        $this->waybill_id             = $request['waybill_id'];
        $this->vehicle_id             = $request['vehicle_id'] ?? \App\Models\Waybill::find($this->waybill_id)?->vehicle_id;
        $this->accident_date          = $request['accident_date'];
        $this->accident_location      = $request['accident_location'];
        $this->accident_location_lat  = $request['accident_location_lat'] ?? null;
        $this->accident_location_lng  = $request['accident_location_lng'] ?? null;
        $this->accident_type          = $request['accident_type'] ?? null;
        $this->accident_type_detail   = $request['accident_type_detail'] ?? null;
        $this->description            = $request['description'] ?? null;
        $this->damage_cost            = $request['damage_cost'] ?? null;
        $this->resolved_date          = $request['resolved_date'] ?? null;
        $this->status                 = $request['status'];
        $this->driver_status          = $request['driver_status'];
        $this->motor_mate_status      = $request['motor_mate_status'];
        $this->content                = $request['content'] ?? null;
        $this->damage_extend          = $request['damage_extend'] ?? null;
        $this->is_fatality            = $request['is_fatality'];
        $this->fatality_type          = $request['fatality_type'] ?? null;
        $this->accident_nature_detail = $request['accident_nature_detail'] ?? null;
        $this->is_damage_asset        = $request['is_damage_asset'];
        $this->is_injury              = $request['is_injury'];
        $this->created_by             = Auth::id();
        $this->authority              = $request['authority'];
    }

    public static function fromRequest($request)
    {
        return new self($request);
    }

    public function toArray()
    {
        return [
            'waybill_id'             => $this->waybill_id,
            'vehicle_id'             => $this->vehicle_id,
            'accident_date'          => $this->accident_date,
            'accident_location'      => $this->accident_location,
            'accident_location_lat'  => $this->accident_location_lat,
            'accident_location_lng'  => $this->accident_location_lng,
            'accident_type'          => $this->accident_type,
            'accident_type_detail'   => $this->accident_type_detail,
            'description'            => $this->description,
            'damage_cost'            => $this->damage_cost,
            'resolved_date'          => $this->resolved_date,
            'status'                 => $this->status,
            'driver_status'          => $this->driver_status,
            'motor_mate_status'      => $this->motor_mate_status,
            'content'                => $this->content,
            'damage_extend'          => $this->damage_extend,
            'is_fatality'            => $this->is_fatality,
            'fatality_type'          => $this->fatality_type,
            'accident_nature_detail' => $this->accident_nature_detail,
            'is_damage_asset'        => $this->is_damage_asset,
            'is_injury'              => $this->is_injury,
            'authority'              => $this->authority,
            'created_by'             => $this->created_by,
        ];
    }
}
