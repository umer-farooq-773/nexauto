<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('accidents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
            $table->date('accident_date');
            $table->string('accident_location');
            $table->string('accident_type')->nullable();
            $table->text('description')->nullable();
            $table->decimal('damage_cost', 10, 2)->nullable();
            $table->date('resolved_date')->nullable();
            $table->string('status')->default('0')->comment('0=Pending, 1=Resolved, 2=In Progress');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('accidents');
    }
};
