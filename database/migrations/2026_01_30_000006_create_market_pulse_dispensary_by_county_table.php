<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Snapshot from BQ market_pulse_dispensary_locations_by_county_latest (replaced each sync).
     */
    public function up(): void
    {
        Schema::create('market_pulse_dispensary_by_county', function (Blueprint $table) {
            $table->id();
            $table->string('county');
            $table->unsignedInteger('dispensary_location_count')->default(0);
            $table->timestamps();

            $table->unique('county');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('market_pulse_dispensary_by_county');
    }
};
