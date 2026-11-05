<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tables that need client_approved columns.
     * (impoundments already has these columns from a previous migration)
     */
    private array $tables = [
        'accidents',
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
                $blueprint->boolean('client_approved')->default(false)->after('status');
                $blueprint->timestamp('client_approved_at')->nullable()->after('client_approved');
                $blueprint->string('client_approved_by')->nullable()->after('client_approved_at');
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropColumn(['client_approved', 'client_approved_at', 'client_approved_by']);
            });
        }
    }
};
