<?php

use App\Constants\Constants;
use App\Models\AuthSidebarMenu;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create permissions
        $permissions = [
            ['name' => 'illegal_shortage',        'title' => 'View illegal shortage records', 'category' => 'Driver Behavior'],
            ['name' => 'add_illegal_shortage',    'title' => 'Add new illegal shortage record', 'category' => 'Driver Behavior'],
            ['name' => 'view_illegal_shortage',   'title' => 'View illegal shortage details', 'category' => 'Driver Behavior'],
            ['name' => 'edit_illegal_shortage',   'title' => 'Edit illegal shortage record', 'category' => 'Driver Behavior'],
            ['name' => 'delete_illegal_shortage', 'title' => 'Delete illegal shortage record', 'category' => 'Driver Behavior'],
        ];

        foreach ($permissions as $permData) {
            $permission = Permission::where('name', $permData['name'])->where('guard_name', 'web')->first();
            if (!$permission) {
                $permission = new Permission();
                $permission->name = $permData['name'];
                $permission->guard_name = 'web';
                $permission->title = $permData['title'];
                $permission->category = $permData['category'];
                $permission->save();
            }

            // Assign to Superadmin, Admin, and Champs Management
            $roles = Role::whereIn('name', [
                Constants::SUPERADMIN,
                Constants::ADMIN,
                Constants::CHAMPS_MANAGEMENT,
                Constants::CLIENT_MANAGEMENT,
                Constants::CLIENT_DIRECTORS,
            ])->get();

            foreach ($roles as $role) {
                if (!$role->hasPermissionTo($permission->name)) {
                    $role->givePermissionTo($permission);
                }
            }
        }

        // 2. Add sidebar menu under Driver Behavior parent
        if (Schema::hasTable('auth_sidebar_menus')) {
            $parent = AuthSidebarMenu::where('name', 'Driver Behavior')
                ->orWhere('main_permission_key', 'driver_behavior')
                ->first();

            $existing = AuthSidebarMenu::where('route', 'illegal_shortage.index')->first();

            if (!$existing) {
                $menuId = (string) Str::uuid();
                DB::table('auth_sidebar_menus')->insert([
                    'id'                  => $menuId,
                    'parent_id'           => $parent?->id,
                    'name'                => 'Illegal Shortage',
                    'icon'                => 'fa-angle-double-right',
                    'route'               => 'illegal_shortage.index',
                    'route_param'         => '',
                    'sort_order'          => 3,
                    'main_permission_key' => 'illegal_shortage',
                    'status'              => 1,
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ]);

                if (Schema::hasTable('auth_sidebar_menu_permissions')) {
                    $mainPerm = Permission::where('name', 'illegal_shortage')->first();
                    if ($mainPerm) {
                        DB::table('auth_sidebar_menu_permissions')->insert([
                            'id'                    => (string) Str::uuid(),
                            'auth_sidebar_menu_id'  => $menuId,
                            'permission_id'         => $mainPerm->id,
                            'created_at'            => now(),
                            'updated_at'            => now(),
                        ]);
                    }
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('auth_sidebar_menus')) {
            DB::table('auth_sidebar_menus')->where('route', 'illegal_shortage.index')->delete();
        }

        $permNames = [
            'illegal_shortage',
            'add_illegal_shortage',
            'view_illegal_shortage',
            'edit_illegal_shortage',
            'delete_illegal_shortage',
        ];

        Permission::whereIn('name', $permNames)->delete();
    }
};
