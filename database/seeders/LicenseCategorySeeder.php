<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\LicenseCategory;

class LicenseCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'LTV (Light Transport Vehicle)', 'status' => 1],
            ['name' => 'HTV (Heavy Transport Vehicle)', 'status' => 1],
            ['name' => 'PSV (Public Service Vehicle)', 'status' => 1],
            ['name' => 'Motorcycle / Car', 'status' => 1],
            ['name' => 'Heavy Machinery Operator License', 'status' => 1],
            ['name' => 'Tractor (Commercial)', 'status' => 1],
            ['name' => 'LTV / HTV Combined', 'status' => 1],
        ];

        foreach ($categories as $category) {
            LicenseCategory::create($category);
        }
    }
}
