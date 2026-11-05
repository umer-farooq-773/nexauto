<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Waybill;

class WaybillSeeder extends Seeder
{
    public function run(): void
    {
        Waybill::factory(100)->create();
    }
}
