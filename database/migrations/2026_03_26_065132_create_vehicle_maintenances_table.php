<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('vehicle_maintenances', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');

            $table->string('maintenance_type'); // e.g. Oil Change, Tire Replacement
            $table->date('maintenance_date');
            $table->date('next_maintenance_date')->nullable();
            $table->decimal('cost', 10, 2)->nullable();
            $table->string('service_provider')->nullable();
            $table->text('description')->nullable();

            $table->string('status')->default(1)->comment('1=Completed, 0=Pending, 2=In Progress');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vehicle_maintenances');
    }
};
