<?php

namespace App\Services;

use Google\Cloud\BigQuery\BigQueryClient;
use Illuminate\Support\Facades\Cache;

class BigQueryService
{
    /** Default cache TTL for query results (seconds). Monthly data — 30 min reduces BQ load and speeds up pages. */
    public const CACHE_TTL = 1800;

    /** TTL for dashboard-only keys (2 hours); data changes only when new month lands. */
    public const CACHE_TTL_DASHBOARD = 7200;

    protected ?BigQueryClient $client = null;

    /**
     * Don't create the client in constructor so the app can boot (e.g. login) when
     * credentials are missing (e.g. on Render without a key file). Client is created
     * lazily when runQuery() is first called.
     */
    public function __construct()
    {
    }

    /**
     * Whether BigQuery credentials are configured (for sync command / health checks).
     */
    public function isConfigured(): bool
    {
        return $this->getClient() !== null;
    }

    /**
     * Get the BigQuery client, creating it only when credentials are available.
     * Supports (1) key file path via GOOGLE_APPLICATION_CREDENTIALS or (2) inline JSON via
     * GOOGLE_APPLICATION_CREDENTIALS_JSON (e.g. on Render where you can't upload files).
     * Returns null if no valid credentials are found.
     */
    protected function getClient(): ?BigQueryClient
    {
        if ($this->client !== null) {
            return $this->client;
        }

        // Read from env first so we're not affected by config cache (e.g. on Render)
        $projectId = env('BQ_PROJECT_ID') ?: config('services.bigquery.project_id');
        $keyFile = env('GOOGLE_APPLICATION_CREDENTIALS') ?: config('services.bigquery.key_file');
        $keyJson = env('GOOGLE_APPLICATION_CREDENTIALS_JSON') ?: config('services.bigquery.key_json');

        // Prefer inline JSON (for Render): write to a temp file and use that path
        $decoded = null;
        if (! empty($keyJson) && is_string($keyJson)) {
            $decoded = json_decode($keyJson, true);
            // Must have private_key (and be valid JSON) or Google client will throw
            if (is_array($decoded) && ! empty($decoded['private_key'])) {
                $path = storage_path('app/google-credentials.json');
                if (is_dir(dirname($path)) || @mkdir(dirname($path), 0755, true)) {
                    // Write normalized JSON so escaping is correct (env var pastes can break \n in private_key)
                    file_put_contents($path, json_encode($decoded, JSON_UNESCAPED_SLASHES));
                    $keyFile = $path;
                }
                // Use project_id from JSON if BQ_PROJECT_ID env is not set (e.g. on Render)
                if (empty($projectId) && ! empty($decoded['project_id'])) {
                    $projectId = $decoded['project_id'];
                }
            }
        }

        if (empty($projectId) || ! is_string($projectId) || trim($projectId) === '') {
            return null;
        }
        if (! $keyFile || ! is_file($keyFile)) {
            return null;
        }

        $this->client = new BigQueryClient([
            'projectId' => trim($projectId),
            'keyFilePath' => $keyFile,
        ]);

        return $this->client;
    }

    public function runQuery(string $sql): array
    {
        $client = $this->getClient();

        if ($client === null) {
            return [];
        }

        $queryJob = $client->query($sql);
        $results = $client->runQuery($queryJob);

        if (! $results->isComplete()) {
            throw new \Exception('BigQuery job did not complete.');
        }

        return iterator_to_array($results->rows());
    }

    /**
     * Run a query and cache the result. Use for read-only dashboard/chart data to improve page speed.
     *
     * @param  string  $cacheKey  Unique key (e.g. 'bq.dashboard.kpi'). Include month if data is month-specific.
     * @param  int  $ttl  Cache TTL in seconds (default 10 min).
     * @return array<int, mixed>
     */
    public function runQueryCached(string $cacheKey, string $sql, int $ttl = self::CACHE_TTL): array
    {
        return Cache::remember($cacheKey, $ttl, function () use ($sql): array {
            return $this->runQuery($sql);
        });
    }

    /**
     * Run a parameterized query (e.g. WHERE month_date = @month_date). Returns [] when credentials are missing.
     *
     * @param  array<string, mixed>  $parameters
     * @return array<int, mixed>
     */
    public function runParameterizedQuery(string $sql, array $parameters = []): array
    {
        $client = $this->getClient();

        if ($client === null) {
            return [];
        }

        $queryJob = $client->query($sql)->parameters($parameters);
        $results = $client->runQuery($queryJob);

        if (! $results->isComplete()) {
            throw new \Exception('BigQuery job did not complete.');
        }

        return iterator_to_array($results->rows());
    }

    /**
     * Run a parameterized query and cache the result. Use for month-scoped data (e.g. metrics snapshot, top categories).
     *
     * @param  string  $cacheKey  Base key (e.g. 'bq.metrics.snapshot'). Month or params are appended by caller.
     * @param  int  $ttl  Cache TTL in seconds.
     * @param  array<string, mixed>  $parameters
     * @return array<int, mixed>
     */
    public function runParameterizedQueryCached(string $cacheKey, string $sql, array $parameters = [], int $ttl = self::CACHE_TTL): array
    {
        $key = $cacheKey . '.' . md5(json_encode($parameters));
        return Cache::remember($key, $ttl, function () use ($sql, $parameters): array {
            return $this->runParameterizedQuery($sql, $parameters);
        });
    }
}
