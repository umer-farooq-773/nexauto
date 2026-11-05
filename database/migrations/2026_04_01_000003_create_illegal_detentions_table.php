<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('illegal_detentions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
            $table->date('detention_date');
            $table->string('detention_location');
            $table->string('detained_by')->nullable();
            $table->string('reason')->nullable();
            $table->date('release_date')->nullable();
            $table->text('remarks')->nullable();
            $table->string('status')->default('0')->comment('0=Detained, 1=Released');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('illegal_detentions');
    }
};
