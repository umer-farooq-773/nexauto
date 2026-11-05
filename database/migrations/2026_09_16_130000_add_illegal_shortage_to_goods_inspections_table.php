<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('goods_inspections', function (Blueprint $table) {
            if (!Schema::hasColumn('goods_inspections', 'illegal_shortage')) {
                $table->string('illegal_shortage')->nullable()->default('No')->after('illegal_haulage');
            }
        });
    }

    public function down(): void
    {
        Schema::table('goods_inspections', function (Blueprint $table) {
            if (Schema::hasColumn('goods_inspections', 'illegal_shortage')) {
                $table->dropColumn('illegal_shortage');
            }
        });
    }
};
