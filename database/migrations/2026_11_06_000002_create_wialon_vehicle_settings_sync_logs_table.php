<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wialon_vehicle_settings_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('direction', 10)->default('push');
            $table->string('status', 10);
            $table->json('settings')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();

            $table->index(['vehicle_id', 'created_at']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wialon_vehicle_settings_sync_logs');
    }
};
