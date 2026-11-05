<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Directly add the column using raw SQL
        // Using try-catch to handle if column already exists
        try {
            DB::statement("ALTER TABLE payment_histories ADD COLUMN expense_details LONGTEXT NULL AFTER price");
        } catch (\Exception $e) {
            // If column already exists or other error, just continue
            // You might want to log this or check the error message
            if (!str_contains($e->getMessage(), 'Duplicate column')) {
                throw $e;
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            DB::statement("ALTER TABLE payment_histories DROP COLUMN expense_details");
        } catch (\Exception $e) {
            // If column doesn't exist, just continue
            if (!str_contains($e->getMessage(), 'Unknown column')) {
                throw $e;
            }
        }
    }
};
