<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $inspectionIdIndexed = collect(Schema::getIndexes('goods_inspections'))
            ->contains(fn (array $index) => in_array('id', $index['columns'], true));

        if (!$inspectionIdIndexed) {
            Schema::table('goods_inspections', function (Blueprint $blueprint) {
                $blueprint->index('id', 'goods_inspections_id_index');
            });
        }

        $tables = [
            'illegal_haulages',
            'illegal_shortages',
            'route_diversions',
            'breathaliser_tests',
            'driver_misconducts',
        ];

        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            $this->ensureInspectionForeignKey($table, false);
        }

        if (!Schema::hasTable('goods_inspection_findings')) {
            Schema::create('goods_inspection_findings', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->char('inspection_id', 36)->charset('ascii')->collation('ascii_general_ci');
                $table->string('finding_type');
                $table->text('remarks')->nullable();
                $table->string('status')->default('0');
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->unique(['inspection_id', 'finding_type']);
            });
        }

        $this->ensureInspectionForeignKey('goods_inspection_findings', true);
    }

    public function down(): void
    {
        Schema::dropIfExists('goods_inspection_findings');

        $tables = [
            'illegal_haulages',
            'illegal_shortages',
            'route_diversions',
            'breathaliser_tests',
            'driver_misconducts',
        ];

        foreach ($tables as $table) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'inspection_id')) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropUnique(['inspection_id']);
                $blueprint->dropConstrainedForeignId('inspection_id');
            });
        }
    }

    private function ensureInspectionForeignKey(string $table, bool $cascade): void
    {
        if (!Schema::hasColumn($table, 'inspection_id')) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->char('inspection_id', 36)
                    ->charset('ascii')
                    ->collation('ascii_general_ci')
                    ->nullable()
                    ->after('id');
            });
        } else {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->char('inspection_id', 36)
                    ->charset('ascii')
                    ->collation('ascii_general_ci')
                    ->nullable()
                    ->change();
            });
        }

            DB::statement(
                "UPDATE `{$table}` incident
                 LEFT JOIN `goods_inspections` inspection ON inspection.id = incident.inspection_id
                 SET incident.inspection_id = NULL
                 WHERE incident.inspection_id IS NOT NULL AND inspection.id IS NULL"
            );

        $foreignKeyExists = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $table)
            ->where('COLUMN_NAME', 'inspection_id')
            ->where('REFERENCED_TABLE_NAME', 'goods_inspections')
            ->exists();

        if (!$foreignKeyExists) {
            Schema::table($table, function (Blueprint $blueprint) use ($cascade) {
                $foreign = $blueprint->foreign('inspection_id')
                    ->references('id')
                    ->on('goods_inspections');

                $cascade ? $foreign->cascadeOnDelete() : $foreign->nullOnDelete();
            });
        }

        if (!$cascade) {
            $indexExists = collect(Schema::getIndexes($table))
                ->contains(fn (array $index) => $index['columns'] === ['inspection_id']);

            if (!$indexExists) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->unique('inspection_id');
                });
            }
        }
    }
};
