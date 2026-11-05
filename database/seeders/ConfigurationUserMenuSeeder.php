<?php

namespace Database\Seeders;

use App\Models\AuthSidebarMenu;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ConfigurationUserMenuSeeder extends Seeder
{
    public function run(): void
    {
        $configurationMenu = AuthSidebarMenu::query()
            ->whereNull('parent_id')
            ->where('main_permission_key', 'website_setting')
            ->first();

        if (! $configurationMenu) {
            return;
        }

        $userMenu = AuthSidebarMenu::firstOrCreate(
            [
                'parent_id' => $configurationMenu->id,
                'route' => 'users.index',
                'route_param' => 'user',
            ],
            [
                'name' => 'User',
                'icon' => 'fa-angle-double-right',
                'sort_order' => 2,
                'main_permission_key' => 'all_user',
                'status' => 1,
            ]
        );

        $permission = Permission::where('name', 'all_user')->first();

        if ($permission && ! $userMenu->permissions()->where('permission_id', $permission->id)->exists()) {
            $userMenu->permissions()->newPivotStatement()->insert([
                'id' => (string) Str::uuid(),
                'auth_sidebar_menu_id' => $userMenu->id,
                'permission_id' => $permission->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
