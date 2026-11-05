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
        $incidentTables = [
            'impoundments',
            'route_diversions',
            'illegal_haulages',
            'breathaliser_tests',
            'driver_misconducts',
            'goods_inspections',
        ];

        foreach ($incidentTables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $t) use ($table) {
                    if (!Schema::hasColumn($table, 'created_by')) {
                        $t->unsignedBigInteger('created_by')->nullable()->after('created_at');
                        $t->foreign('created_by')->references('id')->on('users')->onDelete('set null');
                    }
                });
            }
        }

        // Ensure goods_inspections has client approval columns
        if (Schema::hasTable('goods_inspections')) {
            Schema::table('goods_inspections', function (Blueprint $t) {
                if (!Schema::hasColumn('goods_inspections', 'client_approved')) {
                    $t->boolean('client_approved')->default(false)->after('status');
                }
                if (!Schema::hasColumn('goods_inspections', 'client_approved_at')) {
                    $t->timestamp('client_approved_at')->nullable()->after('client_approved');
                }
                if (!Schema::hasColumn('goods_inspections', 'client_approved_by')) {
                    $t->string('client_approved_by')->nullable()->after('client_approved_at');
                }
                if (!Schema::hasColumn('goods_inspections', 'client_approved_comment')) {
                    $t->text('client_approved_comment')->nullable()->after('client_approved_by');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $incidentTables = [
            'impoundments',
            'route_diversions',
            'illegal_haulages',
            'breathaliser_tests',
            'driver_misconducts',
            'goods_inspections',
        ];

        foreach ($incidentTables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'created_by')) {
                Schema::table($table, function (Blueprint $t) {
                    try {
                        $t->dropForeign(['created_by']);
                    } catch (\Throwable $e) {
                        // ignore if foreign key is not named as default
                    }
                    $t->dropColumn('created_by');
                });
            }
        }

        if (Schema::hasTable('goods_inspections')) {
            Schema::table('goods_inspections', function (Blueprint $t) {
                $columnsToDrop = array_filter(['client_approved', 'client_approved_at', 'client_approved_by', 'client_approved_comment'], function ($col) {
                    return Schema::hasColumn('goods_inspections', $col);
                });
                if (!empty($columnsToDrop)) {
                    $t->dropColumn($columnsToDrop);
                }
            });
        }
    }
};
