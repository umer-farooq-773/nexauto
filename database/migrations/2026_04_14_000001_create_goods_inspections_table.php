<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('goods_inspections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('vehicle_id')->nullable()->references('id')->on('vehicles')->onDelete('set null');
            $table->string('fleet_no');
            $table->date('inspection_date');
            $table->enum('inspection_type', ['full', 'partial', 'random']);
            $table->string('inspector_name');
            $table->text('issues_found')->nullable();
            $table->enum('goods_status', ['cleared', 'issues']);
            $table->tinyInteger('status')->default('0')->comment('0=Pending, 1=Completed');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index('inspection_date');
            $table->index('status');
            $table->index('goods_status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('goods_inspections');
    }
};
