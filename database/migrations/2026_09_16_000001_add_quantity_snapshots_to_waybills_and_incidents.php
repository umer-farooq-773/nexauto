<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('waybills', function (Blueprint $table) {
            $table->decimal('quantity', 12, 2)->nullable()->after('cargo_description');
        });

        Schema::table('illegal_haulages', function (Blueprint $table) {
            $table->decimal('declared_quantity', 12, 2)->nullable()->after('waybill_id');
            $table->decimal('observed_quantity', 12, 2)->nullable()->after('declared_quantity');
            $table->decimal('difference_quantity', 12, 2)->nullable()->after('observed_quantity');
        });

        Schema::table('illegal_shortages', function (Blueprint $table) {
            $table->decimal('declared_quantity', 12, 2)->nullable()->after('waybill_id');
            $table->decimal('observed_quantity', 12, 2)->nullable()->after('declared_quantity');
        });
    }

    public function down(): void
    {
        Schema::table('illegal_shortages', function (Blueprint $table) {
            $table->dropColumn(['declared_quantity', 'observed_quantity']);
        });

        Schema::table('illegal_haulages', function (Blueprint $table) {
            $table->dropColumn(['declared_quantity', 'observed_quantity', 'difference_quantity']);
        });

        Schema::table('waybills', function (Blueprint $table) {
            $table->dropColumn('quantity');
        });
    }
};
