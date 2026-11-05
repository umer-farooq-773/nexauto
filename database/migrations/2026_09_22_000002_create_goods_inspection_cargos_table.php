<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('goods_inspection_cargos')) {
            Schema::create('goods_inspection_cargos', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->foreignUuid('inspection_id')->constrained('goods_inspections')->cascadeOnDelete();
                $table->foreignUuid('waybill_cargo_id')->nullable()->constrained('waybill_cargos')->nullOnDelete();
                $table->string('cargo_description')->nullable();
                $table->decimal('declared_quantity', 12, 2)->nullable();
                $table->decimal('observed_quantity', 12, 2)->nullable();
                $table->decimal('difference_quantity', 12, 2)->nullable();
                $table->string('quantity_unit', 20)->nullable();
                $table->decimal('cargo_weight_kg', 12, 2)->nullable();
                $table->string('weight_unit', 20)->nullable();
                $table->string('volume_unit', 20)->nullable();
                $table->string('goods_match_waybill', 20)->nullable()->default('Yes');
                $table->string('illegal_shortage', 20)->nullable()->default('No');
                $table->string('illegal_haulage', 20)->nullable()->default('No');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('goods_inspection_cargos');
    }
};
