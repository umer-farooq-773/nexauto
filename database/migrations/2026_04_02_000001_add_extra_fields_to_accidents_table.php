<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('accidents', function (Blueprint $table) {
            $table->string('accident_type_detail')->nullable()->after('accident_type');
            $table->string('driver_status')->nullable()->after('accident_type_detail');
            $table->string('motor_mate_status')->nullable()->after('driver_status');
            $table->string('content')->nullable()->after('motor_mate_status');
            $table->string('damage_extend')->nullable()->after('content');
            $table->string('is_fatality')->default('no')->after('damage_extend');
            $table->string('fatality_type')->nullable()->after('is_fatality');
            $table->string('accident_nature_detail')->nullable()->after('fatality_type');
            $table->string('is_damage_injury')->default('no')->after('accident_nature_detail');
        });
    }

    public function down()
    {
        Schema::table('accidents', function (Blueprint $table) {
            $table->dropColumn([
                'accident_type_detail', 'driver_status', 'motor_mate_status',
                'content', 'damage_extend', 'is_fatality', 'fatality_type',
                'accident_nature_detail', 'is_damage_injury',
            ]);
        });
    }
};
