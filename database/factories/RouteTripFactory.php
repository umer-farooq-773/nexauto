<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class RouteTripFactory extends Factory
{
    public function definition(): array
    {
        $cities = ['Karachi', 'Lahore', 'Islamabad', 'Peshawar', 'Quetta', 'Multan', 'Faisalabad', 'Rawalpindi', 'Hyderabad', 'Sialkot'];
        $origin = $this->faker->randomElement($cities);
        $destination = $this->faker->randomElement(array_diff($cities, [$origin]));

        return [
            'trip_name'          => $origin . ' to ' . $destination . ' Route',
            'origin'             => $origin,
            'destination'        => $destination,
            'distance_km'        => $this->faker->randomFloat(2, 50, 1500),
            'estimated_duration' => $this->faker->numberBetween(1, 24) . ' hours',
            'description'        => $this->faker->optional()->sentence(),
            'status'             => $this->faker->randomElement([0, 1]),
        ];
    }
}
