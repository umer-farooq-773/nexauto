<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        try {
            Schema::table('wialon_report_results', function (Blueprint $table) {
                $table->dropForeign(['wialon_report_template_id']);
            });
        } catch (\Throwable $e) {}

        if (!Schema::hasTable('wialon_report_templates')) {
            Schema::create('wialon_report_templates', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->unsignedBigInteger('wialon_resource_id');
                $table->unsignedBigInteger('wialon_template_id');
                $table->string('name');
                $table->string('type')->nullable();
                $table->timestamp('synced_at')->nullable();
                $table->string('status')->default('1');
                $table->timestamps();
            });
        }

        try {
            Schema::table('wialon_report_results', function (Blueprint $table) {
                $table->foreign('wialon_report_template_id')
                    ->references('id')
                    ->on('wialon_report_templates')
                    ->onDelete('cascade');
            });
        } catch (\Throwable $e) {}
    }

    public function down()
    {
    }
};
