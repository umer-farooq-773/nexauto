<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('vehicle_breakdowns', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
            $table->date('breakdown_date');
            $table->string('breakdown_location');
            $table->string('breakdown_type')->nullable();
            $table->text('description')->nullable();
            $table->date('resolved_date')->nullable();
            $table->decimal('repair_cost', 10, 2)->nullable();
            $table->string('status')->default('0')->comment('0=Pending, 1=Resolved, 2=In Progress');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vehicle_breakdowns');
    }
};
