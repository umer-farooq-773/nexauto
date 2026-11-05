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
        Schema::table('waybills', function (Blueprint $table) {
            if (!Schema::hasColumn('waybills', 'origin_lat')) {
                $table->decimal('origin_lat', 10, 7)->nullable()->after('origin');
            }
            if (!Schema::hasColumn('waybills', 'origin_lng')) {
                $table->decimal('origin_lng', 10, 7)->nullable()->after('origin_lat');
            }
            if (!Schema::hasColumn('waybills', 'destination_lat')) {
                $table->decimal('destination_lat', 10, 7)->nullable()->after('destination');
            }
            if (!Schema::hasColumn('waybills', 'destination_lng')) {
                $table->decimal('destination_lng', 10, 7)->nullable()->after('destination_lat');
            }
            if (!Schema::hasColumn('waybills', 'checkpoints')) {
                $table->json('checkpoints')->nullable()->after('destination_lng');
            }
            if (!Schema::hasColumn('waybills', 'route_geometry')) {
                $table->json('route_geometry')->nullable()->after('checkpoints');
            }
            if (!Schema::hasColumn('waybills', 'wialon_route_id')) {
                $table->string('wialon_route_id')->nullable()->after('route_geometry');
            }
            if (!Schema::hasColumn('waybills', 'route_source')) {
                $table->string('route_source', 50)->default('geoapify')->after('wialon_route_id');
            } else {
                DB::statement("ALTER TABLE waybills MODIFY COLUMN route_source VARCHAR(50) NOT NULL DEFAULT 'geoapify'");
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('waybills', function (Blueprint $table) {
            $table->dropColumn([
                'origin_lat',
                'origin_lng',
                'destination_lat',
                'destination_lng',
                'checkpoints',
                'route_geometry',
                'wialon_route_id',
                'route_source',
            ]);
        });
    }
};
