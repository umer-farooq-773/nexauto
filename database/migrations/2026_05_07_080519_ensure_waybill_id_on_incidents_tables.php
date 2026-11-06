<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;

return new class extends Migration
{
    private array $tables = [
        'impoundments',
        'vehicle_breakdowns',
        'illegal_detentions',
        'accidents',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            try {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->foreignUuid('waybill_id')
                        ->nullable()
                        ->after('vehicle_id')
                        ->constrained('waybills')
                        ->onDelete('cascade');
                });
            } catch (QueryException $e) {
                // If column already exists, ignore the error
                if (str_contains(strtolower($e->getMessage()), 'duplicate column name')) {
                    continue;
                }
                throw $e;
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            try {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropForeign(['waybill_id']);
                    $table->dropColumn('waybill_id');
                });
            } catch (QueryException $e) {
                // If column doesn't exist or foreign key doesn't exist, ignore
                if (str_contains(strtolower($e->getMessage()), 'unknown column') ||
                    str_contains(strtolower($e->getMessage()), 'cannot drop index')) {
                    continue;
                }
                throw $e;
            }
        }
    }
};
