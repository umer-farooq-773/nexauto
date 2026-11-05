<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_misconducts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('driver_id')->constrained('driver_details')->cascadeOnDelete();
            $table->foreignUuid('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->foreignUuid('waybill_id')->constrained('waybills')->cascadeOnDelete();
            $table->date('misconduct_date');
            $table->string('misconduct_location');
            $table->string('misconduct_type');
            $table->string('severity')->default('minor');
            $table->string('action_taken', 1000)->nullable();
            $table->text('remarks')->nullable();
            $table->string('status')->default('0')->comment('0=Open, 1=Under Review, 2=Resolved');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_misconducts');
    }
};
