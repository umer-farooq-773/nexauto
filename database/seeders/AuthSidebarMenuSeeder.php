<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AuthSidebarMenu;
use App\Models\Role;
use App\Models\Permission;

class AuthSidebarMenuSeeder extends Seeder
{
    public function run(): void
    {
        $dashboard = AuthSidebarMenu::create([
            'name' => 'language.dashboard',
            'icon' => 'fa-home',
            'route' => 'auth',
            'sort_order' => 1,
            'main_permission_key' => 'dashboard',
            'status' => 1,
        ]);

        $this->attachPermission($dashboard);

        $vehicle_management = AuthSidebarMenu::create([
            'name' => 'language.assets_enumeration',
            'icon' => 'fa-truck-pickup',
            'route' => 'vehicle.index',
            'sort_order' => 2,
            'main_permission_key' => 'vehicle_management',
            'status' => 1,
        ]);

        $this->attachPermission($vehicle_management);

        $vehicle_child = $vehicle_management->children()->create([
            'name' => 'Dashboard',
            'icon' => 'fa-angle-double-right',
            'route' => 'vehicle.index',
            'route_param' => '',
            'sort_order' => 1,
            'main_permission_key' => 'vehicle_management',
            'status' => 1,
        ]);

        $this->attachPermission($vehicle_child);

        $vehicle_child2 = $vehicle_management->children()->create([
            'name' => 'Assets Enumeration',
            'icon' => 'fa-angle-double-right',
            'route' => 'vehicle.list',
            'route_param' => '',
            'sort_order' => 2,
            'main_permission_key' => 'vehicle_management',
            'status' => 1,
        ]);

        $this->attachPermission($vehicle_child2);

        $vehicle_child3 = $vehicle_management->children()->create([
            'name' => 'Driver Details',
            'icon' => 'fa-angle-double-right',
            'route' => 'driver.index',
            'route_param' => '',
            'sort_order' => 3,
            'main_permission_key' => 'driver_management',
            'status' => 1,
        ]);

        $this->attachPermission($vehicle_child3);

        $vehicle_child4 = $vehicle_management->children()->create([
            'name' => 'Driver Mate',
            'icon' => 'fa-angle-double-right',
            'route' => 'driver_mate.index',
            'route_param' => '',
            'sort_order' => 4,
            'main_permission_key' => 'driver_mate_management',
            'status' => 1,
        ]);

        $this->attachPermission($vehicle_child4);

        $vehicle_child5 = $vehicle_management->children()->create([
            'name' => 'Route & Trip',
            'icon' => 'fa-angle-double-right',
            'route' => 'route_trip.index',
            'route_param' => '',
            'sort_order' => 5,
            'main_permission_key' => 'route_trip_management',
            'status' => 0,
        ]);

        $this->attachPermission($vehicle_child5);



        $vehicle_child7 = $vehicle_management->children()->create([
            'name' => 'Maintenance',
            'icon' => 'fa-angle-double-right',
            'route' => 'maintenance.index',
            'route_param' => '',
            'sort_order' => 7,
            'main_permission_key' => 'maintenance_management',
            'status' => 1,
        ]);

        $this->attachPermission($vehicle_child7);

        $vehicle_child8 = $vehicle_management->children()->create([
            'name' => 'Maintenance History',
            'icon' => 'fa-angle-double-right',
            'route' => 'maintenance.history',
            'route_param' => '',
            'sort_order' => 8,
            'main_permission_key' => 'maintenance_management',
            'status' => 1,
        ]);

        $this->attachPermission($vehicle_child8);

        $incident_intervention = AuthSidebarMenu::create([
            'name' => 'language.incident_intervention',
            'icon' => 'fa-exclamation-circle',
            'route' => null,
            'sort_order' => 4,
            'main_permission_key' => 'incident_intervention',
            'status' => 1,
        ]);

        $this->attachPermission($incident_intervention);

        $incident_intervention_child = $incident_intervention->children()->create([
            'name' => 'Bogged Down',
            'icon' => 'fa-angle-double-right',
            'route' => 'vehicle_bogged_down.index',
            'route_param' => '',
            'sort_order' => 5,
            'main_permission_key' => 'bogged_down',
            'status' => 1,
        ]);

        $this->attachPermission($incident_intervention_child);

        $traffic_violations_parent = AuthSidebarMenu::firstOrCreate(
            [
                'name'      => 'Driver Behavior',
                'parent_id' => null,
            ],
            [
                'icon'               => 'fa-user-tie',
                'route'              => null,
                'route_param'        => '',
                'sort_order'         => 5,
                'main_permission_key'=> 'driver_behavior',
                'status'             => 1,
            ]
        );

        $this->attachPermission($traffic_violations_parent);

        $driverBehaviorChildren = [
            [
                'name'                => 'Route Diversions',
                'route'               => 'route_diversion.index',
                'sort_order'          => 1,
                'main_permission_key' => 'route_diversion',
            ],
            [
                'name'                => 'Illegal Haulage',
                'route'               => 'illegal_haulage.index',
                'sort_order'          => 2,
                'main_permission_key' => 'illegal_haulage',
            ],
            [
                'name'                => 'Illegal Shortage',
                'route'               => 'illegal_shortage.index',
                'sort_order'          => 3,
                'main_permission_key' => 'illegal_shortage',
            ],
            [
                'name'                => 'Breathaliser Test',
                'route'               => 'breathaliser_test.index',
                'sort_order'          => 4,
                'main_permission_key' => 'breathaliser_test',
            ],
            [
                'name'                => 'Driver Misconduct',
                'route'               => 'driver_misconduct.index',
                'sort_order'          => 5,
                'main_permission_key' => 'driver_misconduct',
            ],
        ];

        foreach ($driverBehaviorChildren as $childData) {
            $child = AuthSidebarMenu::firstOrCreate(
                [
                    'parent_id' => $traffic_violations_parent->id,
                    'name'      => $childData['name'],
                ],
                [
                    'icon'               => 'fa-angle-double-right',
                    'route'              => $childData['route'],
                    'route_param'        => '',
                    'sort_order'         => $childData['sort_order'],
                    'main_permission_key'=> $childData['main_permission_key'],
                    'status'             => 1,
                ]
            );

            $this->attachPermission($child);
        }

        // Driver Behavior Menu End

        $waybill_management = AuthSidebarMenu::create([
            'name' => 'Trip Management',
            'icon' => 'fa-truck-pickup',
            'route' => 'waybill.index',
            'sort_order' => 3,
            'main_permission_key' => 'waybill_management',
            'status' => 1,
        ]);
        $waybill_management_child = $waybill_management->children()->create([
            'name' => 'Waybill',
            'icon' => 'fa-angle-double-right',
            'route' => 'waybill.index',
            'route_param' => '',
            'sort_order' => 1,
            'main_permission_key' => 'waybill_management',
            'status' => 1,
        ]);

        $this->attachPermission($waybill_management_child);

        $this->attachPermission($waybill_management);

        $vehicle_tracking = AuthSidebarMenu::create([
            'name' => 'language.vehicle_tracking',
            'icon' => 'fa-satellite',
            'route' => null,
            'sort_order' => 6,
            'main_permission_key' => 'vehicle_tracking',
            'status' => 0,
        ]);

        $this->attachPermission($vehicle_tracking);

        $assets_enumeration = AuthSidebarMenu::create([
            'name' => 'language.assets_enumeration',
            'icon' => 'fa-list-check',
            'route' => null,
            'sort_order' => 7,
            'main_permission_key' => 'assets_enumeration',
            'status' => 0,
        ]);

        $this->attachPermission($assets_enumeration);

        $reports_analytics = AuthSidebarMenu::create([
            'name' => 'language.reports_analytics',
            'icon' => 'fa-file-lines',
            'route' => null,
            'sort_order' => 8,
            'main_permission_key' => 'reports_analytics',
            'status' => 1,
        ]);

        $this->attachPermission($reports_analytics);

        $staffManagement = AuthSidebarMenu::create([
            'name' => 'language.staff_management',
            'icon' => 'fa-file-invoice-dollar',
            'route' => null,
            'sort_order' => 9,
            'main_permission_key' => 'all_user',
            'status' => 0,
        ]);

        $this->attachPermission($staffManagement);

        $roles = Role::pluck('name', 'title');
        $sortOrder = 1;

        foreach ($roles as $roleKey => $roleName) {
            $child = $staffManagement->children()->create([
                'name' => $roleKey,
                'icon' => 'fa-angle-double-right',
                'route' => 'users.index',
                'route_param' => $roleName,
                'sort_order' => $sortOrder++,
                'main_permission_key' => 'all_user',
                'status' => 1,
            ]);

            $this->attachPermission($child);
        }

        $settings = AuthSidebarMenu::create([
            'name' => 'language.configuration',
            'icon' => 'fa-cogs',
            'route' => null,
            'sort_order' => 10,
            'main_permission_key' => 'website_setting',
            'status' => 1,
        ]);

        $this->attachPermission($settings);

        $settingsChildren = [
            [
                'name' => 'language.role_permission',
                'icon' => 'fa-angle-double-right',
                'route' => 'roles.index',
                'sort_order' => 1,
                'main_permission_key' => 'all_role',
                'status' => 1,
            ],
            [
                'name' => 'Users',
                'icon' => 'fa-angle-double-right',
                'route' => 'users.index',
                'route_param' => 'user',
                'sort_order' => 2,
                'main_permission_key' => 'all_user',
                'status' => 1,
            ],
            [
                'name' => 'language.site_configuration',
                'icon' => 'fa-angle-double-right',
                'route' => 'settings.index',
                'route_param' => 'basic-info',
                'sort_order' => 3,
                'main_permission_key' => 'site_configuration',
                'status' => 0,
            ],
            [
                'name' => 'language.sidebar_menu',
                'icon' => 'fa-angle-double-right',
                'route' => 'menues.index',
                'sort_order' => 4,
                'main_permission_key' => 'all_sidebar_menu',
                'status' => 0,
            ]
        ];

        foreach ($settingsChildren as $childData) {
            $child = $settings->children()->create($childData);
            $this->attachPermission($child);
        }

        $map = AuthSidebarMenu::create([
            'name' => 'language.map',
            'icon' => 'fa-map',
            'route' => null,
            'sort_order' => 10,
            'main_permission_key' => 'map_management',
            'status' => 1,
        ]);

        $mapChildren = [
            [
                'name' => 'language.map',
                'icon' => 'fa-angle-double-right',
                'route' => 'map.index',
                'sort_order' => 1,
                'main_permission_key' => 'map_management',
                'status' => 1,
            ],
        ];

        $this->attachPermission($map);

        foreach ($mapChildren as $childData) {
            $child = $map->children()->create($childData);
            $this->attachPermission($child);
        }

        $communication = AuthSidebarMenu::create([
            'name' => 'Communication',
            'icon' => 'fa-comments',
            'route' => 'messages.seen',
            'sort_order' => 11,
            'main_permission_key' => 'communication',
            'status' => 0,
        ]);

        $this->attachPermission($communication);

        $settingsMenu = AuthSidebarMenu::create([
            'name' => 'language.settings',
            'icon' => 'fa-user-cog',
            'route' => 'change_password',
            'sort_order' => 12,
            'main_permission_key' => 'manage_settings',
            'status' => 0,
        ]);

        $this->attachPermission($settingsMenu);

        $blog = AuthSidebarMenu::create([
            'name' => 'language.blog',
            'icon' => 'fa-pen-to-square',
            'route' => null,
            'sort_order' => 13,
            'main_permission_key' => 'manage_blog',
            'status' => 0,
        ]);

        $this->attachPermission($blog);

        $blogChildren = [
            [
                'name' => 'language.category',
                'icon' => 'fa-angle-double-right',
                'route' => 'blog.category.index',
                'sort_order' => 1,
                'main_permission_key' => 'manage_category',
                'status' => 1,
            ],
            [
                'name' => 'language.post',
                'icon' => 'fa-angle-double-right',
                'route' => 'blog.post.index',
                'sort_order' => 2,
                'main_permission_key' => 'manage_post',
                'status' => 1,
            ]
        ];

        foreach ($blogChildren as $blogchildData) {
            $blogchild = $blog->children()->create($blogchildData);
            $this->attachPermission($blogchild);
        }
    }

    private function attachPermission(AuthSidebarMenu $menu)
    {
        if ($menu->main_permission_key) {
            $permission = Permission::where('name', $menu->main_permission_key)->first();
            if ($permission) {
                $menu->permissions()->newPivotStatement()->insert([
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'auth_sidebar_menu_id' => $menu->id,
                    'permission_id' => $permission->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
