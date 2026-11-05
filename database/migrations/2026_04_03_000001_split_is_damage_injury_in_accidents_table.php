<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('accidents', function (Blueprint $table) {
            $table->string('is_damage_asset')->default('no')->after('accident_nature_detail');
            $table->string('is_injury')->default('no')->after('is_damage_asset');
        });

        // Migrate existing data
        \DB::table('accidents')->update([
            'is_damage_asset' => \DB::raw('is_damage_injury'),
            'is_injury'       => \DB::raw('is_damage_injury'),
        ]);

        Schema::table('accidents', function (Blueprint $table) {
            $table->dropColumn('is_damage_injury');
        });
    }

    public function down()
    {
        Schema::table('accidents', function (Blueprint $table) {
            $table->string('is_damage_injury')->default('no')->after('accident_nature_detail');
        });

        Schema::table('accidents', function (Blueprint $table) {
            $table->dropColumn(['is_damage_asset', 'is_injury']);
        });
    }
};
