<?php

namespace App\Services\MarketPulse;

use App\Models\MarketPulseSnapshot;
use App\Services\BigQueryService;
use Carbon\Carbon;

/**
 * Builds Market Pulse report data for a given month (shared by web view and PDF export).
 */
class MarketPulseReportData
{
    public function __construct(
        private BigQueryService $bq,
        private MarketPulseMetrics $metrics,
        private ExecutiveSummaryBuilder $builder
    ) {}

    /**
     * Return report data array for the given month (YYYY-MM). Keys: displayMonthDate, kpi, mom, executive_summary, executive_summary_is_html, salesTrend, licensesByType, categoryBreakdown, dispensaryByCounty.
     */
    public function forMonth(string $monthParam): array
    {
        $displayMonthDate = preg_match('/^\d{4}-\d{2}$/', $monthParam)
            ? Carbon::parse($monthParam . '-01')->startOfMonth()->format('Y-m-d')
            : null;

        $kpi = null;
        $currentRow = null;
        $previousRow = null;
        $kpiColumns = ['month_date', 'total_monthly_sales', 'total_transactions', 'avg_transaction_value', 'active_licenses'];

        if ($displayMonthDate !== null) {
            $kpiRows = $this->bq->runQuery("
              SELECT month_date, total_monthly_sales, total_transactions, avg_transaction_value, active_licenses
              FROM `mca-dashboard-456223.terpinsights_mart.market_pulse_monthly_kpis_joined`
              WHERE month_date <= DATE('$displayMonthDate')
              ORDER BY month_date DESC
              LIMIT 2
            ");
            $currentRow = isset($kpiRows[0]) ? $this->rowToArray($kpiRows[0], $kpiColumns) : null;
            $previousRow = isset($kpiRows[1]) ? $this->rowToArray($kpiRows[1], $kpiColumns) : null;
        }

        if ($currentRow !== null) {
            $kpi = [
                'month_date' => $currentRow['month_date'] ?? null,
                'total_monthly_sales' => isset($currentRow['total_monthly_sales']) && $currentRow['total_monthly_sales'] !== null ? (float) $currentRow['total_monthly_sales'] : null,
                'total_transactions' => isset($currentRow['total_transactions']) && $currentRow['total_transactions'] !== null ? (int) $currentRow['total_transactions'] : null,
                'avg_transaction_value' => isset($currentRow['avg_transaction_value']) && $currentRow['avg_transaction_value'] !== null ? (float) $currentRow['avg_transaction_value'] : null,
                'active_licenses' => isset($currentRow['active_licenses']) && $currentRow['active_licenses'] !== null ? (int) $currentRow['active_licenses'] : null,
            ];
        }

        $mom = ['total_monthly_sales' => null, 'total_transactions' => null, 'avg_transaction_value' => null, 'active_licenses' => null];
        if ($currentRow && $previousRow) {
            $pct = fn($c, $p) => ($p === null || (is_numeric($p) && (float) $p == 0)) ? null : (is_numeric($c) && is_numeric($p) ? (($c - $p) / $p) * 100 : null);
            $mom['total_monthly_sales'] = $pct($currentRow['total_monthly_sales'] ?? null, $previousRow['total_monthly_sales'] ?? null);
            $mom['total_transactions'] = $pct($currentRow['total_transactions'] ?? null, $previousRow['total_transactions'] ?? null);
            $mom['avg_transaction_value'] = $pct($currentRow['avg_transaction_value'] ?? null, $previousRow['avg_transaction_value'] ?? null);
            $mom['active_licenses'] = $pct($currentRow['active_licenses'] ?? null, $previousRow['active_licenses'] ?? null);
        }

        $executive_summary = null;
        $executive_summary_is_html = false;
        if ($displayMonthDate !== null) {
            $displayCarbon = Carbon::parse($displayMonthDate);
            $snapshot = MarketPulseSnapshot::query()
                ->whereYear('month_date', $displayCarbon->year)
                ->whereMonth('month_date', $displayCarbon->month)
                ->first();
            if ($snapshot && $snapshot->published_at !== null && trim((string) ($snapshot->executive_summary ?? '')) !== '') {
                $executive_summary = $snapshot->executive_summary;
            } else {
                try {
                    $m = $this->metrics->forMonth($displayMonthDate);
                    $built = $this->builder->build($m);
                    $executive_summary = $built['summary'] ?? null;
                    $executive_summary_is_html = true;
                } catch (\Throwable $e) {
                    $executive_summary = null;
                }
            }
        }

        $latestMonthDate = $displayMonthDate;
        $salesTrendRows = [];
        if ($latestMonthDate !== null) {
            $displayYear = Carbon::parse($latestMonthDate)->year;
            $yearStart = $displayYear . '-01-01';
            $yearEnd = $displayYear . '-12-01';
            $salesTrendRows = $this->bq->runQuery("
              SELECT month_date, total_sales FROM `mca-dashboard-456223.terpinsights_mart.market_pulse_sales_trend`
              WHERE month_date >= DATE('$yearStart') AND month_date <= DATE('$yearEnd')
              ORDER BY month_date ASC
            ");
        } else {
            $salesTrendRows = $this->bq->runQuery("
              SELECT month_date, total_sales FROM `mca-dashboard-456223.terpinsights_mart.market_pulse_sales_trend`
              ORDER BY month_date ASC
            ");
        }
        $salesTrend = [
            'labels' => array_map(fn($r) => isset($r['month_date']) && $r['month_date'] ? Carbon::parse($r['month_date'])->format('M Y') : '', $salesTrendRows),
            'values' => array_map(fn($r) => isset($r['total_sales']) && $r['total_sales'] !== null ? (float) $r['total_sales'] : 0.0, $salesTrendRows),
        ];

        $licensesByTypeRows = [];
        try {
            $licensesByTypeRows = $this->bq->runQuery("
              SELECT license_type, active_license_count
              FROM `mca-dashboard-456223.terpinsights_mart.market_pulse_active_licenses_by_type_latest`
              ORDER BY active_license_count DESC
            ");
        } catch (\Throwable $e) {
            // leave empty
        }
        $licensesByType = [
            'labels' => array_map(fn($r) => (string) ($r['license_type'] ?? $r['business_type'] ?? ''), $licensesByTypeRows),
            'values' => array_map(fn($r) => isset($r['active_license_count']) && $r['active_license_count'] !== null ? (int) $r['active_license_count'] : 0, $licensesByTypeRows),
        ];

        $categoryRows = [];
        if ($latestMonthDate) {
            try {
                $categoryRows = $this->bq->runQuery("
                  SELECT category, category_revenue
                  FROM `mca-dashboard-456223.terpinsights_mart.market_pulse_category_breakdown_by_month`
                  WHERE month_date = DATE('$latestMonthDate')
                  ORDER BY category_revenue DESC
                ");
            } catch (\Throwable $e) {
                // leave empty
            }
        }
        $categoryValues = array_map(fn($r) => isset($r['category_revenue']) && $r['category_revenue'] !== null ? (float) $r['category_revenue'] : 0.0, $categoryRows);
        $totalRevenue = array_sum($categoryValues);
        $sharePct = $totalRevenue > 0 ? array_map(fn($v) => (float) (($v / $totalRevenue) * 100), $categoryValues) : array_fill(0, count($categoryValues), null);
        $categoryBreakdown = [
            'labels' => array_map(fn($r) => (string) ($r['category'] ?? ''), $categoryRows),
            'values' => $categoryValues,
            'share_pct' => $sharePct,
        ];

        $dispensaryColumns = ['county', 'dispensary_location_count'];
        $dispensaryRows = [];
        try {
            $rawDispensary = $this->bq->runQuery("
              SELECT county, SUM(dispensary_location_count) AS dispensary_location_count
              FROM `mca-dashboard-456223.terpinsights_mart.market_pulse_dispensary_locations_by_county_latest`
              GROUP BY county
              ORDER BY dispensary_location_count DESC
            ");
            foreach ($rawDispensary as $row) {
                $dispensaryRows[] = $this->rowToArray($row, $dispensaryColumns);
            }
        } catch (\Throwable $e) {
            // leave empty
        }
        $dispensaryByCounty = empty($dispensaryRows)
            ? ['labels' => ['Unknown'], 'values' => [0]]
            : [
                'labels' => array_map(fn($r) => $this->cleanCountyName((string) ($r['county'] ?? '')), $dispensaryRows),
                'values' => array_map(fn($r) => isset($r['dispensary_location_count']) && $r['dispensary_location_count'] !== null ? (int) $r['dispensary_location_count'] : 0, $dispensaryRows),
            ];

        return [
            'displayMonthDate' => $displayMonthDate,
            'kpi' => $kpi,
            'mom' => $mom,
            'executive_summary' => $executive_summary,
            'executive_summary_is_html' => $executive_summary_is_html,
            'salesTrend' => $salesTrend,
            'licensesByType' => $licensesByType,
            'categoryBreakdown' => $categoryBreakdown,
            'dispensaryByCounty' => $dispensaryByCounty,
        ];
    }

    private function rowToArray($row, array $columns): array
    {
        if (is_array($row)) {
            return $row;
        }
        $out = [];
        foreach ($row as $key => $value) {
            $name = is_int($key) && isset($columns[$key]) ? $columns[$key] : $key;
            if (is_string($name)) {
                $out[$name] = $value;
            }
        }
        if (empty($out) && !empty($columns)) {
            foreach ($columns as $idx => $col) {
                $out[$col] = $row[$idx] ?? null;
            }
        }
        return $out;
    }

    private function cleanCountyName(string $name): string
    {
        $name = trim($name);
        if ($name === '') {
            return $name;
        }
        $name = preg_replace_callback("/'(.)/u", fn($m) => "'" . mb_strtolower($m[1]), $name);
        return mb_convert_case(mb_strtolower($name), MB_CASE_TITLE, 'UTF-8');
    }
}
