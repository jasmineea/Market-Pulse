<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Snapshot from BQ market_pulse_active_licenses_by_type_latest (replaced each sync).
     */
    public function up(): void
    {
        Schema::create('market_pulse_licenses_by_type', function (Blueprint $table) {
            $table->id();
            $table->string('license_type');
            $table->unsignedInteger('active_license_count')->default(0);
            $table->timestamps();

            $table->unique('license_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('market_pulse_licenses_by_type');
    }
};
