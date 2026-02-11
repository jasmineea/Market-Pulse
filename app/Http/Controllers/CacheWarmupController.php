<?php

namespace App\Http\Controllers;

use App\Services\BigQueryService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Warms the BigQuery result cache so the first page loads after deploy are fast.
 * Call from Render deploy hook or a cron: GET /warmup?secret=YOUR_CACHE_WARMUP_SECRET
 */
class CacheWarmupController extends Controller
{
    public function __invoke(Request $request, BigQueryService $bq): Response
    {
        $secret = env('CACHE_WARMUP_SECRET');
        if (empty($secret) || $request->query('secret') !== $secret) {
            abort(404);
        }

        $warmed = [];

        try {
            // Dashboard: KPI (also gives latest month for category_mix)
            $kpiRows = $bq->runQueryCached('bq.dashboard.kpi', "
              SELECT month_date, total_monthly_sales, total_transactions, avg_transaction_value, active_licenses
              FROM `mca-dashboard-456223.terpinsights_mart.market_pulse_monthly_kpis_joined`
              ORDER BY month_date DESC
              LIMIT 2
            ");
            $warmed[] = 'bq.dashboard.kpi';

            $latestMonth = isset($kpiRows[0]['month_date']) ? Carbon::parse($kpiRows[0]['month_date'])->format('Y-m-d') : null;

            $bq->runQueryCached('bq.dashboard.sales_trend', "
              SELECT month_date, total_sales
              FROM `mca-dashboard-456223.terpinsights_mart.market_pulse_sales_trend`
              ORDER BY month_date DESC
              LIMIT 12
            ");
            $warmed[] = 'bq.dashboard.sales_trend';

            $bq->runQueryCached('bq.dashboard.license_growth', "
              SELECT month_date, active_licenses
              FROM `mca-dashboard-456223.terpinsights_mart.market_pulse_monthly_kpis_joined`
              ORDER BY month_date DESC
              LIMIT 3
            ");
            $warmed[] = 'bq.dashboard.license_growth';

            if ($latestMonth) {
                $dateFilter = " WHERE month_date = DATE('" . $latestMonth . "')";
                $bq->runQueryCached('bq.dashboard.category_mix.' . $latestMonth, "
                  SELECT category, category_sales, share_pct
                  FROM `mca-dashboard-456223.terpinsights_mart.market_pulse_category_breakdown_latest`
                  $dateFilter
                  ORDER BY category_sales DESC
                ");
                $warmed[] = 'bq.dashboard.category_mix.' . $latestMonth;
            }

            $bq->runQueryCached('bq.dashboard.history_months', "
              SELECT DISTINCT month_date
              FROM `mca-dashboard-456223.terpinsights_mart.market_pulse_sales_trend`
              ORDER BY month_date DESC
              LIMIT 12
            ");
            $warmed[] = 'bq.dashboard.history_months';

            // Market Pulse: latest month + KPI for that month
            $latestRow = $bq->runQueryCached('bq.market_pulse.latest_month', "
              SELECT month_date
              FROM `mca-dashboard-456223.terpinsights_mart.market_pulse_monthly_kpis_joined`
              ORDER BY month_date DESC
              LIMIT 1
            ");
            $warmed[] = 'bq.market_pulse.latest_month';

            if (! empty($latestRow) && isset($latestRow[0]['month_date'])) {
                $displayMonthDate = Carbon::parse($latestRow[0]['month_date'])->startOfMonth()->format('Y-m-d');
                $bq->runQueryCached('bq.market_pulse.kpi.' . $displayMonthDate, "
                  SELECT month_date, total_monthly_sales, total_transactions, avg_transaction_value, active_licenses
                  FROM `mca-dashboard-456223.terpinsights_mart.market_pulse_monthly_kpis_joined`
                  WHERE month_date <= DATE('$displayMonthDate')
                  ORDER BY month_date DESC
                  LIMIT 2
                ");
                $warmed[] = 'bq.market_pulse.kpi.' . $displayMonthDate;
            }

            $bq->runQueryCached('bq.export.available_months', "
              SELECT DISTINCT month_date
              FROM `mca-dashboard-456223.terpinsights_mart.market_pulse_sales_trend`
              ORDER BY month_date DESC
            ");
            $warmed[] = 'bq.export.available_months';
        } catch (\Throwable $e) {
            return response('Warmup partial: ' . implode(', ', $warmed) . '; error: ' . $e->getMessage(), 200, ['Content-Type' => 'text/plain']);
        }

        return response('OK warmed: ' . implode(', ', $warmed), 200, ['Content-Type' => 'text/plain']);
    }
}
