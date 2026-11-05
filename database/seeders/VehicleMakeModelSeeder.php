<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\VehicleMakeModel;

class VehicleMakeModelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vehicleMakeModels = [
            ['name' => 'Volkswagen Voyage', 'status' => 1],
            ['name' => 'Toyota Corolla', 'status' => 1],
            ['name' => 'Hino 500 Series', 'status' => 1],
            ['name' => 'Isuzu NPR', 'status' => 1],
            ['name' => 'Caterpillar Excavator', 'status' => 1],
            ['name' => 'JCB Backhoe Loader', 'status' => 1],
        ];

        foreach ($vehicleMakeModels as $vehicleMakeModel) {
            VehicleMakeModel::create($vehicleMakeModel);
        }
    }
}
