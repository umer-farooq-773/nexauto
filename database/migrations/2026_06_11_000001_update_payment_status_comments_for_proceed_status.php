<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = [
        'vehicle_breakdowns',
        'vehicle_bogged_downs',
        'illegal_detentions',
        'accidents',
    ];

    public function up(): void
    {
        $this->updatePaymentStatusComment('0=Pending, 1=Approved, 2=Proceed');
    }

    public function down(): void
    {
        $this->updatePaymentStatusComment('0=Pending, 1=Approved');
    }

    private function updatePaymentStatusComment(string $comment): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        foreach ($this->tables as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            DB::statement(sprintf(
                "ALTER TABLE `%s` MODIFY `payment_status` VARCHAR(255) NOT NULL DEFAULT '0' COMMENT '%s'",
                str_replace('`', '``', $table),
                str_replace("'", "''", $comment)
            ));
        }
    }
};
