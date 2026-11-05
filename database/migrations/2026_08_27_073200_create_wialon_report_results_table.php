<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('wialon_report_results')) {
            return;
        }

        Schema::create('wialon_report_results', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->unsignedBigInteger('wialon_unit_id');

            $table->foreignUuid('wialon_report_template_id')
                ->constrained('wialon_report_templates')
                ->cascadeOnDelete();

            $table->timestamp('interval_from');
            $table->timestamp('interval_to');
            $table->string('status')->default('pending');

            $table->text('error_message')->nullable();

            $table->string('export_path')->nullable();
            $table->string('export_format')->nullable();
            $table->string('name')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wialon_report_results');
    }
};
