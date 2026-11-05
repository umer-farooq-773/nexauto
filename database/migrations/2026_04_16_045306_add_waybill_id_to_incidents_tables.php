<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = ['impoundments', 'vehicle_breakdowns', 'illegal_detentions', 'accidents'];
        
        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->foreignUuid('waybill_id')->nullable()->after('vehicle_id')->constrained('waybills')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        $tables = ['impoundments', 'vehicle_breakdowns', 'illegal_detentions', 'accidents'];
        
        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropForeign(['waybill_id']);
                $table->dropColumn('waybill_id');
            });
        }
    }
};
