<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incident_chats', function (Blueprint $table) {
            $table->uuid('id')->primary();
            // The incident record UUID (impoundment, vehicle_bogged_down, accident, etc.)
            $table->string('incident_id');
            // The incident type slug: impoundment, vehicle_bogged_down, accident, etc.
            $table->string('incident_type');
            // The user who sent the message
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            // Role snapshot at time of message (admin, client_directors, etc.)
            $table->string('role_type')->nullable();
            // The message body
            $table->text('message');
            // Optional file attachment path
            $table->string('attachment')->nullable();
            $table->timestamps();

            $table->index(['incident_id', 'incident_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incident_chats');
    }
};
