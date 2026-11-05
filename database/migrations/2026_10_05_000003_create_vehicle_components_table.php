<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_components', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');

            $table->string('category'); // tyre, battery, telematics, other
            $table->string('component_name'); // Tyre, Battery, GPS Tracker, FLS Sensor, Dashcam
            $table->string('location'); // Rear Axle 1 – Left Outer, Front Left, Main Battery 1, Cab
            $table->string('serial_no'); // TYR-000892, BAT-00876, 863YYYYYYYY
            $table->string('brand')->nullable(); // Michelin, Exide, Teltonika
            $table->string('model')->nullable(); // Pattern / Spec
            $table->string('size')->nullable(); // 24R18.22, 12V 180Ah

            $table->date('installed_date')->nullable();
            $table->string('installed_odometer')->nullable();

            $table->string('status')->default('active'); // active, removed, replaced, discarded
            $table->date('removed_date')->nullable();
            $table->string('removed_reason')->nullable(); // Replaced during MT-000125, Faulty, Worn

            $table->foreignUuid('vehicle_maintenance_id')->nullable()->references('id')->on('vehicle_maintenances')->nullOnDelete();
            $table->foreignUuid('vehicle_maintenance_item_id')->nullable()->references('id')->on('vehicle_maintenance_items')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_components');
    }
};
