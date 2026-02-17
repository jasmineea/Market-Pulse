<?php

namespace App\Services;

use Carbon\Carbon;

/**
 * Warms the BigQuery result cache so dashboard and Market Pulse first loads are fast.
 * Used by the /warmup HTTP endpoint and by the cache:warmup Artisan command (e.g. in start.sh after deploy).
 */
class CacheWarmupService
{
    public function __construct(private BigQueryService $bq) {}

    /**
     * Run all warmup queries. Returns list of cache keys warmed, or throws.
     *
     * @return array<int, string>
     */
    public function run(): array
    {
        $warmed = [];

        // Dashboard: KPI (also gives latest month for category_mix)
        $kpiRows = $this->bq->runQueryCached('bq.dashboard.kpi', "
          SELECT month_date, total_monthly_sales, total_transactions, avg_transaction_value, active_licenses
          FROM `mca-dashboard-456223.terpinsights_mart.market_pulse_monthly_kpis_joined`
          ORDER BY month_date DESC
          LIMIT 2
        ", BigQueryService::CACHE_TTL_DASHBOARD);
        $warmed[] = 'bq.dashboard.kpi';

        $latestMonth = isset($kpiRows[0]['month_date']) ? Carbon::parse($kpiRows[0]['month_date'])->format('Y-m-d') : null;

        $this->bq->runQueryCached('bq.dashboard.sales_trend', "
          SELECT month_date, total_sales
          FROM `mca-dashboard-456223.terpinsights_mart.market_pulse_sales_trend`
          ORDER BY month_date DESC
          LIMIT 12
        ", BigQueryService::CACHE_TTL_DASHBOARD);
        $warmed[] = 'bq.dashboard.sales_trend';

        $this->bq->runQueryCached('bq.dashboard.license_growth', "
          SELECT month_date, active_licenses
          FROM `mca-dashboard-456223.terpinsights_mart.market_pulse_monthly_kpis_joined`
          ORDER BY month_date DESC
          LIMIT 3
        ", BigQueryService::CACHE_TTL_DASHBOARD);
        $warmed[] = 'bq.dashboard.license_growth';

        if ($latestMonth) {
            $dateFilter = " WHERE month_date = DATE('" . $latestMonth . "')";
            $this->bq->runQueryCached('bq.dashboard.category_mix.' . $latestMonth, "
              SELECT category, category_sales, share_pct
              FROM `mca-dashboard-456223.terpinsights_mart.market_pulse_category_breakdown_latest`
              $dateFilter
              ORDER BY category_sales DESC
            ", BigQueryService::CACHE_TTL_DASHBOARD);
            $warmed[] = 'bq.dashboard.category_mix.' . $latestMonth;
        }

        $this->bq->runQueryCached('bq.dashboard.history_months', "
          SELECT DISTINCT month_date
          FROM `mca-dashboard-456223.terpinsights_mart.market_pulse_sales_trend`
          ORDER BY month_date DESC
          LIMIT 12
        ", BigQueryService::CACHE_TTL_DASHBOARD);
        $warmed[] = 'bq.dashboard.history_months';

        // Market Pulse: latest month + KPI for that month
        $latestRow = $this->bq->runQueryCached('bq.market_pulse.latest_month', "
          SELECT month_date
          FROM `mca-dashboard-456223.terpinsights_mart.market_pulse_monthly_kpis_joined`
          ORDER BY month_date DESC
          LIMIT 1
        ");
        $warmed[] = 'bq.market_pulse.latest_month';

        $displayMonthDate = null;
        if (! empty($latestRow) && isset($latestRow[0]['month_date'])) {
            $displayMonthDate = Carbon::parse($latestRow[0]['month_date'])->startOfMonth()->format('Y-m-d');
            $this->bq->runQueryCached('bq.market_pulse.kpi.' . $displayMonthDate, "
              SELECT month_date, total_monthly_sales, total_transactions, avg_transaction_value, active_licenses
              FROM `mca-dashboard-456223.terpinsights_mart.market_pulse_monthly_kpis_joined`
              WHERE month_date <= DATE('$displayMonthDate')
              ORDER BY month_date DESC
              LIMIT 2
            ");
            $warmed[] = 'bq.market_pulse.kpi.' . $displayMonthDate;
        }

        $this->bq->runQueryCached('bq.market_pulse.licenses_by_type', "
          SELECT license_type, active_license_count
          FROM `mca-dashboard-456223.terpinsights_mart.market_pulse_active_licenses_by_type_latest`
          ORDER BY active_license_count DESC
        ");
        $warmed[] = 'bq.market_pulse.licenses_by_type';

        $this->bq->runQueryCached('bq.market_pulse.dispensary_county', "
          SELECT county, SUM(dispensary_location_count) AS dispensary_location_count
          FROM `mca-dashboard-456223.terpinsights_mart.market_pulse_dispensary_locations_by_county_latest`
          GROUP BY county
          ORDER BY dispensary_location_count DESC
        ");
        $warmed[] = 'bq.market_pulse.dispensary_county';

        $this->bq->runQueryCached('bq.market_pulse.available_months', "
          SELECT DISTINCT month_date
          FROM `mca-dashboard-456223.terpinsights_mart.market_pulse_sales_trend`
          ORDER BY month_date DESC
        ");
        $warmed[] = 'bq.market_pulse.available_months';

        if ($displayMonthDate !== null) {
            $displayYear = Carbon::parse($displayMonthDate)->year;
            $yearStart = $displayYear . '-01-01';
            $yearEnd = $displayYear . '-12-01';
            $this->bq->runQueryCached('bq.market_pulse.sales_trend.' . $displayYear, "
              SELECT month_date, total_sales
              FROM `mca-dashboard-456223.terpinsights_mart.market_pulse_sales_trend`
              WHERE month_date >= DATE('$yearStart') AND month_date <= DATE('$yearEnd')
              ORDER BY month_date ASC
            ");
            $warmed[] = 'bq.market_pulse.sales_trend.' . $displayYear;

            $this->bq->runQueryCached('bq.market_pulse.category_breakdown.' . $displayMonthDate, "
              SELECT category, category_revenue
              FROM `mca-dashboard-456223.terpinsights_mart.market_pulse_category_breakdown_by_month`
              WHERE month_date = DATE('$displayMonthDate')
              ORDER BY category_revenue DESC
            ");
            $warmed[] = 'bq.market_pulse.category_breakdown.' . $displayMonthDate;
        }

        $this->bq->runQueryCached('bq.export.available_months', "
          SELECT DISTINCT month_date
          FROM `mca-dashboard-456223.terpinsights_mart.market_pulse_sales_trend`
          ORDER BY month_date DESC
        ");
        $warmed[] = 'bq.export.available_months';

        return $warmed;
    }
}
