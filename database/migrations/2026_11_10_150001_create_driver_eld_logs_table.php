<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('driver_eld_logs')) {
            Schema::create('driver_eld_logs', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('driver_id')->constrained('driver_details')->cascadeOnDelete();
                $table->foreignUuid('waybill_id')->nullable()->constrained('waybills')->nullOnDelete();
                $table->foreignUuid('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();

                $table->string('event_type', 50)->default('driving');
                $table->string('status', 50)->default('on_duty');
                $table->decimal('latitude', 10, 7)->nullable();
                $table->decimal('longitude', 10, 7)->nullable();
                $table->string('location_name')->nullable();
                $table->decimal('speed_kmh', 8, 2)->default(0);

                $table->unsignedInteger('elapsed_seconds')->default(0);
                $table->integer('remaining_seconds')->nullable();
                $table->unsignedInteger('planned_seconds')->nullable();

                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['driver_id', 'created_at']);
                $table->index(['waybill_id', 'event_type']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_eld_logs');
    }
};

