<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        // Check if columns already exist
        $columns = $this->getTableColumns('payment_histories');

        // Step 1: Add temporary TEXT shadow columns if they don't exist
        if (!in_array('payment_method_json', $columns)) {
            DB::statement("ALTER TABLE payment_histories ADD COLUMN payment_method_json LONGTEXT NULL AFTER payment_method");
        }
        if (!in_array('reference_number_json', $columns)) {
            DB::statement("ALTER TABLE payment_histories ADD COLUMN reference_number_json LONGTEXT NULL AFTER reference_number");
        }
        if (!in_array('notes_json', $columns)) {
            DB::statement("ALTER TABLE payment_histories ADD COLUMN notes_json LONGTEXT NULL AFTER notes");
        }

        // Step 2: Convert existing values to valid JSON in the shadow columns
        $records = DB::table('payment_histories')->get([
            'id', 'payment_method', 'reference_number', 'notes'
        ]);

        foreach ($records as $record) {
            DB::table('payment_histories')->where('id', $record->id)->update([
                'payment_method_json'   => $this->toJsonScalar($record->payment_method),
                'reference_number_json' => $this->toJsonScalar($record->reference_number),
                'notes_json'            => $this->toJsonScalar($record->notes),
            ]);
        }

        // Step 3: Drop original columns
        if (in_array('payment_method', $columns)) {
            DB::statement("ALTER TABLE payment_histories DROP COLUMN payment_method");
        }
        if (in_array('reference_number', $columns)) {
            DB::statement("ALTER TABLE payment_histories DROP COLUMN reference_number");
        }
        if (in_array('notes', $columns)) {
            DB::statement("ALTER TABLE payment_histories DROP COLUMN notes");
        }

        // Step 4: Rename shadow columns to use LONGTEXT (MariaDB uses LONGTEXT for JSON)
        DB::statement("ALTER TABLE payment_histories CHANGE COLUMN payment_method_json payment_method LONGTEXT NULL");
        DB::statement("ALTER TABLE payment_histories CHANGE COLUMN reference_number_json reference_number LONGTEXT NULL");
        DB::statement("ALTER TABLE payment_histories CHANGE COLUMN notes_json notes LONGTEXT NULL");

        // Step 5: Add JSON validation constraint (optional - for MariaDB 10.2+)
        // This ensures the data is valid JSON
        try {
            DB::statement("ALTER TABLE payment_histories ADD CONSTRAINT check_payment_method_json CHECK (JSON_VALID(payment_method))");
            DB::statement("ALTER TABLE payment_histories ADD CONSTRAINT check_reference_number_json CHECK (JSON_VALID(reference_number))");
            DB::statement("ALTER TABLE payment_histories ADD CONSTRAINT check_notes_json CHECK (JSON_VALID(notes))");
        } catch (\Exception $e) {
            // Constraints might not be supported in older MariaDB versions, so ignore
        }
    }

    public function down(): void
    {
        // Drop JSON validation constraints if they exist
        try {
            DB::statement("ALTER TABLE payment_histories DROP CONSTRAINT check_payment_method_json");
            DB::statement("ALTER TABLE payment_histories DROP CONSTRAINT check_reference_number_json");
            DB::statement("ALTER TABLE payment_histories DROP CONSTRAINT check_notes_json");
        } catch (\Exception $e) {
            // Constraints might not exist, so ignore
        }

        $columns = $this->getTableColumns('payment_histories');

        // Step 1: Add temporary TEXT shadow columns
        if (!in_array('payment_method_text', $columns)) {
            DB::statement("ALTER TABLE payment_histories ADD COLUMN payment_method_text TEXT NULL AFTER payment_method");
        }
        if (!in_array('reference_number_text', $columns)) {
            DB::statement("ALTER TABLE payment_histories ADD COLUMN reference_number_text TEXT NULL AFTER reference_number");
        }
        if (!in_array('notes_text', $columns)) {
            DB::statement("ALTER TABLE payment_histories ADD COLUMN notes_text TEXT NULL AFTER notes");
        }

        // Step 2: Convert JSON values back to text
        $records = DB::table('payment_histories')->get([
            'id', 'payment_method', 'reference_number', 'notes'
        ]);

        foreach ($records as $record) {
            DB::table('payment_histories')->where('id', $record->id)->update([
                'payment_method_text'   => $this->fromJsonScalar($record->payment_method),
                'reference_number_text' => $this->fromJsonScalar($record->reference_number),
                'notes_text'            => $this->fromJsonScalar($record->notes),
            ]);
        }

        // Step 3: Drop LONGTEXT/JSON columns
        if (in_array('payment_method', $columns)) {
            DB::statement("ALTER TABLE payment_histories DROP COLUMN payment_method");
        }
        if (in_array('reference_number', $columns)) {
            DB::statement("ALTER TABLE payment_histories DROP COLUMN reference_number");
        }
        if (in_array('notes', $columns)) {
            DB::statement("ALTER TABLE payment_histories DROP COLUMN notes");
        }

        // Step 4: Rename and convert back to TEXT
        DB::statement("ALTER TABLE payment_histories CHANGE COLUMN payment_method_text payment_method TEXT NULL");
        DB::statement("ALTER TABLE payment_histories CHANGE COLUMN reference_number_text reference_number TEXT NULL");
        DB::statement("ALTER TABLE payment_histories CHANGE COLUMN notes_text notes TEXT NULL");
    }

    private function toJsonScalar(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        json_decode($value);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $value;
        }

        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    private function fromJsonScalar(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $decoded = json_decode($value, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $value;
        }

        if (is_string($decoded)) {
            return $decoded;
        }

        return $value;
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
