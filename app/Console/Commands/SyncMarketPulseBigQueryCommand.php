<?php

namespace App\Console\Commands;

use App\Services\BigQueryService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Syncs BigQuery market pulse data into the app database so dashboard and Market Pulse
 * can read from local DB (fast) instead of BQ on every request. Run on a schedule (e.g. daily).
 */
class SyncMarketPulseBigQueryCommand extends Command
{
    protected $signature = 'market-pulse:sync-bigquery';

    protected $description = 'Sync BigQuery market pulse data into the app database for fast dashboard/Market Pulse loads';

    public function handle(BigQueryService $bq): int
    {
        if (! $bq->isConfigured()) {
            $this->error('BigQuery credentials not configured. Skipping sync.');

            return self::FAILURE;
        }

        $bqRef = '`mca-dashboard-456223.terpinsights_mart.';
        $this->info('Syncing from BigQuery…');

        try {
            // 1) KPIs – all months (e.g. last 36) from joined KPIs view
            $kpiRows = $bq->runQuery("
              SELECT month_date, total_monthly_sales, total_transactions, avg_transaction_value, active_licenses
              FROM {$bqRef}market_pulse_monthly_kpis_joined`
              ORDER BY month_date DESC
              LIMIT 36
            ");
            $kpiCount = 0;
            foreach ($kpiRows as $row) {
                $row = $this->toArray($row);
                $monthDate = $this->parseDate($row['month_date'] ?? null);
                if (! $monthDate) {
                    continue;
                }
                DB::table('market_pulse_kpis')->updateOrInsert(
                    ['month_date' => $monthDate],
                    [
                        'total_monthly_sales' => $row['total_monthly_sales'] ?? null,
                        'total_transactions' => $row['total_transactions'] ?? null,
                        'avg_transaction_value' => $row['avg_transaction_value'] ?? null,
                        'active_licenses' => $row['active_licenses'] ?? null,
                        'updated_at' => now(),
                    ]
                );
                $kpiCount++;
            }
            $this->info("  KPIs: {$kpiCount} months.");

            // 2) Sales trend – all months from sales_trend
            $salesRows = $bq->runQuery("
              SELECT month_date, total_sales
              FROM {$bqRef}market_pulse_sales_trend`
              ORDER BY month_date ASC
            ");
            $salesCount = 0;
            foreach ($salesRows as $row) {
                $row = $this->toArray($row);
                $monthDate = $this->parseDate($row['month_date'] ?? null);
                if (! $monthDate) {
                    continue;
                }
                DB::table('market_pulse_sales_trend')->updateOrInsert(
                    ['month_date' => $monthDate],
                    [
                        'total_sales' => $row['total_sales'] ?? null,
                        'updated_at' => now(),
                    ]
                );
                $salesCount++;
            }
            $this->info("  Sales trend: {$salesCount} months.");

            // 3) Category breakdown – per month (months we have in sales_trend)
            $months = DB::table('market_pulse_sales_trend')->pluck('month_date')->map(fn ($d) => Carbon::parse($d)->format('Y-m-d'))->toArray();
            $categoryCount = 0;
            foreach ($months as $monthDate) {
                $catRows = $bq->runQuery("
                  SELECT category, category_revenue
                  FROM {$bqRef}market_pulse_category_breakdown_by_month`
                  WHERE month_date = DATE('" . $monthDate . "')
                  ORDER BY category_revenue DESC
                ");
                DB::table('market_pulse_category_breakdown')->where('month_date', $monthDate)->delete();
                foreach ($catRows as $row) {
                    $row = $this->toArray($row);
                    DB::table('market_pulse_category_breakdown')->insert([
                        'month_date' => $monthDate,
                        'category' => (string) ($row['category'] ?? ''),
                        'category_revenue' => $row['category_revenue'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $categoryCount++;
                }
            }
            $this->info("  Category breakdown: {$categoryCount} rows.");

            // 4) Licenses by type – snapshot (replace all)
            DB::table('market_pulse_licenses_by_type')->truncate();
            $licRows = $bq->runQuery("
              SELECT license_type, active_license_count
              FROM {$bqRef}market_pulse_active_licenses_by_type_latest`
              ORDER BY active_license_count DESC
            ");
            foreach ($licRows as $row) {
                $row = $this->toArray($row);
                DB::table('market_pulse_licenses_by_type')->insert([
                    'license_type' => (string) ($row['license_type'] ?? ''),
                    'active_license_count' => (int) ($row['active_license_count'] ?? 0),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $this->info('  Licenses by type: ' . count($licRows) . ' rows.');

            // 5) Dispensary by county – snapshot (replace all)
            DB::table('market_pulse_dispensary_by_county')->truncate();
            $dispRows = $bq->runQuery("
              SELECT county, SUM(dispensary_location_count) AS dispensary_location_count
              FROM {$bqRef}market_pulse_dispensary_locations_by_county_latest`
              GROUP BY county
              ORDER BY dispensary_location_count DESC
            ");
            foreach ($dispRows as $row) {
                $row = $this->toArray($row);
                DB::table('market_pulse_dispensary_by_county')->insert([
                    'county' => (string) ($row['county'] ?? ''),
                    'dispensary_location_count' => (int) ($row['dispensary_location_count'] ?? 0),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $this->info('  Dispensary by county: ' . count($dispRows) . ' rows.');

            // 6) Update sync meta (single row for "data as of" in UI)
            $now = now();
            DB::table('market_pulse_sync_meta')->updateOrInsert(
                ['id' => 1],
                ['id' => 1, 'last_synced_at' => $now, 'updated_at' => $now, 'created_at' => $now]
            );

            $this->info('Sync completed. Data is from BigQuery (source of truth).');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Sync failed: ' . $e->getMessage());

            return self::FAILURE;
        }
    }

    private function toArray(mixed $row): array
    {
        if (is_array($row)) {
            return $row;
        }
        $out = [];
        foreach ($row as $key => $value) {
            $out[$key] = $value;
        }

        return $out;
    }

    private function parseDate(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }
}
