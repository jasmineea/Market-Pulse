<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Mirrors BQ market_pulse_category_breakdown_by_month (month_date, category, category_revenue).
     */
    public function up(): void
    {
        Schema::create('market_pulse_category_breakdown', function (Blueprint $table) {
            $table->id();
            $table->date('month_date');
            $table->string('category');
            $table->decimal('category_revenue', 18, 2)->nullable();
            $table->timestamps();

            $table->unique(['month_date', 'category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('market_pulse_category_breakdown');
    }
};
