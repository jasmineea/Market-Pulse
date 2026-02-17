<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Sync BigQuery market pulse data to app DB daily so dashboard/Market Pulse load from local DB (fast)
Schedule::command('market-pulse:sync-bigquery')->daily();
