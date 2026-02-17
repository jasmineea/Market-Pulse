<?php

namespace App\Console\Commands;

use App\Services\BigQueryService;
use App\Services\CacheWarmupService;
use Illuminate\Console\Command;

/**
 * Warms the BigQuery result cache so the first request after deploy is fast.
 * Run from start.sh after migrations so every deploy (and cold start) warms cache — no need to call the /warmup URL.
 */
class CacheWarmupCommand extends Command
{
    protected $signature = 'cache:warmup';

    protected $description = 'Warm BigQuery result cache for dashboard and Market Pulse (run after deploy/cold start)';

    public function handle(CacheWarmupService $warmup, BigQueryService $bq): int
    {
        if (! $bq->isConfigured()) {
            $this->warn('BigQuery not configured; skipping cache warmup.');

            return self::SUCCESS;
        }

        try {
            $warmed = $warmup->run();
            $this->info('Warmed ' . count($warmed) . ' cache keys: ' . implode(', ', $warmed));

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Warmup failed: ' . $e->getMessage());

            return self::FAILURE;
        }
    }
}
