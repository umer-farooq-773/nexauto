<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('waybills', function (Blueprint $table) {
            $table->string('quantity_unit', 10)->nullable()->after('quantity');
            $table->string('weight_unit', 10)->nullable()->after('cargo_weight_kg');
            $table->string('volume_unit', 10)->nullable()->after('weight_unit');
        });
    }

    public function down(): void
    {
        Schema::table('waybills', function (Blueprint $table) {
            $table->dropColumn(['quantity_unit', 'weight_unit', 'volume_unit']);
        });
    }
};
