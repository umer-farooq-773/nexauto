<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VehicleMaintenanceTypeSeeder extends Seeder
{
    public function run()
    {
        $maintenanceTypes = [
            'Preventive Maintenance',
            'Corrective Maintenance',
            'Predictive Maintenance',
            'Engine Maintenance',
            'Transmission Maintenance',
            'Brake System',
            'Suspension System',
            'Steering System',
            'Tire & Wheel Maintenance',
            'Electrical System',
            'Cooling System',
            'Fuel System',
            'Air Conditioning (HVAC)',
            'Hydraulic System',
            'Trailer Maintenance',
            'Body & Chassis',
            'Exhaust & Emission',
            'Safety Inspection',
            'Compliance Maintenance',
            'Fleet Inspection',
            'Emergency Repair',
            'Roadside Assistance',
            'Accident Repair',
            'Cleaning & Detailing',
            'Battery Service',
            'Wheel Alignment',
            'Wheel Balancing',
            'Oil Change',
            'Filter Replacement',
            'Greasing & Lubrication',
            'Vehicle Diagnostics',
            'General Service',
            'Major Service',
            'Minor Service',
            'Overhaul',
            'Parts Replacement',
            'Glass & Windshield Repair',
            'Lighting System',
            'GPS & Tracking System',
            'Insurance Inspection',
            'Registration Inspection',
        ];

        foreach ($maintenanceTypes as $type) {
            DB::table('vehicle_maintenance_types')->insert([
                'id' => Str::uuid(),
                'name' => $type,
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
