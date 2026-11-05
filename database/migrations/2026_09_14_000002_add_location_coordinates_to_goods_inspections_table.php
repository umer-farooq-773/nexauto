<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('goods_inspections', function (Blueprint $table) {
            if (!Schema::hasColumn('goods_inspections', 'inspection_location_lat')) {
                $table->decimal('inspection_location_lat', 10, 7)->nullable()->after('inspection_location');
            }
            if (!Schema::hasColumn('goods_inspections', 'inspection_location_lng')) {
                $table->decimal('inspection_location_lng', 10, 7)->nullable()->after('inspection_location_lat');
            }
        });
    }

    public function down(): void
    {
        Schema::table('goods_inspections', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('goods_inspections', 'inspection_location_lat')) {
                $columns[] = 'inspection_location_lat';
            }
            if (Schema::hasColumn('goods_inspections', 'inspection_location_lng')) {
                $columns[] = 'inspection_location_lng';
            }
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
