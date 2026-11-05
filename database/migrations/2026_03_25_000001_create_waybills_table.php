<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('waybills', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('waybill_number')->unique();
            $table->foreignUuid('vehicle_id')->constrained('vehicles')->onDelete('cascade');
            $table->foreignUuid('driver_id')->constrained('driver_details')->onDelete('cascade');
            $table->foreignUuid('route_trip_id')->nullable()->constrained('route_trips')->onDelete('set null');

            $table->string('origin')->nullable();
            $table->string('destination')->nullable();
            $table->decimal('distance', 10, 2)->nullable();
            $table->string('estimated_duration')->nullable();

            $table->date('dispatch_date');
            $table->date('delivery_date')->nullable();
            $table->string('cargo_description')->nullable();
            $table->decimal('cargo_weight_kg', 10, 2)->nullable();
            $table->text('notes')->nullable();

            // 0=Awaiting, 1=Allocated, 2=In Transit, 3=Delivered, 4=Cancelled
            $table->tinyInteger('status')->default(0);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('waybills');
    }
};
