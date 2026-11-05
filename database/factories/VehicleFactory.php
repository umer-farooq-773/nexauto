<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\VehicleMakeModel;
use App\Models\VehicleType;

class VehicleFactory extends Factory
{
    public function definition(): array
    {
        $lastService = $this->faker->dateTimeBetween('-1 year', 'now');

        return [
            'fleet_no' => 'FL-' . $this->faker->unique()->numberBetween(1000,9999),

            'manager_first_name' => $this->faker->firstName(),
            'manager_last_name' => $this->faker->lastName(),
            'manager_email' => $this->faker->unique()->safeEmail(),

            'owner_first_name' => $this->faker->firstName(),
            'owner_last_name' => $this->faker->lastName(),

            'vehicle_make_model_id' => VehicleMakeModel::inRandomOrder()->value('id'),
            'vehicle_type_id' => VehicleType::inRandomOrder()->value('id'),

            'truck_no' => 'TRK-' . $this->faker->unique()->numberBetween(100000,999999),

            'last_service_date' => $lastService,
            'next_service_due_date' => $this->faker->dateTimeBetween($lastService, '+6 months'),

            'insurance_expiry_date' => $this->faker->dateTimeBetween('now', '+2 years'),
            'vehicle_license_expiry_date' => $this->faker->dateTimeBetween('now', '+2 years'),

            'base_site' => $this->faker->city(),

            'status' => $this->faker->randomElement([0,1,2]),
        ];
    }
}