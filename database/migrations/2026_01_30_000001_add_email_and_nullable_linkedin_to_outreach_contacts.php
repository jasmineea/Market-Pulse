<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds email column for AI Lab collaboration requests; makes linkedin_url
     * nullable to support inbound contacts without LinkedIn.
     */
    public function up(): void
    {
        Schema::table('outreach_contacts', function (Blueprint $table) {
            $table->string('email', 255)->nullable()->after('name');
            $table->index('email');
        });

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE outreach_contacts MODIFY linkedin_url VARCHAR(500) NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE outreach_contacts ALTER COLUMN linkedin_url DROP NOT NULL');
        } elseif ($driver === 'sqlite') {
            // SQLite does not support ALTER COLUMN; linkedin_url remains NOT NULL.
            // Install doctrine/dbal for schema changes, or use MySQL/Postgres in production.
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('outreach_contacts', function (Blueprint $table) {
            $table->dropIndex(['email']);
            $table->dropColumn('email');
        });

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE outreach_contacts MODIFY linkedin_url VARCHAR(500) NOT NULL');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE outreach_contacts ALTER COLUMN linkedin_url SET NOT NULL');
        }
    }
};
