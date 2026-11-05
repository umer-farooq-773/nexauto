<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AuthSidebarMenu;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class IncidentInterventionMenuSeeder extends Seeder
{
    public function run(): void
    {
        $parent = AuthSidebarMenu::where('main_permission_key', 'incident_intervention')
            ->whereNull('parent_id')
            ->first();

        if (!$parent) return;

        $incidentReport = AuthSidebarMenu::firstOrNew([
            'route' => 'incident_report.index',
        ]);
        $incidentReport->fill([
            'parent_id'           => null,
            'name'                => 'Incident Report',
            'icon'                => 'fa-file-lines',
            'sort_order'          => 14,
            'main_permission_key' => 'incident_intervention',
            'status'              => 1,
            'route_param'         => '',
        ]);
        $incidentReport->save();

        $permission = Permission::where('name', 'incident_intervention')->first();
        if ($permission && !$incidentReport->permissions()->where('permission_id', $permission->id)->exists()) {
            $incidentReport->permissions()->newPivotStatement()->insert([
                'id'                   => (string) Str::uuid(),
                'auth_sidebar_menu_id' => $incidentReport->id,
                'permission_id'        => $permission->id,
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);
        }

        $legacyVehicleBuggedDown = AuthSidebarMenu::where('parent_id', $parent->id)
            ->whereIn('name', ['vehcle bugged down ', 'Vehicle Bugged Down', 'Bogged Down'])
            ->first();

        if ($legacyVehicleBuggedDown) {
            $legacyVehicleBuggedDown->update([
                'name'                => 'Bogged Down',
                'route'               => 'vehicle_bogged_down.index',
                'route_param'         => '',
                'sort_order'          => 5,
                'main_permission_key' => 'bogged_down',
                'status'              => 1,
            ]);
        }

        $children = [
            ['name' => 'Impoundment',      'route' => 'impoundment.index',          'sort_order' => 1],
            ['name' => 'Vehicle Breakdown', 'route' => 'vehicle_breakdown.index',   'sort_order' => 2],
            ['name' => 'Illegal Detention', 'route' => 'illegal_detention.index',   'sort_order' => 3],
            ['name' => 'Accident',          'route' => 'accident.index',            'sort_order' => 4],
            ['name' => 'Bogged Down',       'route' => 'vehicle_bogged_down.index', 'sort_order' => 5],
        ];

        foreach ($children as $data) {
            $child = AuthSidebarMenu::firstOrCreate(
                ['parent_id' => $parent->id, 'name' => $data['name']],
                [
                    'icon'                => 'fa-angle-double-right',
                    'route'               => $data['route'],
                    'route_param'         => '',
                    'sort_order'          => $data['sort_order'],
                    'main_permission_key' => match ($data['route']) {
                        'vehicle_bogged_down.index' => 'bogged_down',
                        default => 'incident_intervention',
                    },
                    'status'              => 1,
                ]
            );

            $permissionName = $child->main_permission_key ?: 'incident_intervention';
            $permission = Permission::where('name', $permissionName)->first();
            if ($permission && !$child->permissions()->where('permission_id', $permission->id)->exists()) {
                $child->permissions()->newPivotStatement()->insert([
                    'id'                   => (string) Str::uuid(),
                    'auth_sidebar_menu_id' => $child->id,
                    'permission_id'        => $permission->id,
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ]);
            }
        }

        // Assign incident_intervention permission to all roles
        $permission = Permission::where('name', 'incident_intervention')->first();
        if ($permission) {
            foreach (Role::all() as $role) {
                if (!$role->hasPermissionTo($permission)) {
                    $role->givePermissionTo($permission);
                }
            }
        }

        Cache::flush();
    }
}
