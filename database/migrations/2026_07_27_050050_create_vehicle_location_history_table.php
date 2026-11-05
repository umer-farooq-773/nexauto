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
        if (!Schema::hasTable('vehicle_location_history')) {
            Schema::create('vehicle_location_history', function (Blueprint $table) {
                $table->uuid('id')->primary();

                $table->foreignUuid('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
                $table->foreignUuid('waybill_id')->nullable()->constrained('waybills')->nullOnDelete();

                $table->decimal('latitude', 10, 7);
                $table->decimal('longitude', 10, 7);

                $table->decimal('speed_kmh', 8, 2)->default(0);
                $table->integer('heading')->nullable();

                $table->boolean('ignition')->default(false);

                $table->decimal('fuel_level', 8, 2)->nullable();
                $table->decimal('odometer_km', 12, 2)->nullable();

                $table->timestamp('gps_time');

                $table->timestamps();

                $table->index(['vehicle_id', 'gps_time']);
                $table->index(['waybill_id', 'gps_time']);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vehicle_location_history');
    }
};
