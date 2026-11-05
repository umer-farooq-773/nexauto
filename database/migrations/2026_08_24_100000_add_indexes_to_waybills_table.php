<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('waybills', function (Blueprint $table) {
            // Add index on created_at for efficient sorting/ordering
            $table->index('created_at');
            
            // Add composite index for common query patterns
            $table->index(['status_id', 'created_at']);
            
            // Add index on vehicle_id for faster joins
            $table->index('vehicle_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('waybills', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['status_id', 'created_at']);
            $table->dropIndex(['vehicle_id']);
        });
    }
};
