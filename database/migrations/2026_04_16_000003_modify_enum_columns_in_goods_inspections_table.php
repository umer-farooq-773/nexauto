<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('goods_inspections', function (Blueprint $table) {
            $table->string('inspection_type')->change();
            $table->string('goods_status')->change();
        });
    }

    public function down()
    {
        Schema::table('goods_inspections', function (Blueprint $table) {
            $table->enum('inspection_type', ['full', 'partial', 'random'])->change();
            $table->enum('goods_status', ['cleared', 'issues'])->change();
        });
    }
};
