<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_mate_pivot', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('driver_id')->constrained('driver_details')->cascadeOnDelete();
            $table->foreignUuid('driver_mate_id')->constrained('driver_mates')->cascadeOnDelete();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamps();

            $table->unique('driver_mate_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_mate_pivot');
    }
};