<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('waybill_cargos')) {
            Schema::create('waybill_cargos', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('waybill_id')->constrained('waybills')->cascadeOnDelete();
                $table->string('cargo_description')->nullable();
                $table->decimal('quantity', 12, 2)->nullable();
                $table->string('quantity_unit', 20)->nullable();
                $table->decimal('cargo_weight_kg', 12, 2)->nullable();
                $table->string('weight_unit', 20)->nullable();
                $table->string('volume_unit', 20)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('waybill_cargos');
    }
};
