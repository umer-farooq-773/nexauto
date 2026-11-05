<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('driver_mates', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('first_name');
            $table->string('last_name');
            $table->string('address');
            $table->string('phone_number');

            $table->date('date_of_birth')->nullable();
            $table->string('national_id_passport_number')->unique();

            $table->date('date_employed')->nullable();

            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone_number')->nullable();

            $table->boolean('status')->default(1);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('driver_mates');
    }
};
