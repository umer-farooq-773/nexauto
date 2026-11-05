<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('route_diversions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->foreignUuid('waybill_id')->constrained('waybills')->cascadeOnDelete();
            $table->date('diversion_date');
            $table->string('diversion_location');
            $table->string('original_route');
            $table->string('diverted_route');
            $table->string('diversion_reason', 1000);
            $table->date('expected_rejoin_date')->nullable();
            $table->date('actual_rejoin_date')->nullable();
            $table->text('remarks')->nullable();
            $table->string('status')->default('0')->comment('0=Active, 1=Completed, 2=Cancelled');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('route_diversions');
    }
};
