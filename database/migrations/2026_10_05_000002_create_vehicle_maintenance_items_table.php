<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_maintenance_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('vehicle_maintenance_id')->references('id')->on('vehicle_maintenances')->onDelete('cascade');

            $table->string('category'); // tyre, battery, telematics, brake, engine, general
            $table->string('action')->default('replace'); // replace, repair, rotate, install, inspect, service, recharge, calibrate
            $table->string('location')->nullable(); // Rear Axle 1 – Left Outer, Front Left, Main Battery 1, Cab / Dashboard
            $table->string('axle')->nullable(); // Front Axle, Rear Axle 1, Rear Axle 2, Trailer Axle 1
            $table->string('position')->nullable(); // Left Outer, Left Inner, Right Outer, Right Inner, Left, Right
            $table->string('item_name')->nullable(); // Tyre, Main Battery, GPS Tracker, Brake Pads
            $table->string('brand')->nullable(); // Michelin, Exide, Teltonika, Bosch
            $table->string('model')->nullable(); // Pattern / Model / Spec (e.g. FMC920, 24R18.22)
            $table->string('size')->nullable(); // 24R18.22, 12V 180Ah
            $table->string('old_serial_no')->nullable(); // TYR-000457, BAT-00124, 863XXXXXXXX
            $table->string('new_serial_no')->nullable(); // TYR-000892, BAT-00876, 863YYYYYYYY
            $table->decimal('tread_depth_mm', 6, 2)->nullable(); // 32.00 mm
            $table->string('reason')->nullable(); // Worn, Failed, Faulty, Damaged, Scheduled
            $table->integer('quantity')->default(1);
            $table->decimal('unit_cost', 12, 2)->nullable();
            $table->decimal('cost', 12, 2)->nullable();
            $table->text('notes')->nullable();
            $table->string('photo_path')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_maintenance_items');
    }
};
