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
        Schema::create('blood_sugar', function (Blueprint $table) {
            $table->id();
            $table->string('level');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('blood_sugar_advice_id')->constrained('blood_sugar_advice')->cascadeOnDelete();
            $table->foreignId('blood_sugar_statuses_id')->constrained('blood_sugar_statuses')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blood_sugar');
    }
};
