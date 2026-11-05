<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DriverDetail>
 */
class DriverDetailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'phone_number' => $this->faker->phoneNumber(),
            'date_of_birth' => $this->faker->date(),
            'national_id_passport_number' => $this->faker->unique()->numerify('##########'),
            'date_employed' => $this->faker->date(),
            'drivers_license_number' => $this->faker->unique()->numerify('DL#####'),
            'license_category_class' => 'B',
            'license_expiry_date' => $this->faker->dateTimeBetween('+1 year', '+5 years'),
            'emergency_contact_name' => $this->faker->name(),
            'emergency_contact_phone_number' => $this->faker->phoneNumber(),
            'status' => $this->faker->randomElement([0,1]),
            'address' => $this->faker->streetAddress() . ', ' . $this->faker->city(),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function ($driver) {
            $driver->file()->create([
                'name' => 'avatar.png',
                'path' => 'users/avatar.png',
                'type' => 'image'
            ]);
        });
    }
}
