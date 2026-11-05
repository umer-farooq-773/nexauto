<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicle_maintenances', function (Blueprint $table) {
            $table->boolean('client_approved')->default(false)->after('status');
            $table->timestamp('client_approved_at')->nullable()->after('client_approved');
            $table->string('client_approved_by')->nullable()->after('client_approved_at');
            $table->text('client_approved_comment')->nullable()->after('client_approved_by');
            $table->string('payment_status')->default('0')->comment('0=Pending, 1=Approved')->after('client_approved_comment');
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_maintenances', function (Blueprint $table) {
            $table->dropColumn([
                'client_approved',
                'client_approved_at',
                'client_approved_by',
                'client_approved_comment',
                'payment_status',
            ]);
        });
    }
};
