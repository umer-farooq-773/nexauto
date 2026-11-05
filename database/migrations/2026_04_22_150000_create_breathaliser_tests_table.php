<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('breathaliser_tests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('driver_id')->constrained('driver_details')->cascadeOnDelete();
            $table->foreignUuid('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->foreignUuid('waybill_id')->constrained('waybills')->cascadeOnDelete();
            $table->date('test_date');
            $table->string('test_location');
            $table->string('device_number')->nullable();
            $table->decimal('bac_level', 8, 3)->nullable();
            $table->string('test_result')->default('negative');
            $table->string('action_taken', 1000)->nullable();
            $table->text('remarks')->nullable();
            $table->string('status')->default('0')->comment('0=Open, 1=Under Review, 2=Closed');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('breathaliser_tests');
    }
};
