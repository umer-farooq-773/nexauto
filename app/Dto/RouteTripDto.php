<?php

namespace App\Dto;

class RouteTripDto
{
    public readonly string $trip_name;
    public readonly string $origin;
    public readonly string $destination;
    public readonly ?string $distance_km;
    public readonly ?string $estimated_duration;
    public readonly ?string $description;
    public readonly bool $status;

    public function __construct($request)
    {
        $this->trip_name          = $request['trip_name'];
        $this->origin             = $request['origin'];
        $this->destination        = $request['destination'];
        $this->distance_km        = $request['distance_km'] ?? null;
        $this->estimated_duration = $request['estimated_duration'] ?? null;
        $this->description        = $request['description'] ?? null;
        $this->status             = $request['status'];
    }

    public static function fromRequest($request)
    {
        return new self($request);
    }

    public function toArray()
    {
        return [
            'trip_name'          => $this->trip_name,
            'origin'             => $this->origin,
            'destination'        => $this->destination,
            'distance_km'        => $this->distance_km,
            'estimated_duration' => $this->estimated_duration,
            'description'        => $this->description,
            'status'             => $this->status,
        ];
    }
}
