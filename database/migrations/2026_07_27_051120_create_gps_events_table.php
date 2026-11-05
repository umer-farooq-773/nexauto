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
        if (!Schema::hasTable('gps_events')) {
            Schema::create('gps_events', function (Blueprint $table) {
                $table->uuid('id')->primary();

                $table->foreignUuid('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
                $table->foreignUuid('waybill_id')->nullable()->constrained('waybills')->nullOnDelete();

                $table->string('event_type', 50);

                $table->decimal('latitude', 10, 7)->nullable();
                $table->decimal('longitude', 10, 7)->nullable();

                $table->decimal('speed_kmh', 8, 2)->nullable();

                $table->text('description')->nullable();

                $table->timestamp('event_time');

                $table->timestamps();

                $table->index(['vehicle_id', 'event_time']);
                $table->index('event_type');
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
        Schema::dropIfExists('gps_events');
    }
};
