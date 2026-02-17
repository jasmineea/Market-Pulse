<?php

namespace App\Http\Controllers;

use App\Services\CacheWarmupService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Warms the BigQuery result cache so the first page loads after deploy are fast.
 * Call from cron or browser: GET /warmup?secret=YOUR_CACHE_WARMUP_SECRET
 * Or run in start script: php artisan cache:warmup (no secret needed).
 */
class CacheWarmupController extends Controller
{
    public function __invoke(Request $request, CacheWarmupService $warmup): Response
    {
        $secret = env('CACHE_WARMUP_SECRET');
        if (empty($secret) || $request->query('secret') !== $secret) {
            abort(404);
        }

        try {
            $warmed = $warmup->run();
            return response('OK warmed: ' . implode(', ', $warmed), 200, ['Content-Type' => 'text/plain']);
        } catch (\Throwable $e) {
            return response('Warmup failed: ' . $e->getMessage(), 200, ['Content-Type' => 'text/plain']);
        }
    }
}
