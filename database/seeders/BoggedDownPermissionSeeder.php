<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class BoggedDownPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $legacyPermissions = [
            'vehicle_bugged_down' => 'bogged_down',
            'add_vehicle_bugged_down' => 'add_bogged_down',
            'view_vehicle_bugged_down' => 'view_bogged_down',
            'edit_vehicle_bugged_down' => 'edit_bogged_down',
            'delete_vehicle_bugged_down' => 'delete_bogged_down',
        ];

        $definitions = [
            'bogged_down' => 'View bogged down incidents',
            'add_bogged_down' => 'Add new bogged down incident',
            'view_bogged_down' => 'View bogged down incident details',
            'edit_bogged_down' => 'Edit bogged down incident',
            'delete_bogged_down' => 'Delete bogged down incident',
        ];

        foreach ($legacyPermissions as $legacyName => $newName) {
            if (
                Permission::where('name', $legacyName)->exists() &&
                !Permission::where('name', $newName)->exists()
            ) {
                Permission::where('name', $legacyName)->update(['name' => $newName]);
            }
        }

        foreach ($definitions as $name => $title) {
            Permission::updateOrCreate(
                ['name' => $name],
                ['title' => $title, 'category' => 'Incident & Intervention']
            );
        }
    }
}
