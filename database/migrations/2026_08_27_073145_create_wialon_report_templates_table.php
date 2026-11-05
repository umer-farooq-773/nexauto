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
        Schema::create('wialon_report_templates', function (Blueprint $table) {
            $table->UUID('id')->primary();
            $table->unsignedBigInteger('wialon_resource_id');
            $table->unsignedBigInteger('wialon_template_id');
            $table->string('name');
            $table->string('type')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->string('status')->default(1);

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
        Schema::dropIfExists('wialon_report_templates');
    }
};
