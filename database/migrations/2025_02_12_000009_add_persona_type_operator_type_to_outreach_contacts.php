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
        Schema::table('outreach_contacts', function (Blueprint $table) {
            $table->string('persona_type')->nullable()->after('linkedin_url');
            $table->string('operator_type')->nullable()->after('persona_type');
            $table->index('persona_type');
            $table->index('operator_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('outreach_contacts', function (Blueprint $table) {
            $table->dropIndex(['persona_type']);
            $table->dropIndex(['operator_type']);
        });
        Schema::table('outreach_contacts', function (Blueprint $table) {
            $table->dropColumn(['persona_type', 'operator_type']);
        });
    }
};
