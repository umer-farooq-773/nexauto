<?php

namespace App\Dto;

class IllegalHaulageDto
{
    public readonly string $driver_id;
    public readonly ?string $vehicle_id;
    public readonly string $waybill_id;
    public readonly float $declared_quantity;
    public readonly float $observed_quantity;
    public readonly float $difference_quantity;
    public readonly string $haulage_date;
    public readonly string $haulage_location;
    public readonly ?string $haulage_location_lat;
    public readonly ?string $haulage_location_lng;
    public readonly string $haulage_type;
    public readonly ?string $cargo_description;
    public readonly string $severity;
    public readonly ?string $action_taken;
    public readonly ?string $remarks;
    public readonly string $status;
    public readonly ?string $created_by;

    public function __construct($request)
    {
        $waybill = \App\Models\Waybill::find($request['waybill_id']);
        $declaredQuantity = (float) ($waybill?->quantity ?? 0);
        $observedQuantity = (float) $request['observed_quantity'];

        $this->driver_id            = $request['driver_id'] ?? $waybill?->driver_id;
        $this->vehicle_id           = $request['vehicle_id'] ?? $waybill?->vehicle_id;
        $this->waybill_id           = $request['waybill_id'];
        $this->declared_quantity    = $declaredQuantity;
        $this->observed_quantity    = $observedQuantity;
        $this->difference_quantity   = abs($declaredQuantity - $observedQuantity);
        $this->haulage_date         = $request['haulage_date'];
        $this->haulage_location     = $request['haulage_location'];
        $this->haulage_location_lat = $request['haulage_location_lat'] ?? null;
        $this->haulage_location_lng = $request['haulage_location_lng'] ?? null;
        $this->haulage_type         = $request['haulage_type'];
        $this->cargo_description = $request['cargo_description'] ?? null;
        $this->severity = $request['severity'];
        $this->action_taken = $request['action_taken'] ?? null;
        $this->remarks = $request['remarks'] ?? null;
        $this->status = $request['status'];
        $this->created_by = $request['created_by'] ?? \Illuminate\Support\Facades\Auth::id();
    }

    public static function fromRequest($request)
    {
        return new self($request);
    }

    public function toArray()
    {
        return [
            'driver_id' => $this->driver_id,
            'vehicle_id' => $this->vehicle_id,
            'waybill_id' => $this->waybill_id,
            'declared_quantity' => $this->declared_quantity,
            'observed_quantity' => $this->observed_quantity,
            'difference_quantity' => $this->difference_quantity,
            'haulage_date' => $this->haulage_date,
            'haulage_location' => $this->haulage_location,
            'haulage_location_lat' => $this->haulage_location_lat,
            'haulage_location_lng' => $this->haulage_location_lng,
            'haulage_type' => $this->haulage_type,
            'cargo_description' => $this->cargo_description,
            'severity' => $this->severity,
            'action_taken' => $this->action_taken,
            'remarks' => $this->remarks,
            'status' => $this->status,
            'created_by' => $this->created_by,
        ];
    }
}
