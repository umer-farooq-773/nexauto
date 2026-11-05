<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('illegal_detentions', function (Blueprint $table) {
            $table->decimal('cost', 10, 2)->nullable()->after('detention_location');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('illegal_detentions', 'cost')) {
            Schema::table('illegal_detentions', function (Blueprint $table) {
                $table->dropColumn('cost');
            });
        }
    }
};
