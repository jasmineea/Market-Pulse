<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Mirrors BQ market_pulse_sales_trend for dashboard and Market Pulse charts.
     */
    public function up(): void
    {
        Schema::create('market_pulse_sales_trend', function (Blueprint $table) {
            $table->id();
            $table->date('month_date')->unique();
            $table->decimal('total_sales', 18, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('market_pulse_sales_trend');
    }
};
