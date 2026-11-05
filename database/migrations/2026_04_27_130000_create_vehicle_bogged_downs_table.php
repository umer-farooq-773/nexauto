<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_bogged_downs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('waybill_id')->nullable()->references('id')->on('waybills')->nullOnDelete();
            $table->foreignUuid('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
            $table->date('bogged_date');
            $table->string('stuck_location');
            $table->string('ground_condition')->nullable();
            $table->text('description')->nullable();
            $table->date('recovered_date')->nullable();
            $table->decimal('recovery_cost', 10, 2)->nullable();
            $table->string('status')->default('0')->comment('0=Stuck/Pending, 1=Recovered, 2=Recovery In Progress');
            $table->timestamps();
        });

        if (Schema::hasTable('vehicle_breakdowns')) {

            try {

                $legacyRows = DB::table('vehicle_breakdowns')
                    ->where('incident_type', 'bogged_down')
                    ->get();

                foreach ($legacyRows as $row) {
                    DB::table('vehicle_bogged_downs')->insert([
                        'id' => (string) Str::uuid(),
                        'waybill_id' => $row->waybill_id ?? null,
                        'vehicle_id' => $row->vehicle_id,
                        'bogged_date' => $row->breakdown_date,
                        'stuck_location' => $row->breakdown_location,
                        'ground_condition' => $row->breakdown_type,
                        'description' => $row->description,
                        'recovered_date' => $row->resolved_date,
                        'recovery_cost' => $row->repair_cost,
                        'status' => $row->status,
                        'created_at' => $row->created_at,
                        'updated_at' => $row->updated_at,
                    ]);
                }

                DB::table('vehicle_breakdowns')
                    ->where('incident_type', 'bogged_down')
                    ->delete();

            } catch (\Exception $e) {
                // Skip legacy migration if old table structure differs
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_bogged_downs');
    }
};
