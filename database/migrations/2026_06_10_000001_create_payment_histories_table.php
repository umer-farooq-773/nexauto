<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Polymorphic relation: morphable to any incident model
            $table->string('payable_type');
            $table->uuid('payable_id');
            $table->index(['payable_type', 'payable_id']);

            // Cost tracking
            $table->decimal('original_cost', 10, 2)->nullable()->comment('Cost at time of payment submission');
            $table->decimal('paid_amount', 10, 2)->nullable()->comment('Amount entered in payment modal (may differ)');

            // Payment details
            $table->string('payment_method')->nullable()->comment('cash, bank_transfer, credit_card, cheque');
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();

            // Who processed
            $table->string('processed_by')->nullable();
            $table->string('processed_by_name')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_histories');
    }
};
