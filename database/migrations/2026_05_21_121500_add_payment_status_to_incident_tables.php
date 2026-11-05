<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'impoundments',
            'illegal_detentions',
            'illegal_haulages',
            'vehicle_bogged_downs',
            'driver_misconducts',
            'accidents',
            'route_diversions',
            'breathaliser_tests',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    $table->string('payment_status')->default('0')->comment('0=Pending, 1=Approved')->after('client_approved_comment');
                });
            }
        }
    }

    public function down(): void
    {
        $tables = [
            'impoundments',
            'illegal_detentions',
            'illegal_haulages',
            'vehicle_bogged_downs',
            'driver_misconducts',
            'accidents',
            'route_diversions',
            'breathaliser_tests',
        ];

        foreach ($tables as $tablesName) {
            if (Schema::hasTable($tablesName) && Schema::hasColumn($tablesName, 'payment_status')) {
                Schema::table($tablesName, function (Blueprint $table) {
                    $table->dropColumn('payment_status');
                });
            }
        }
    }
};
