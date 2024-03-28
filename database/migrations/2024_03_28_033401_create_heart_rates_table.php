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
        Schema::create('heart_rates', function (Blueprint $table) {
            $table->id();
            $table->float('heart_rate');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('heart_rate_advice_id')->constrained('heart_rate_advice')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('heart_rates');
    }
};
