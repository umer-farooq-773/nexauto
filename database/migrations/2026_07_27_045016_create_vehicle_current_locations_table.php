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
        if (!Schema::hasTable('vehicle_current_locations')) {
            Schema::create('vehicle_current_locations', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
                $table->foreignUuid('waybill_id')->nullable()->constrained('waybills')->nullOnDelete();

                $table->decimal('latitude', 10, 7);
                $table->decimal('longitude', 10, 7);

                $table->decimal('speed_kmh', 8, 2)->default(0);
                $table->integer('heading')->nullable();

                $table->boolean('ignition')->default(false);
                $table->string('engine_status', 20)->nullable();

                $table->decimal('fuel_level', 8, 2)->nullable();
                $table->decimal('odometer_km', 12, 2)->nullable();

                $table->text('address')->nullable();

                $table->timestamp('gps_time');
                $table->timestamp('last_received_at')->useCurrent();

                $table->timestamps();

                $table->index(['latitude', 'longitude']);
                $table->index('gps_time');
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
        Schema::dropIfExists('vehicle_current_locations');
    }
};
