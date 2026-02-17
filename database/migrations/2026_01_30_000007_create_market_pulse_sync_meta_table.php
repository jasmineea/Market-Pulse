<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Single-row table to store last BigQuery sync time for UI (data as of).
     */
    public function up(): void
    {
        Schema::create('market_pulse_sync_meta', function (Blueprint $table) {
            $table->id();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('market_pulse_sync_meta');
    }
};
