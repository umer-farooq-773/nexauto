<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicle_maintenances', function (Blueprint $table) {
            $table->string('reference_no')->nullable()->after('id');
            $table->string('odometer')->nullable()->after('maintenance_date');
            $table->date('expected_completion_date')->nullable()->after('next_maintenance_date');
            $table->string('priority')->default('Routine')->after('service_provider');
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_maintenances', function (Blueprint $table) {
            $table->dropColumn([
                'reference_no',
                'odometer',
                'expected_completion_date',
                'priority',
            ]);
        });
    }
};
