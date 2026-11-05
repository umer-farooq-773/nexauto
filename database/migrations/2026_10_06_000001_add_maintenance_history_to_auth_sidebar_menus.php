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
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create permission
        $permData = [
            'name'     => 'maintenance_history',
            'title'    => 'View maintenance history',
            'category' => 'Vehicle Management',
        ];

        $permission = Permission::where('name', $permData['name'])->where('guard_name', 'web')->first();
        if (!$permission) {
            $nextId = (DB::table('permissions')->max('id') ?? 0) + 1;
            DB::table('permissions')->insert([
                'id'         => $nextId,
                'name'       => $permData['name'],
                'guard_name' => 'web',
                'title'      => $permData['title'],
                'category'   => $permData['category'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
            $permission = Permission::where('name', $permData['name'])->first();
        } else {
            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        }

        // Assign to Super Admin, Admin, and other management roles
        $roles = Role::whereIn('name', [
            'super_admin',
            'admin',
            'champs_management',
            'client_management',
            'client_directors',
            'field_worker',
            'security_operative',
        ])->get();

        foreach ($roles as $role) {
            if (!$role->hasPermissionTo($permission->name)) {
                $role->givePermissionTo($permission);
            }
        }

        // 2. Add sidebar menu under Asset Management parent
        if (Schema::hasTable('auth_sidebar_menus')) {
            // Find parent Asset Management menu
            $parent = AuthSidebarMenu::where('name', 'language.assets_enumeration')
                ->where('route', 'vehicle.index')
                ->first();

            if (!$parent) {
                $parent = AuthSidebarMenu::where('main_permission_key', 'vehicle_management')
                    ->whereNull('parent_id')
                    ->first();
            }

            $existing = AuthSidebarMenu::where('route', 'maintenance.history')->first();

            if (!$existing && $parent) {
                $menuId = (string) Str::uuid();
                DB::table('auth_sidebar_menus')->insert([
                    'id'                  => $menuId,
                    'parent_id'           => $parent->id,
                    'name'                => 'Maintenance History',
                    'icon'                => 'fa-angle-double-right',
                    'route'               => 'maintenance.history',
                    'route_param'         => '',
                    'sort_order'          => 8,
                    'main_permission_key' => 'maintenance_management',
                    'status'              => 1,
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ]);

                if (Schema::hasTable('auth_sidebar_menu_permissions')) {
                    $mainPerm = Permission::where('name', 'maintenance_management')->first() 
                        ?? Permission::where('name', 'maintenance_history')->first();
                    
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('auth_sidebar_menus')) {
            DB::table('auth_sidebar_menus')->where('route', 'maintenance.history')->delete();
        }

        Permission::where('name', 'maintenance_history')->delete();
    }
};
