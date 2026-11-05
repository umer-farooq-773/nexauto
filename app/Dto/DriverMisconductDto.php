<?php

namespace App\Dto;

class DriverMisconductDto
{
    public readonly string $driver_id;
    public readonly ?string $vehicle_id;
    public readonly string $waybill_id;
    public readonly string $misconduct_date;
    public readonly string $misconduct_location;
    public readonly ?string $misconduct_location_lat;
    public readonly ?string $misconduct_location_lng;
    public readonly string $misconduct_type;
    public readonly string $severity;
    public readonly ?string $action_taken;
    public readonly ?string $remarks;
    public readonly string $status;
    public readonly ?string $created_by;

    public function __construct($request)
    {
        $waybill = \App\Models\Waybill::find($request['waybill_id']);

        $this->driver_id               = $request['driver_id'] ?? $waybill?->driver_id;
        $this->vehicle_id              = $request['vehicle_id'] ?? $waybill?->vehicle_id;
        $this->waybill_id              = $request['waybill_id'];
        $this->misconduct_date         = $request['misconduct_date'];
        $this->misconduct_location     = $request['misconduct_location'];
        $this->misconduct_location_lat = $request['misconduct_location_lat'] ?? null;
        $this->misconduct_location_lng = $request['misconduct_location_lng'] ?? null;
        $this->misconduct_type         = $request['misconduct_type'];
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
            'misconduct_date' => $this->misconduct_date,
            'misconduct_location' => $this->misconduct_location,
            'misconduct_location_lat' => $this->misconduct_location_lat,
            'misconduct_location_lng' => $this->misconduct_location_lng,
            'misconduct_type' => $this->misconduct_type,
            'severity' => $this->severity,
            'action_taken' => $this->action_taken,
            'remarks' => $this->remarks,
            'status' => $this->status,
            'created_by' => $this->created_by,
        ];
    }
}
