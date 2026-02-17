<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Mirrors BQ market_pulse_monthly_kpis_joined for fast dashboard/Market Pulse reads.
     */
    public function up(): void
    {
        Schema::create('market_pulse_kpis', function (Blueprint $table) {
            $table->id();
            $table->date('month_date')->unique();
            $table->decimal('total_monthly_sales', 18, 2)->nullable();
            $table->unsignedBigInteger('total_transactions')->nullable();
            $table->decimal('avg_transaction_value', 18, 2)->nullable();
            $table->unsignedInteger('active_licenses')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('market_pulse_kpis');
    }
};
