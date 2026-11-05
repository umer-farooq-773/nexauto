<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('goods_inspections', 'waybill_id')) {
            Schema::table('goods_inspections', function (Blueprint $table) {
                $table->foreignUuid('waybill_id')
                    ->nullable()
                    ->after('vehicle_id')
                    ->constrained('waybills')
                    ->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('goods_inspections', 'waybill_id')) {
            Schema::table('goods_inspections', function (Blueprint $table) {
                $table->dropForeign(['waybill_id']);
                $table->dropColumn('waybill_id');
            });
        }
    }
};
