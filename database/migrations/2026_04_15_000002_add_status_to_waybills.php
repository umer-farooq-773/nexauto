<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('waybills', function (Blueprint $table) {
            // Add status_id foreign key column after driver_id
            $table->foreignUuid('status_id')->nullable()->after('driver_id')->constrained('statuses')->onDelete('set null');
        });

        // Migrate existing status values to the new table
        $this->migrateExistingStatuses();

        // Drop the old status column
        Schema::table('waybills', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }

    public function down()
    {
        Schema::table('waybills', function (Blueprint $table) {
            $table->tinyInteger('status')->default(0);
            $table->dropConstrainedForeignId('status_id');
        });
    }

    private function migrateExistingStatuses()
    {
        $waybills = DB::table('waybills')->get();
        $statuses = DB::table('statuses')->get()->keyBy('value');

        foreach ($waybills as $waybill) {
            if (isset($statuses[$waybill->status])) {
                DB::table('waybills')
                    ->where('id', $waybill->id)
                    ->update(['status_id' => $statuses[$waybill->status]->id]);
            }
        }
    }
};
