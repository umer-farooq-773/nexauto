<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('goods_inspections', function (Blueprint $table) {
            $table->string('inspection_time')->nullable()->after('inspection_date');
            $table->string('inspection_location')->nullable()->after('inspection_time');
            $table->string('vehicle_lorry_number')->nullable()->after('fleet_no');
            $table->string('route')->nullable()->after('vehicle_lorry_number');
            $table->string('driver_name')->nullable()->after('route');
            $table->string('driver_phone_id')->nullable()->after('driver_name');
            $table->text('goods_description')->nullable()->after('driver_phone_id');
            $table->string('waybill_number')->nullable()->after('goods_description');
            $table->string('declared_quantity')->nullable()->after('waybill_number');
            $table->string('observed_quantity')->nullable()->after('declared_quantity');
            $table->string('cargo_secured')->nullable()->after('observed_quantity');
            $table->string('goods_match_waybill')->nullable()->after('cargo_secured');
            $table->string('unauthorised_items')->nullable()->after('goods_match_waybill');
            $table->string('doors_tarpaulin_secure')->nullable()->after('unauthorised_items');
            $table->string('visible_damage')->nullable()->after('doors_tarpaulin_secure');
        });
    }

    public function down()
    {
        Schema::table('goods_inspections', function (Blueprint $table) {
            $table->dropColumn([
                'inspection_time', 'inspection_location', 'vehicle_lorry_number', 'route',
                'driver_name', 'driver_phone_id', 'goods_description', 'waybill_number',
                'declared_quantity', 'observed_quantity', 'cargo_secured',
                'goods_match_waybill', 'unauthorised_items', 'doors_tarpaulin_secure',
                'visible_damage'
            ]);
        });
    }
};
