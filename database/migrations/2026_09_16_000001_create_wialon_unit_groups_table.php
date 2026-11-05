<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('wialon_unit_groups', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('wialon_group_id')->unique();
            $table->string('name');
            $table->unsignedInteger('units_count')->default(0);
            $table->timestamp('synced_at')->nullable();
            $table->string('status')->default('1');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wialon_unit_groups');
    }
};
