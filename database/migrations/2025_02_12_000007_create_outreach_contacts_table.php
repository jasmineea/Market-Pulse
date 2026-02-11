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
        Schema::create('outreach_contacts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('linkedin_url');
            $table->string('role')->nullable();
            $table->string('organization')->nullable();
            $table->string('location')->nullable();
            $table->text('why_selected')->nullable();
            $table->string('priority')->nullable();
            $table->string('source')->nullable();
            $table->string('status')->default('Not Contacted');
            $table->date('date_contacted')->nullable();
            $table->text('response_summary')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::table('outreach_contacts', function (Blueprint $table) {
            $table->index('status');
            $table->index('follow_up_date');
            $table->index('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outreach_contacts');
    }
};
