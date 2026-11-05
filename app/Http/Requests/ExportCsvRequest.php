<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class ExportCsvRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'table' => 'required|string|max:255',
            'columns' => 'required|array|min:1',
            'columns.*' => 'string|max:255',
            'from_date' => 'nullable|date|date_format:Y-m-d',
            'to_date' => 'nullable|date|date_format:Y-m-d|after_or_equal:from_date'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'table.required' => 'Table name is required',
            'columns.required' => 'Please select at least one column',
            'columns.min' => 'Please select at least one column',
            'from_date.date_format' => 'From date must be in YYYY-MM-DD format',
            'to_date.date_format' => 'To date must be in YYYY-MM-DD format',
            'to_date.after_or_equal' => 'To date must be after or equal to From date'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('table')) {
            $this->merge([
                'table' => trim($this->input('table'))
            ]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $table = $this->input('table');
            $columns = $this->input('columns', []);

            if (!$table) {
                return;
            }

            $dbName = DB::connection()->getDatabaseName();

            // Check if table exists via INFORMATION_SCHEMA
            $tableExists = DB::select(
                'SELECT COUNT(*) as cnt FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?',
                [$dbName, $table]
            );

            if (($tableExists[0]->cnt ?? 0) === 0) {
                $validator->errors()->add('table', 'The specified table does not exist.');
                return;
            }

            // Check if columns exist in the table
            if (!empty($columns)) {
                $results = DB::select(
                    'SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?',
                    [$dbName, $table]
                );
                $allColumns = array_map(fn($col) => $col->COLUMN_NAME, $results);
                $invalidColumns = array_diff($columns, $allColumns);

                if (!empty($invalidColumns)) {
                    $validator->errors()->add('columns', 'Invalid columns: ' . implode(', ', $invalidColumns));
                }
            }
        });
    }
}
