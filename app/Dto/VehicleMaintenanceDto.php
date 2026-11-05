<?php

namespace App\Dto;

class VehicleMaintenanceDto
{
    public readonly string $vehicle_id;
    public readonly array $maintenance_type_ids;
    public readonly string $maintenance_date;
    public readonly ?string $next_maintenance_date;
    public readonly ?string $expected_completion_date;
    public readonly ?string $odometer;
    public readonly ?string $cost;
    public readonly ?string $service_provider;
    public readonly ?string $priority;
    public readonly ?string $description;
    public readonly string $status;
    public readonly array $maintenance_files;

    public function __construct($request)
    {
        $this->vehicle_id               = $request['vehicle_id'];
        $this->maintenance_type_ids     = $request['maintenance_type_ids'] ?? [];
        $this->maintenance_date         = $request['maintenance_date'];
        $this->next_maintenance_date    = $request['next_maintenance_date'] ?? null;
        $this->expected_completion_date = $request['expected_completion_date'] ?? null;
        $this->odometer                 = $request['odometer'] ?? null;
        $this->cost                     = $request['cost'] ?? null;
        $this->service_provider         = $request['service_provider'] ?? null;
        $this->priority                 = $request['priority'] ?? 'Routine';
        $this->description              = $request['description'] ?? null;
        $this->status                   = $request['status'];
        $this->maintenance_files        = $request['maintenance_files'] ?? [];
    }

    public static function fromRequest($request)
    {
        return new self($request);
    }

    public function toArray()
    {
        return [
            'vehicle_id'               => $this->vehicle_id,
            'maintenance_date'         => $this->maintenance_date,
            'next_maintenance_date'    => $this->next_maintenance_date,
            'expected_completion_date' => $this->expected_completion_date,
            'odometer'                 => $this->odometer,
            'cost'                     => $this->cost,
            'service_provider'         => $this->service_provider,
            'priority'                 => $this->priority,
            'description'              => $this->description,
            'status'                   => $this->status,
        ];
    }
}
