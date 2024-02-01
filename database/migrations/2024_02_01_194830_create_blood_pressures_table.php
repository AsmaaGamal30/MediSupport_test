<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('blood_pressures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('pressure_advice_id')->nullable();
            $table->integer('systolic');
            $table->integer('diastolic');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('pressure_advice_id')->references('id')->on('pressure_advice')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blood_pressures');
    }
};
