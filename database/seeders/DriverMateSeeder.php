<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DriverMate;

class DriverMateSeeder extends Seeder
{
    public function run(): void
    {
        DriverMate::factory(100)->create();
    }
}
