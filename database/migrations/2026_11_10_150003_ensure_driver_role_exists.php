<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $role = DB::table('roles')->where('name', 'driver')->first();

        if (!$role) {
            $maxId = (int) (DB::table('roles')->max('id') ?? 8);
            DB::table('roles')->insert([
                'id'         => $maxId + 1,
                'name'       => 'driver',
                'title'      => 'Driver',
                'color'      => '#0ea5e9',
                'guard_name' => 'web',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep driver role intact or delete
    }
};

