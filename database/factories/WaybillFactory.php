<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Vehicle;
use App\Models\DriverDetail;
use App\Models\RouteTrip;
use App\Models\Status;

class WaybillFactory extends Factory
{
    public function definition(): array
    {
        $dispatch = $this->faker->dateTimeBetween('-6 months', 'now');

        return [
            'waybill_number'    => 'WB-' . strtoupper($this->faker->unique()->bothify('??####')),
            'vehicle_id'        => Vehicle::inRandomOrder()->value('id'),
            'driver_id'         => DriverDetail::inRandomOrder()->value('id'),
            'status_id'         => Status::inRandomOrder()->value('id'),
            'route_trip_id'     => RouteTrip::inRandomOrder()->value('id'),
            'dispatch_date'     => $dispatch,
            'delivery_date'     => $this->faker->optional()->dateTimeBetween($dispatch, '+30 days'),
            'cargo_description' => $this->faker->optional()->words(3, true),
            'cargo_weight_kg'   => $this->faker->optional()->randomFloat(2, 100, 20000),
            'notes'             => $this->faker->optional()->sentence(),
        ];
    }
}
