<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Vehicle;

class VehicleMaintenanceFactory extends Factory
{
    public function definition(): array
    {
        $types = ['Oil Change', 'Tire Replacement', 'Brake Service', 'Engine Overhaul', 'Battery Replacement', 'Transmission Service', 'Coolant Flush', 'Air Filter Replacement'];
        $maintDate = $this->faker->dateTimeBetween('-1 year', 'now');

        return [
            'vehicle_id'            => Vehicle::inRandomOrder()->value('id'),
            'maintenance_type'      => $this->faker->randomElement($types),
            'maintenance_date'      => $maintDate,
            'next_maintenance_date' => $this->faker->optional()->dateTimeBetween($maintDate, '+6 months'),
            'cost'                  => $this->faker->optional()->randomFloat(2, 500, 150000),
            'service_provider'      => $this->faker->optional()->company(),
            'description'           => $this->faker->optional()->sentence(),
            'status'                => $this->faker->randomElement(['0', '1', '2']),
        ];
    }
}
