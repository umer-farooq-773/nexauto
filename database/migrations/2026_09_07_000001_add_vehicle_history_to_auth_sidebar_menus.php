<?php

use App\Models\AuthSidebarMenu;
use App\Models\Permission;
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
        if (Schema::hasTable('auth_sidebar_menus')) {
            $existing = AuthSidebarMenu::where('route', 'vehicle_history.index')->first();

            if (!$existing) {
                $menuId = (string) Str::uuid();
                DB::table('auth_sidebar_menus')->insert([
                    'id'                  => $menuId,
                    'parent_id'           => null,
                    'name'                => 'Vehicle History',
                    'icon'                => 'fa-file-audio',
                    'route'               => 'vehicle_history.index',
                    'sort_order'          => 4,
                    'main_permission_key' => 'vehicle_management',
                    'status'              => 1,
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ]);

                // Attach permission with explicit UUID if table exists
                if (Schema::hasTable('auth_sidebar_menu_permissions')) {
                    $permission = Permission::where('name', 'vehicle_management')->first();
                    if ($permission) {
                        DB::table('auth_sidebar_menu_permissions')->insert([
                            'id'                    => (string) Str::uuid(),
                            'auth_sidebar_menu_id'  => $menuId,
                            'permission_id'         => $permission->id,
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
            DB::table('auth_sidebar_menus')->where('route', 'vehicle_history.index')->delete();
        }
    }
};
