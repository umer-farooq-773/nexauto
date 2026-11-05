<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('route_trips', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('trip_name');
            $table->string('origin');
            $table->string('destination');
            $table->decimal('distance_km', 8, 2)->nullable();
            $table->string('estimated_duration')->nullable();
            $table->text('description')->nullable();
            $table->boolean('status')->default(1);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('route_trips');
    }
};
