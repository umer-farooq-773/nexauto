<?php

namespace App\Dto;

use App\Models\Waybill;
use Illuminate\Support\Facades\Auth;

class IllegalShortageDto
{
    public readonly string $driver_id;
    public readonly ?string $vehicle_id;
    public readonly string $waybill_id;
    public readonly float $declared_quantity;
    public readonly float $observed_quantity;
    public readonly string $shortage_date;
    public readonly string $shortage_location;
    public readonly ?string $shortage_location_lat;
    public readonly ?string $shortage_location_lng;
    public readonly string $shortage_type;
    public readonly float $shortage_quantity;
    public readonly ?string $cargo_description;
    public readonly string $severity;
    public readonly ?string $action_taken;
    public readonly ?string $remarks;
    public readonly string $status;
    public readonly ?string $created_by;

    public function __construct($request)
    {
        $waybill = Waybill::find($request['waybill_id'] ?? null);
        $declaredQuantity = (float) ($waybill?->quantity ?? 0);
        $observedQuantity = (float) $request['observed_quantity'];

        $this->driver_id             = $request['driver_id'] ?? $waybill?->driver_id;
        $this->vehicle_id            = $request['vehicle_id'] ?? $waybill?->vehicle_id;
        $this->waybill_id            = $request['waybill_id'];
        $this->declared_quantity     = $declaredQuantity;
        $this->observed_quantity     = $observedQuantity;
        $this->shortage_date         = $request['shortage_date'];
        $this->shortage_location     = $request['shortage_location'];
        $this->shortage_location_lat = $request['shortage_location_lat'] ?? null;
        $this->shortage_location_lng = $request['shortage_location_lng'] ?? null;
        $this->shortage_type         = $request['shortage_type'];
        $this->shortage_quantity     = abs($declaredQuantity - $observedQuantity);
        $this->cargo_description     = $request['cargo_description'] ?? null;
        $this->severity              = $request['severity'];
        $this->action_taken          = $request['action_taken'] ?? null;
        $this->remarks               = $request['remarks'] ?? null;
        $this->status                = (string) ($request['status'] ?? '0');
        $this->created_by            = $request['created_by'] ?? Auth::id();
    }

    public static function fromRequest($request)
    {
        return new self($request);
    }

    public function toArray()
    {
        return [
            'driver_id'             => $this->driver_id,
            'vehicle_id'            => $this->vehicle_id,
            'waybill_id'            => $this->waybill_id,
            'declared_quantity'     => $this->declared_quantity,
            'observed_quantity'     => $this->observed_quantity,
            'shortage_date'         => $this->shortage_date,
            'shortage_location'     => $this->shortage_location,
            'shortage_location_lat' => $this->shortage_location_lat,
            'shortage_location_lng' => $this->shortage_location_lng,
            'shortage_type'         => $this->shortage_type,
            'shortage_quantity'     => $this->shortage_quantity,
            'cargo_description'     => $this->cargo_description,
            'severity'              => $this->severity,
            'action_taken'          => $this->action_taken,
            'remarks'               => $this->remarks,
            'status'                => $this->status,
            'created_by'            => $this->created_by,
        ];
    }
}
