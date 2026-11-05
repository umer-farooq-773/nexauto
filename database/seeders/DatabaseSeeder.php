<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(VehicleMakeModelSeeder::class);
        $this->call(VehicleTypeSeeder::class);
        $this->call(PermissionSeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(DriverDetailSeeder::class);
        $this->call(DriverMateSeeder::class);
        $this->call(VehicleSeeder::class);
        $this->call(SettingSeeder::class);
        $this->call(AuthSidebarMenuSeeder::class);
        $this->call(ConfigurationUserMenuSeeder::class);

        $this->call(IncidentInterventionMenuSeeder::class);
        $this->call(GoodsInspectionMenuSeeder::class);
        $this->call(LicenseCategorySeeder::class);
        $this->call(RouteTripSeeder::class);
        $this->call(StatusSeeder::class);
        $this->call(WaybillSeeder::class);
        $this->call([GpsDeviceTypeSeeder::class,]);


        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
