<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicle_breakdowns', function (Blueprint $table) {
            $table->string('incident_type')->default('breakdown')->after('repair_cost');
        });

        DB::table('vehicle_breakdowns')->update([
            'incident_type' => 'breakdown',
        ]);
    }

    public function down(): void
    {
        Schema::table('vehicle_breakdowns', function (Blueprint $table) {
            $table->dropColumn('incident_type');
        });
    }
};
