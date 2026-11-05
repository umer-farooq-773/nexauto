<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            if (!Schema::hasColumn('vehicles', 'phone_number')) {
                $table->string('phone_number')->nullable()->after('manager_email');
            }
            if (!Schema::hasColumn('vehicles', 'vehicle_type')) {
                $table->string('vehicle_type')->nullable()->after('vehicle_type_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            if (Schema::hasColumn('vehicles', 'phone_number')) {
                $table->dropColumn('phone_number');
            }
            if (Schema::hasColumn('vehicles', 'vehicle_type')) {
                $table->dropColumn('vehicle_type');
            }
        });
    }
};
