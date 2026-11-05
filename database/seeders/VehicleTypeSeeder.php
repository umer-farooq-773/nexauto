<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\VehicleType;

class VehicleTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vehicleType = [
            ['name' => 'Fleet light Vehicles', 'status' => 1],
            ['name' => 'Trucks / HGVS', 'status' => 1],
            ['name' => 'Heavy Equipment & Machinery', 'status' => 1],
            ['name' => 'Plant & Industrial Assets', 'status' => 1],
        ];

        foreach ($vehicleType as $vehicleType) {
            VehicleType::create($vehicleType);
        }
    }
}
