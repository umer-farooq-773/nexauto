<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('goods_inspections', function (Blueprint $table) {
            if (!Schema::hasColumn('goods_inspections', 'route_diversion')) {
                $table->string('route_diversion')->nullable()->default('No')->after('visible_damage');
            }
            if (!Schema::hasColumn('goods_inspections', 'illegal_haulage')) {
                $table->string('illegal_haulage')->nullable()->default('No')->after('route_diversion');
            }
            if (!Schema::hasColumn('goods_inspections', 'breathaliser_test')) {
                $table->string('breathaliser_test')->nullable()->default('Passed')->after('illegal_haulage');
            }
            if (!Schema::hasColumn('goods_inspections', 'driver_misconduct')) {
                $table->string('driver_misconduct')->nullable()->default('No')->after('breathaliser_test');
            }
        });
    }

    public function down(): void
    {
        Schema::table('goods_inspections', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('goods_inspections', 'route_diversion')) {
                $columns[] = 'route_diversion';
            }
            if (Schema::hasColumn('goods_inspections', 'illegal_haulage')) {
                $columns[] = 'illegal_haulage';
            }
            if (Schema::hasColumn('goods_inspections', 'breathaliser_test')) {
                $columns[] = 'breathaliser_test';
            }
            if (Schema::hasColumn('goods_inspections', 'driver_misconduct')) {
                $columns[] = 'driver_misconduct';
            }
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
