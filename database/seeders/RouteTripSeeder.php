<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RouteTrip;

class RouteTripSeeder extends Seeder
{
    public function run(): void
    {
        RouteTrip::factory(100)->create();
    }
}
