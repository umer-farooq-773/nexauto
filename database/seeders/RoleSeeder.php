<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app()['cache']->forget('spatie.permission.cache');

        // Super Admin — Full access
        $superAdmin = Role::create([
            'name' => 'super_admin',
            'title' => 'Super Admin',
            'color' => '#FF6B6B'
        ]);

        $permissions = Permission::all();
        $superAdmin->availablePermissions()->sync($permissions);
        $superAdmin->syncPermissions($permissions);

        // Admin — Almost full access
        $admin = Role::create([
            'name' => 'admin',
            'title' => 'Admin',
            'color' => '#FF6B6B'
        ]);

        $permissions = Permission::all();
        $admin->availablePermissions()->sync($permissions);
        $admin->syncPermissions($permissions);

        // Champs Management
        $champsManagement = Role::create([
            'name' => 'champs_management',
            'title' => 'Champs Management',
            'color' => '#6bff77ff'
        ]);

        $champsManagementPermissions = Permission::where(function ($query) {
            $query->where('name', 'dashboard')
                ->orWhere('name', 'vehicle_management')
                ->orWhere('name', 'view_vehicle')
                ->orWhere('name', 'edit_vehicle')
                ->orWhere('name', 'incident_intervention')
                ->orWhere('name', 'view_incident')
                ->orWhere('name', 'edit_incident')
                ->orWhere('name', 'vehicle_tracking')
                ->orWhere('name', 'add_tracking')
                ->orWhere('name', 'reports_analytics')
                ->orWhere('name', 'view_reports')
                ->orWhere('name', 'assets_enumeration')
                ->orWhere('name', 'view_asset')
                ->orWhere('name', 'workflow_management')
                ->orWhere('name', 'approve_workflow')
                ->orWhere('name', 'reject_workflow');
        })->get();

        $champsManagement->availablePermissions()->sync($champsManagementPermissions);
        $champsManagement->syncPermissions($champsManagementPermissions);

        // Security Operative
        $securityOperative = Role::create([
            'name' => 'security_operative',
            'title' => 'Security Operative',
            'color' => '#30007cff'
        ]);

        $securityOperativePermissions = Permission::where(function ($query) {
            $query->where('name', 'dashboard')
                ->orWhere('name', 'vehicle_management')
                ->orWhere('name', 'view_vehicle')
                ->orWhere('name', 'incident_intervention')
                ->orWhere('name', 'add_incident')
                ->orWhere('name', 'view_incident')
                ->orWhere('name', 'consignment_inspection')
                ->orWhere('name', 'add_consignment')
                ->orWhere('name', 'view_consignment')
                ->orWhere('name', 'vehicle_tracking')
                ->orWhere('name', 'add_tracking')
                ->orWhere('name', 'view_tracking');
        })->get();

        $securityOperative->availablePermissions()->sync($securityOperativePermissions);
        $securityOperative->syncPermissions($securityOperativePermissions);

        // Field Worker
        $fieldWorker = Role::create([
            'name' => 'field_worker',
            'title' => 'Field Worker',
            'color' => '#30007cff'
        ]);

        $fieldWorkerPermissions = Permission::where(function ($query) {
            $query->where('name', 'dashboard')
                ->orWhere('name', 'vehicle_management')
                ->orWhere('name', 'add_vehicle')
                ->orWhere('name', 'view_vehicle')
                ->orWhere('name', 'edit_vehicle')
                ->orWhere('name', 'consignment_inspection')
                ->orWhere('name', 'view_consignment');
        })->get();

        $fieldWorker->availablePermissions()->sync($fieldWorkerPermissions);
        $fieldWorker->syncPermissions($fieldWorkerPermissions);

// Client Management
        $clientManagement = Role::create([
            'name' => 'client_management',
            'title' => 'Client Management',
            'color' => '#6bff77ff'
        ]);

        // Client Directors
        $clientDirectors = Role::create([
            'name' => 'client_directors',
            'title' => 'Client Directors',
            'color' => '#6bff77ff'
        ]);


        $clientManagementPermissions = Permission::where(function ($query) {
            $query->where('name', 'dashboard')
                ->orWhere('name', 'vehicle_management')
                ->orWhere('name', 'view_vehicle')
                ->orWhere('name', 'incident_intervention')
                ->orWhere('name', 'view_incident')
                ->orWhere('name', 'reports_analytics')
                ->orWhere('name', 'view_reports')
                ->orWhere('name', 'workflow_management')
                ->orWhere('name', 'approve_workflow')
                ->orWhere('name', 'reject_workflow');
        })->get();

        $clientManagement->availablePermissions()->sync($clientManagementPermissions);
        $clientManagement->syncPermissions($clientManagementPermissions);

        // Normal User
        $user = Role::create([
            'name' => 'user',
            'title' => 'User',
            'color' => '#4ECDC4'
        ]);

        $userPermissions = Permission::where(function ($query) {
            $query->where('name', 'dashboard')
                ->orWhere('name', 'view_vehicle')
                ->orWhere('name', 'view_tracking');
        })->get();

        $user->availablePermissions()->sync($userPermissions);
        $user->syncPermissions($userPermissions);
    }
}
