<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incident_chats', function (Blueprint $table) {
            // Track which users have read this message
            $table->json('read_by')->nullable()->after('attachment');
            // Store the incident URL for redirect
            $table->string('incident_url')->nullable()->after('read_by');
        });
    }

    public function down(): void
    {
        Schema::table('incident_chats', function (Blueprint $table) {
            $table->dropColumn(['read_by', 'incident_url']);
        });
    }
};
