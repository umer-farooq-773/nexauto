<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('illegal_shortages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('driver_id')->constrained('driver_details')->cascadeOnDelete();
            $table->foreignUuid('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->foreignUuid('waybill_id')->constrained('waybills')->cascadeOnDelete();
            $table->date('shortage_date');
            $table->string('shortage_location');
            $table->decimal('shortage_location_lat', 10, 7)->nullable();
            $table->decimal('shortage_location_lng', 10, 7)->nullable();
            $table->string('shortage_type');
            $table->string('shortage_quantity')->nullable();
            $table->string('cargo_description', 1000)->nullable();
            $table->string('severity')->default('minor');
            $table->string('action_taken', 1000)->nullable();
            $table->text('remarks')->nullable();
            $table->string('status')->default('0')->comment('0=Open, 1=Under Review, 2=Resolved');
            $table->boolean('client_approved')->default(false);
            $table->timestamp('client_approved_at')->nullable();
            $table->string('client_approved_by')->nullable();
            $table->text('client_approved_comment')->nullable();
            $table->string('payment_status')->default('0')->comment('0=Pending, 1=Approved');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('illegal_shortages');
    }
};
