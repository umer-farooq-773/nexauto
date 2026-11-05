<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, check if columns exist using raw SQL
        $columns = $this->getTableColumns('payment_histories');

        Schema::table('payment_histories', function (Blueprint $table) use ($columns) {
            // Only add columns if they don't exist
            if (!in_array('paid_to', $columns)) {
                // Use LONGTEXT instead of JSON for MariaDB compatibility
                $table->longText('paid_to')->nullable()->after('paid_amount')
                      ->comment('JSON: recipient details, e.g. {"name":"Bank XYZ","account":"123"}');
            }

            if (!in_array('authority', $columns)) {
                $table->longText('authority')->nullable()->after('paid_to')
                      ->comment('JSON: authorizing body/person details');
            }

            if (!in_array('price', $columns)) {
                $table->longText('price')->nullable()->after('authority')
                      ->comment('JSON: price breakdown, e.g. {"base":1000,"tax":50,"total":1050}');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $columns = $this->getTableColumns('payment_histories');

        $columnsToDrop = [];
        if (in_array('paid_to', $columns)) $columnsToDrop[] = 'paid_to';
        if (in_array('authority', $columns)) $columnsToDrop[] = 'authority';
        if (in_array('price', $columns)) $columnsToDrop[] = 'price';

        if (!empty($columnsToDrop)) {
            Schema::table('payment_histories', function (Blueprint $table) use ($columnsToDrop) {
                $table->dropColumn($columnsToDrop);
            });
        }
    }

    private function getTableColumns(string $table): array
    {
        try {
            $result = DB::select("SHOW COLUMNS FROM `{$table}`");
            return array_map(function($column) {
                return $column->Field;
            }, $result);
        } catch (\Exception $e) {
            return [];
        }
    }
};
