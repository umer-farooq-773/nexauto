<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StandardVehicleTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This seeder populates the vehicle_types table with standard
     * Wialon-compatible vehicle types that will be synced to Wialon units.
     */
    public function run(): void
    {
        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Clear existing vehicle types
        DB::table('vehicle_types')->truncate();
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Standard Wialon vehicle types
        $vehicleTypes = [
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Truck',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Car',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Bus',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Van',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Motorcycle',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('vehicle_types')->insert($vehicleTypes);

        $this->command->info('✓ Standard vehicle types seeded successfully!');
        $this->command->info('✓ Added: Truck, Car, Bus, Van, Motorcycle');
    }
}

