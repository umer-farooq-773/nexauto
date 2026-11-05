<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'accidents',
        'impoundments',
        'vehicle_breakdowns',
        'vehicle_bogged_downs',
        'illegal_detentions',
        'illegal_haulages',
        'driver_misconducts',
        'breathaliser_tests',
        'route_diversions',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->text('client_approved_comment')->nullable()->after('client_approved_by');
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropColumn('client_approved_comment');
            });
        }
    }
};
