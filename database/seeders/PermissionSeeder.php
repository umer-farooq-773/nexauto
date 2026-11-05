<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        Permission::create(['name' => 'dashboard', 'title' => 'Dashboard', 'category' => 'Dashboard']);

        // Users
        Permission::create(['name' => 'all_user', 'title' => 'View all users', 'category' => 'Users']);
        Permission::create(['name' => 'add_user', 'title' => 'Add new user', 'category' => 'Users']);
        Permission::create(['name' => 'view_user', 'title' => 'View user details', 'category' => 'Users']);
        Permission::create(['name' => 'edit_user', 'title' => 'Edit user', 'category' => 'Users']);
        Permission::create(['name' => 'delete_user', 'title' => 'Delete user', 'category' => 'Users']);

        // Sidebar Menu
        Permission::create(['name' => 'all_sidebar_menu', 'title' => 'View all sidebar menues', 'category' => 'Sidebar Menu']);
        Permission::create(['name' => 'add_sidebar_menu', 'title' => 'Add new sidebar menu', 'category' => 'Sidebar Menu']);
        Permission::create(['name' => 'view_sidebar_menu', 'title' => 'View sidebar menu details', 'category' => 'Sidebar Menu']);
        Permission::create(['name' => 'edit_sidebar_menu', 'title' => 'Edit sidebar menu', 'category' => 'Sidebar Menu']);
        Permission::create(['name' => 'delete_sidebar_menu', 'title' => 'Delete sidebar menu', 'category' => 'Sidebar Menu']);

        // Roles
        Permission::create(['name' => 'all_role', 'title' => 'All Roles', 'category' => 'Roles']);
        Permission::create(['name' => 'add_role', 'title' => 'Add Role', 'category' => 'Roles']);
        Permission::create(['name' => 'view_role', 'title' => 'View Role', 'category' => 'Roles']);
        Permission::create(['name' => 'edit_role', 'title' => 'Edit Role', 'category' => 'Roles']);
        Permission::create(['name' => 'delete_role', 'title' => 'Delete Role', 'category' => 'Roles']);
        Permission::create(['name' => 'assign_permission', 'title' => 'Assign Permission', 'category' => 'Roles']);

        // Settings
        Permission::create(['name' => 'site_setting', 'title' => 'Site Setting', 'category' => 'Settings']);
        Permission::create(['name' => 'website_setting', 'title' => 'Website Setting', 'category' => 'Website Settings']);
        Permission::create(['name' => 'site_configuration', 'title' => 'Site Configuration', 'category' => 'Configuration']);

        // Profile
        Permission::create(['name' => 'manage_settings', 'title' => 'Profile Setting', 'category' => 'Profile Settings']);

        // Blog
        Permission::create(['name' => 'manage_blog', 'title' => 'Manage Blog', 'category' => 'Blog']);
        Permission::create(['name' => 'manage_category', 'title' => 'Manage Category', 'category' => 'Blog']);
        Permission::create(['name' => 'manage_post', 'title' => 'Manage Post', 'category' => 'Blog']);

        // Communication
        Permission::create(['name' => 'communication', 'title' => 'Communication', 'category' => 'Communication']);

        // Vehicle Management
        Permission::create(['name' => 'vehicle_management', 'title' => 'View all vehicles', 'category' => 'Vehicle Management']);
        Permission::create(['name' => 'add_vehicle', 'title' => 'Add new vehicle', 'category' => 'Vehicle Management']);
        Permission::create(['name' => 'view_vehicle', 'title' => 'View vehicle details', 'category' => 'Vehicle Management']);
        Permission::create(['name' => 'edit_vehicle', 'title' => 'Edit vehicle', 'category' => 'Vehicle Management']);
        Permission::create(['name' => 'delete_vehicle', 'title' => 'Delete vehicle', 'category' => 'Vehicle Management']);

        Permission::create(['name' => 'driver_management', 'title' => 'Driver Management', 'category' => 'Vehicle Management']);
        Permission::create(['name' => 'add_driver', 'title' => 'Add new driver', 'category' => 'Vehicle Management']);
        Permission::create(['name' => 'view_driver', 'title' => 'View driver details', 'category' => 'Vehicle Management']);
        Permission::create(['name' => 'edit_driver', 'title' => 'Edit driver', 'category' => 'Vehicle Management']);
        Permission::create(['name' => 'delete_driver', 'title' => 'Delete driver', 'category' => 'Vehicle Management']);

        Permission::create(['name' => 'driver_mate_management', 'title' => 'Driver Mate Management', 'category' => 'Vehicle Management']);
        Permission::create(['name' => 'add_driver_mate', 'title' => 'Add new driver mate', 'category' => 'Vehicle Management']);
        Permission::create(['name' => 'view_driver_mate', 'title' => 'View driver mate details', 'category' => 'Vehicle Management']);
        Permission::create(['name' => 'edit_driver_mate', 'title' => 'Edit driver mate', 'category' => 'Vehicle Management']);
        Permission::create(['name' => 'delete_driver_mate', 'title' => 'Delete driver mate', 'category' => 'Vehicle Management']);

        Permission::create(['name' => 'route_trip_management', 'title' => 'Route Trip Management', 'category' => 'Vehicle Management']);
        Permission::create(['name' => 'add_route_trip', 'title' => 'Add new route trip', 'category' => 'Vehicle Management']);
        Permission::create(['name' => 'view_route_trip', 'title' => 'View route trip details', 'category' => 'Vehicle Management']);
        Permission::create(['name' => 'edit_route_trip', 'title' => 'Edit route trip', 'category' => 'Vehicle Management']);
        Permission::create(['name' => 'delete_route_trip', 'title' => 'Delete route trip', 'category' => 'Vehicle Management']);

        Permission::create(['name' => 'waybill_management', 'title' => 'Waybill Management', 'category' => 'Waybill Management']);
        Permission::create(['name' => 'add_waybill', 'title' => 'Add new waybill', 'category' => 'Waybill Management']);
        Permission::create(['name' => 'view_waybill', 'title' => 'View waybill details', 'category' => 'Waybill Management']);
        Permission::create(['name' => 'edit_waybill', 'title' => 'Edit waybill', 'category' => 'Waybill Management']);
        Permission::create(['name' => 'delete_waybill', 'title' => 'Delete waybill', 'category' => 'Waybill Management']);

        Permission::create(['name' => 'maintenance_management', 'title' => 'Maintenance Management', 'category' => 'Vehicle Management']);
        Permission::create(['name' => 'maintenance_history', 'title' => 'View maintenance history', 'category' => 'Vehicle Management']);
        Permission::create(['name' => 'add_maintenance', 'title' => 'Add new maintenance record', 'category' => 'Vehicle Management']);
        Permission::create(['name' => 'view_maintenance', 'title' => 'View maintenance details', 'category' => 'Vehicle Management']);
        Permission::create(['name' => 'edit_maintenance', 'title' => 'Edit maintenance record', 'category' => 'Vehicle Management']);
        Permission::create(['name' => 'delete_maintenance', 'title' => 'Delete maintenance record', 'category' => 'Vehicle Management']);


        // Incident & Intervention
        Permission::create(['name' => 'incident_intervention', 'title' => 'View all incidents',          'category' => 'Incident & Intervention']);
        Permission::create(['name' => 'add_incident',          'title' => 'Add new incident',            'category' => 'Incident & Intervention']);
        Permission::create(['name' => 'view_incident',         'title' => 'View incident details',       'category' => 'Incident & Intervention']);
        Permission::create(['name' => 'edit_incident',         'title' => 'Edit incident',               'category' => 'Incident & Intervention']);
        Permission::create(['name' => 'delete_incident',       'title' => 'Delete incident',             'category' => 'Incident & Intervention']);

        // Impoundment
        Permission::create(['name' => 'impoundment',        'title' => 'View impoundment records',   'category' => 'Incident & Intervention']);
        Permission::create(['name' => 'add_impoundment',    'title' => 'Add new impoundment',        'category' => 'Incident & Intervention']);
        Permission::create(['name' => 'view_impoundment',   'title' => 'View impoundment details',   'category' => 'Incident & Intervention']);
        Permission::create(['name' => 'edit_impoundment',   'title' => 'Edit impoundment record',    'category' => 'Incident & Intervention']);
        Permission::create(['name' => 'delete_impoundment', 'title' => 'Delete impoundment record',  'category' => 'Incident & Intervention']);

        // Vehicle Breakdown
        Permission::create(['name' => 'vehicle_breakdown',        'title' => 'View vehicle breakdowns',        'category' => 'Incident & Intervention']);
        Permission::create(['name' => 'add_vehicle_breakdown',    'title' => 'Add new vehicle breakdown',      'category' => 'Incident & Intervention']);
        Permission::create(['name' => 'view_vehicle_breakdown',   'title' => 'View vehicle breakdown details', 'category' => 'Incident & Intervention']);
        Permission::create(['name' => 'edit_vehicle_breakdown',   'title' => 'Edit vehicle breakdown',         'category' => 'Incident & Intervention']);
        Permission::create(['name' => 'delete_vehicle_breakdown', 'title' => 'Delete vehicle breakdown',       'category' => 'Incident & Intervention']);

        // Illegal Detention
        Permission::create(['name' => 'illegal_detention',        'title' => 'View illegal detentions',        'category' => 'Incident & Intervention']);
        Permission::create(['name' => 'add_illegal_detention',    'title' => 'Add new illegal detention',      'category' => 'Incident & Intervention']);
        Permission::create(['name' => 'view_illegal_detention',   'title' => 'View illegal detention details', 'category' => 'Incident & Intervention']);
        Permission::create(['name' => 'edit_illegal_detention',   'title' => 'Edit illegal detention',         'category' => 'Incident & Intervention']);
        Permission::create(['name' => 'delete_illegal_detention', 'title' => 'Delete illegal detention',       'category' => 'Incident & Intervention']);

        // Accident
        Permission::create(['name' => 'accident',        'title' => 'View accident records',   'category' => 'Incident & Intervention']);
        Permission::create(['name' => 'add_accident',    'title' => 'Add new accident',        'category' => 'Incident & Intervention']);
        Permission::create(['name' => 'view_accident',   'title' => 'View accident details',   'category' => 'Incident & Intervention']);
        Permission::create(['name' => 'edit_accident',   'title' => 'Edit accident record',    'category' => 'Incident & Intervention']);
        Permission::create(['name' => 'delete_accident', 'title' => 'Delete accident record',  'category' => 'Incident & Intervention']);

        // Bogged Down
        $legacyBoggedDownPermissions = [
            'vehicle_bugged_down' => 'bogged_down',
            'add_vehicle_bugged_down' => 'add_bogged_down',
            'view_vehicle_bugged_down' => 'view_bogged_down',
            'edit_vehicle_bugged_down' => 'edit_bogged_down',
            'delete_vehicle_bugged_down' => 'delete_bogged_down',
        ];

        foreach ($legacyBoggedDownPermissions as $legacyName => $newName) {
            if (
                Permission::where('name', $legacyName)->exists() &&
                !Permission::where('name', $newName)->exists()
            ) {
                Permission::where('name', $legacyName)->update(['name' => $newName]);
            }
        }

        Permission::updateOrCreate(
            ['name' => 'bogged_down'],
            ['title' => 'View bogged down incidents', 'category' => 'Incident & Intervention']
        );
        Permission::updateOrCreate(
            ['name' => 'add_bogged_down'],
            ['title' => 'Add new bogged down incident', 'category' => 'Incident & Intervention']
        );
        Permission::updateOrCreate(
            ['name' => 'view_bogged_down'],
            ['title' => 'View bogged down incident details', 'category' => 'Incident & Intervention']
        );
        Permission::updateOrCreate(
            ['name' => 'edit_bogged_down'],
            ['title' => 'Edit bogged down incident', 'category' => 'Incident & Intervention']
        );
        Permission::updateOrCreate(
            ['name' => 'delete_bogged_down'],
            ['title' => 'Delete bogged down incident', 'category' => 'Incident & Intervention']
        );

        // Driver Behavior
        // Parent menu permission — must be category 'Driver Behavior', not 'Incident & Intervention'
        Permission::create(['name' => 'driver_behavior', 'title' => 'View Driver Behavior menu', 'category' => 'Driver Behavior']);

        // Route Diversions
        Permission::create(['name' => 'route_diversion', 'title' => 'View route diversions', 'category' => 'Driver Behavior']);
        Permission::create(['name' => 'add_route_diversion', 'title' => 'Add new route diversion', 'category' => 'Driver Behavior']);
        Permission::create(['name' => 'view_route_diversion', 'title' => 'View route diversion details', 'category' => 'Driver Behavior']);
        Permission::create(['name' => 'edit_route_diversion', 'title' => 'Edit route diversion', 'category' => 'Driver Behavior']);
        Permission::create(['name' => 'delete_route_diversion', 'title' => 'Delete route diversion', 'category' => 'Driver Behavior']);

        // Illegal Haulage
        Permission::create(['name' => 'illegal_haulage', 'title' => 'View illegal haulage records', 'category' => 'Driver Behavior']);
        Permission::create(['name' => 'add_illegal_haulage', 'title' => 'Add new illegal haulage record', 'category' => 'Driver Behavior']);
        Permission::create(['name' => 'view_illegal_haulage', 'title' => 'View illegal haulage details', 'category' => 'Driver Behavior']);
        Permission::create(['name' => 'edit_illegal_haulage', 'title' => 'Edit illegal haulage record', 'category' => 'Driver Behavior']);
        Permission::create(['name' => 'delete_illegal_haulage', 'title' => 'Delete illegal haulage record', 'category' => 'Driver Behavior']);

        // Illegal Shortage
        Permission::create(['name' => 'illegal_shortage', 'title' => 'View illegal shortage records', 'category' => 'Driver Behavior']);
        Permission::create(['name' => 'add_illegal_shortage', 'title' => 'Add new illegal shortage record', 'category' => 'Driver Behavior']);
        Permission::create(['name' => 'view_illegal_shortage', 'title' => 'View illegal shortage details', 'category' => 'Driver Behavior']);
        Permission::create(['name' => 'edit_illegal_shortage', 'title' => 'Edit illegal shortage record', 'category' => 'Driver Behavior']);
        Permission::create(['name' => 'delete_illegal_shortage', 'title' => 'Delete illegal shortage record', 'category' => 'Driver Behavior']);

        // Breathaliser Test
        Permission::create(['name' => 'breathaliser_test', 'title' => 'View breathaliser test records', 'category' => 'Driver Behavior']);
        Permission::create(['name' => 'add_breathaliser_test', 'title' => 'Add new breathaliser test record', 'category' => 'Driver Behavior']);
        Permission::create(['name' => 'view_breathaliser_test', 'title' => 'View breathaliser test details', 'category' => 'Driver Behavior']);
        Permission::create(['name' => 'edit_breathaliser_test', 'title' => 'Edit breathaliser test record', 'category' => 'Driver Behavior']);
        Permission::create(['name' => 'delete_breathaliser_test', 'title' => 'Delete breathaliser test record', 'category' => 'Driver Behavior']);

        // Driver Misconduct
        Permission::create(['name' => 'driver_misconduct', 'title' => 'View driver misconduct records', 'category' => 'Driver Behavior']);
        Permission::create(['name' => 'add_driver_misconduct', 'title' => 'Add new driver misconduct record', 'category' => 'Driver Behavior']);
        Permission::create(['name' => 'view_driver_misconduct', 'title' => 'View driver misconduct details', 'category' => 'Driver Behavior']);
        Permission::create(['name' => 'edit_driver_misconduct', 'title' => 'Edit driver misconduct record', 'category' => 'Driver Behavior']);
        Permission::create(['name' => 'delete_driver_misconduct', 'title' => 'Delete driver misconduct record', 'category' => 'Driver Behavior']);

        // Vehicle Tracking
        Permission::create(['name' => 'vehicle_tracking', 'title' => 'View all trackings', 'category' => 'Vehicle Tracking']);
        Permission::create(['name' => 'add_tracking', 'title' => 'Add new tracking', 'category' => 'Vehicle Tracking']);
        Permission::create(['name' => 'view_tracking', 'title' => 'View tracking details', 'category' => 'Vehicle Tracking']);
        Permission::create(['name' => 'edit_tracking', 'title' => 'Edit tracking', 'category' => 'Vehicle Tracking']);
        Permission::create(['name' => 'delete_tracking', 'title' => 'Delete tracking', 'category' => 'Vehicle Tracking']);

        // Reports & Analytics
        Permission::create(['name' => 'reports_analytics', 'title' => 'View all reports', 'category' => 'Reports & Analytics']);
        Permission::create(['name' => 'add_reports', 'title' => 'Add new reports', 'category' => 'Reports & Analytics']);
        Permission::create(['name' => 'view_reports', 'title' => 'View reports details', 'category' => 'Reports & Analytics']);
        Permission::create(['name' => 'edit_reports', 'title' => 'Edit reports', 'category' => 'Reports & Analytics']);
        Permission::create(['name' => 'delete_reports', 'title' => 'Delete reports', 'category' => 'Reports & Analytics']);

        // Assets Enumeration
        Permission::create(['name' => 'assets_enumeration', 'title' => 'View all assets', 'category' => 'Assets Enumeration']);
        Permission::create(['name' => 'add_asset', 'title' => 'Add new asset', 'category' => 'Assets Enumeration']);
        Permission::create(['name' => 'view_asset', 'title' => 'View asset details', 'category' => 'Assets Enumeration']);
        Permission::create(['name' => 'edit_asset', 'title' => 'Edit asset', 'category' => 'Assets Enumeration']);
        Permission::create(['name' => 'delete_asset', 'title' => 'Delete asset', 'category' => 'Assets Enumeration']);

        // Consignment Inspection (NEW)
        Permission::create(['name' => 'consignment_inspection', 'title' => 'View all consignments', 'category' => 'Consignment Inspection']);
        Permission::create(['name' => 'add_consignment', 'title' => 'Add new consignment', 'category' => 'Consignment Inspection']);
        Permission::create(['name' => 'view_consignment', 'title' => 'View consignment details', 'category' => 'Consignment Inspection']);
        Permission::create(['name' => 'edit_consignment', 'title' => 'Edit consignment', 'category' => 'Consignment Inspection']);
        Permission::create(['name' => 'delete_consignment', 'title' => 'Delete consignment', 'category' => 'Consignment Inspection']);

        // Workflow Management (NEW)
        Permission::create(['name' => 'workflow_management', 'title' => 'View workflows', 'category' => 'Workflow']);
        Permission::create(['name' => 'approve_workflow', 'title' => 'Approve workflow', 'category' => 'Workflow']);
        Permission::create(['name' => 'reject_workflow', 'title' => 'Reject workflow', 'category' => 'Workflow']);


    }
}
