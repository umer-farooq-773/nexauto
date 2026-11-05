<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('vehicle_maintenance_maintenance_type', function (Blueprint $table) {
            $table->uuid('vehicle_maintenance_id');
            $table->uuid('vehicle_maintenance_type_id');
            
            $table->foreign('vehicle_maintenance_id', 'vm_maintenance_id_foreign')
                  ->references('id')
                  ->on('vehicle_maintenances')
                  ->onDelete('cascade');
            
            $table->foreign('vehicle_maintenance_type_id', 'vm_type_id_foreign')
                  ->references('id')
                  ->on('vehicle_maintenance_types')
                  ->onDelete('cascade');
            
            $table->primary(['vehicle_maintenance_id', 'vehicle_maintenance_type_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('vehicle_maintenance_maintenance_type');
    }
};
