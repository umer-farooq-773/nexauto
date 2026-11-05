<?php

namespace Database\Seeders;

use App\Models\GpsDeviceType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GpsDeviceTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $devices = [
            [
                'name' => 'Concox GT06N',
                'wialon_hw_type_id' => 600002472,
                'status' => 1,
            ],
            [
                'name' => 'Concox GT06E',
                'wialon_hw_type_id' => 600001828,
                'status' => 1,
            ],
            [
                'name' => 'Teltonika FMB920',
                'wialon_hw_type_id' => 600002249,
                'status' => 1,
            ],
            [
                'name' => 'Teltonika FMC130',
                'wialon_hw_type_id' => 600001805,
                'status' => 1,
            ],
        ];

        foreach ($devices as $device) {
            GpsDeviceType::updateOrCreate(
                ['name' => $device['name']],
                [
                    'wialon_hw_type_id' => $device['wialon_hw_type_id'],
                    'status' => $device['status'],
                ]
            );
        }
    }
}
