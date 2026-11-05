<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DriverMateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'first_name'                     => $this->faker->firstName(),
            'last_name'                      => $this->faker->lastName(),
            'address'                        => $this->faker->streetAddress() . ', ' . $this->faker->city(),
            'phone_number'                   => $this->faker->phoneNumber(),
            'date_of_birth'                  => $this->faker->date(),
            'national_id_passport_number'    => $this->faker->unique()->numerify('DM##########'),
            'date_employed'                  => $this->faker->date(),
            'emergency_contact_name'         => $this->faker->name(),
            'emergency_contact_phone_number' => $this->faker->phoneNumber(),
            'status'                         => $this->faker->randomElement([0, 1]),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function ($driverMate) {
            $driverMate->file()->create([
                'name' => 'avatar.png',
                'path' => 'users/avatar.png',
                'type' => 'image',
            ]);
        });
    }
}
