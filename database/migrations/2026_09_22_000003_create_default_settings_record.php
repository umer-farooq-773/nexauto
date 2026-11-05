<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Ensure installations made before the settings seeder was run have the
     * singleton settings record required by guest pages and registration.
     */
    public function up(): void
    {
        if (! Schema::hasTable('settings') || DB::table('settings')->exists()) {
            return;
        }

        DB::table('settings')->insert([
            'id' => (string) Str::uuid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        // The settings record may have been configured after installation, so
        // it must not be deleted when this migration is rolled back.
    }
};
