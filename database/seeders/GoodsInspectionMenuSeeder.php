<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AuthSidebarMenu;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class GoodsInspectionMenuSeeder extends Seeder
{
    public function run(): void
    {
        // Create the main Goods Inspection menu
        $goods_inspection = AuthSidebarMenu::firstOrCreate(
            [
                'name' => 'Goods Inspection',
                'parent_id' => null,
            ],
            [
                'icon' => 'fa-clipboard-check',
                'route' => 'goods_inspection.index',
                'route_param' => '',
                'sort_order' => 5, // Insert after Incident & Intervention (3)
                'main_permission_key' => 'goods_inspection',
                'status' => 1,
            ]
        );

        // Create permission if not exists
        $permission = Permission::firstOrCreate([
            'name' => 'goods_inspection'
        ], [
            'title' => 'Goods Inspection',
            'category' => 'Goods Inspection'
        ]);

        // Attach permission to the menu using pivot table
        if ($permission && !$goods_inspection->permissions()->where('permission_id', $permission->id)->exists()) {
            $goods_inspection->permissions()->newPivotStatement()->insert([
                'id'                   => (string) Str::uuid(),
                'auth_sidebar_menu_id' => $goods_inspection->id,
                'permission_id'        => $permission->id,
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);
        }

        // Assign permission to all roles
        foreach (Role::all() as $role) {
            if (!$role->hasPermissionTo($permission)) {
                $role->givePermissionTo($permission);
            }
        }

        // Adjust sort orders for proper sidebar ordering:
        // 1. Dashboard (sort_order 1) - unchanged
        // 2. Vehicle Management (sort_order 2) - unchanged
        // 3. Incident & Intervention (sort_order 3) - unchanged
        // 4. Waybill Management (sort_order 4) - unchanged
        // 5. Goods Inspection (sort_order 5) - NEW
        // 6. Vehicle Tracking (sort_order 6)
        // 7. Assets Enumeration (sort_order 7)
        // 8. Reports & Analytics (sort_order 8)
        // 9. Staff Management (sort_order 9)
        // 10. Configuration (sort_order 10)
        // 11. Communication (sort_order 11)
        // 12. Settings (sort_order 12)
        // 13. Blog (sort_order 13)

        // No need to update other menus as they are already set correctly in AuthSidebarMenuSeeder
    }
}
