<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VehicleMaintenance;

class VehicleMaintenanceSeeder extends Seeder
{
    public function run(): void
    {
        VehicleMaintenance::factory(100)->create();
    }
}
