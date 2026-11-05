<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->UUID('id')->primary();

            $table->string('fleet_no');
            $table->string('manager_first_name');
            $table->string('manager_last_name');
            $table->string('manager_email');
            $table->string('owner_first_name');
            $table->string('owner_last_name');

            $table->foreignUuId('vehicle_make_model_id')->references('id')->on('vehicle_make_models')->onDelete('cascade');
            $table->foreignUuId('vehicle_type_id')->references('id')->on('vehicle_types')->onDelete('cascade');
            
            $table->string('truck_no')->unique();

            $table->date('last_service_date');
            $table->date('next_service_due_date');
            $table->date('insurance_expiry_date');
            $table->date('vehicle_license_expiry_date');

            $table->string('base_site');

            $table->string('status')->default(1)->comment('1 for active, 2 for serviceable, 0 for Unserviceable');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vehicles');
    }
};
