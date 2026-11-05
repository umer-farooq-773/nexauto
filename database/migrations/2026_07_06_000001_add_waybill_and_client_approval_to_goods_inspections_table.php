<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('goods_inspections', function (Blueprint $table) {
            // waybill FK — placed after vehicle_id if not already present
            if (!Schema::hasColumn('goods_inspections', 'waybill_id')) {
                $table->foreignUuid('waybill_id')
                    ->nullable()
                    ->after('vehicle_id')
                    ->constrained('waybills')
                    ->onDelete('cascade');
            }

            // Client approval columns (matching the pattern used by all other incident tables)
            if (!Schema::hasColumn('goods_inspections', 'client_approved')) {
                $table->boolean('client_approved')->default(false)->after('status');
                $table->timestamp('client_approved_at')->nullable()->after('client_approved');
                $table->string('client_approved_by')->nullable()->after('client_approved_at');
                $table->text('client_approved_comment')->nullable()->after('client_approved_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('goods_inspections', function (Blueprint $table) {
            try {
                $table->dropForeign(['waybill_id']);
                $table->dropColumn('waybill_id');
            } catch (QueryException $e) {
                // ignore if not present
            }

            try {
                $table->dropColumn(['client_approved', 'client_approved_at', 'client_approved_by', 'client_approved_comment']);
            } catch (QueryException $e) {
                // ignore if not present
            }
        });
    }
};
