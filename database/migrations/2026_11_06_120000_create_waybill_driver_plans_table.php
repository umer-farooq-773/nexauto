<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driverIdIsUnique = collect(Schema::getIndexes('driver_details'))->contains(fn (array $index) =>
            in_array('id', $index['columns'] ?? [], true)
            && (($index['unique'] ?? false) || ($index['primary'] ?? false))
        );

        if (!$driverIdIsUnique) {
            $hasDuplicateIds = DB::table('driver_details')
                ->select('id')
                ->groupBy('id')
                ->havingRaw('COUNT(*) > 1')
                ->exists();

            if ($hasDuplicateIds) {
                throw new RuntimeException('Cannot add waybill driver plans: driver_details contains duplicate UUID ids.');
            }

            Schema::table('driver_details', function (Blueprint $table) {
                $table->unique('id', 'driver_details_id_unique_for_waybill_plans');
            });
        }

        Schema::create('waybill_driver_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('waybill_id')->constrained('waybills')->cascadeOnDelete();
            $table->foreignUuid('driver_id')->constrained('driver_details')->cascadeOnDelete();
            $table->unsignedSmallInteger('sequence');
            $table->unsignedInteger('planned_seconds')->nullable();
            $table->enum('status', ['pending', 'active', 'completed', 'skipped'])->default('pending');
            $table->unsignedInteger('actual_driven_seconds')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->unique(['waybill_id', 'sequence']);
            $table->unique(['waybill_id', 'driver_id']);
            $table->index(['driver_id', 'status']);
        });

        DB::table('waybills')->whereNotNull('driver_id')->orderBy('id')->chunkById(500, function ($waybills): void {
            foreach ($waybills as $waybill) {
                $alreadyPlanned = DB::table('waybill_driver_plans')->where('waybill_id', $waybill->id)->exists();

                if (!$alreadyPlanned) {
                    DB::table('waybill_driver_plans')->insert([
                        'id' => (string) \Illuminate\Support\Str::uuid(),
                        'waybill_id' => $waybill->id,
                        'driver_id' => $waybill->driver_id,
                        'sequence' => 1,
                        'planned_seconds' => null,
                        'status' => 'pending',
                        'actual_driven_seconds' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }, 'id');
    }

    public function down(): void
    {
        Schema::dropIfExists('waybill_driver_plans');

        $indexExists = collect(Schema::getIndexes('driver_details'))
            ->contains(fn (array $index) => ($index['name'] ?? null) === 'driver_details_id_unique_for_waybill_plans');

        if ($indexExists) {
            Schema::table('driver_details', function (Blueprint $table) {
                $table->dropUnique('driver_details_id_unique_for_waybill_plans');
            });
        }
    }
};
