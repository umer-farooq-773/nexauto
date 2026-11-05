<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('impoundments', function (Blueprint $table) {
            $table->boolean('client_approved')->default(false)->after('status');

            $table->timestamp('client_approved_at')->nullable()->after('client_approved');

            $table->uuid('client_approved_by')->nullable()->after('client_approved_at');

            $table->index('client_approved');

            $table->index('client_approved_at');
        });
    }

    public function down()
    {
        Schema::table('impoundments', function (Blueprint $table) {
            $table->dropForeign(['client_approved_by']);
            $table->dropIndex(['client_approved']);
            $table->dropIndex(['client_approved_at']);

            $table->dropColumn(['client_approved', 'client_approved_at', 'client_approved_by']);
        });
    }
};
