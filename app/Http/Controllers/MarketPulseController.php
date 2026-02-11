<?php

namespace App\Http\Controllers;

use App\Models\MarketPulseSnapshot;
use App\Services\BigQueryService;
use App\Services\PlanGate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use App\Services\MarketPulse\MarketPulseMetrics;
use App\Services\MarketPulse\ExecutiveSummaryBuilder;

class MarketPulseController extends Controller
{
    public function index(Request $request, BigQueryService $bq, MarketPulseMetrics $metrics, ExecutiveSummaryBuilder $builder, PlanGate $planGate)
    {
        // Resolve display month first: ?month=YYYY-MM or latest available in data
        $displayMonthDate = null;
        $monthParam = $request->query('month');
        if ($monthParam && preg_match('/^\d{4}-\d{2}$/', $monthParam)) {
            $displayMonthDate = Carbon::parse($monthParam . '-01')->startOfMonth()->format('Y-m-d');
        }
        if ($displayMonthDate === null) {
            try {
                $latestRow = $bq->runQueryCached('bq.market_pulse.latest_month', "
                  SELECT month_date
                  FROM `mca-dashboard-456223.terpinsights_mart.market_pulse_monthly_kpis_joined`
                  ORDER BY month_date DESC
                  LIMIT 1
                ");
                if (!empty($latestRow) && isset($latestRow[0]['month_date'])) {
                    $displayMonthDate = Carbon::parse($latestRow[0]['month_date'])->startOfMonth()->format('Y-m-d');
                }
            } catch (\Throwable $e) {
                // leave null
            }
        }
        if ($displayMonthDate !== null) {
            $displayMonthDate = Carbon::parse($displayMonthDate)->startOfMonth()->format('Y-m-d');
        }

        /**
         * KPI SCORECARDS for the display month + previous month (for MoM)
         * View columns: month_date, total_monthly_sales, total_transactions, avg_transaction_value, active_licenses
         */
        $currentRow = null;
        $previousRow = null;
        if ($displayMonthDate !== null) {
            $kpiSql = "
              SELECT
                month_date,
                total_monthly_sales,
                total_transactions,
                avg_transaction_value,
                active_licenses
              FROM `mca-dashboard-456223.terpinsights_mart.market_pulse_monthly_kpis_joined`
              WHERE month_date <= DATE('$displayMonthDate')
              ORDER BY month_date DESC
              LIMIT 2
            ";
            $kpiRows = $bq->runQueryCached('bq.market_pulse.kpi.' . $displayMonthDate, $kpiSql);
            $kpiColumns = ['month_date', 'total_monthly_sales', 'total_transactions', 'avg_transaction_value', 'active_licenses'];
            $currentRow  = isset($kpiRows[0]) ? $this->rowToArray($kpiRows[0], $kpiColumns) : null;
            $previousRow = isset($kpiRows[1]) ? $this->rowToArray($kpiRows[1], $kpiColumns) : null;
        }

        $kpi = null;
        if ($currentRow !== null) {
            $kpi = [
                'month_date' => $currentRow['month_date'] ?? null,
                'total_monthly_sales' => isset($currentRow['total_monthly_sales']) && $currentRow['total_monthly_sales'] !== null
                    ? (float) $currentRow['total_monthly_sales']
                    : null,
                'total_transactions' => isset($currentRow['total_transactions']) && $currentRow['total_transactions'] !== null
                    ? (int) $currentRow['total_transactions']
                    : null,
                'avg_transaction_value' => isset($currentRow['avg_transaction_value']) && $currentRow['avg_transaction_value'] !== null
                    ? (float) $currentRow['avg_transaction_value']
                    : null,
                'active_licenses' => isset($currentRow['active_licenses']) && $currentRow['active_licenses'] !== null
                    ? (int) $currentRow['active_licenses']
                    : null,
            ];
        }

        // Month-over-month % change
        $mom = [
            'total_monthly_sales' => null,
            'total_transactions' => null,
            'avg_transaction_value' => null,
            'active_licenses' => null,
        ];

        if ($currentRow && $previousRow) {
            $pct = function ($curr, $prev) {
                if ($prev === null || (is_numeric($prev) && (float) $prev == 0)) {
                    return null;
                }
                $c = is_numeric($curr) ? (float) $curr : null;
                $p = is_numeric($prev) ? (float) $prev : null;
                return $c !== null && $p !== null ? (($c - $p) / $p) * 100 : null;
            };

            $mom['total_monthly_sales']   = $pct($currentRow['total_monthly_sales'] ?? null, $previousRow['total_monthly_sales'] ?? null);
            $mom['total_transactions']    = $pct($currentRow['total_transactions'] ?? null, $previousRow['total_transactions'] ?? null);
            $mom['avg_transaction_value'] = $pct($currentRow['avg_transaction_value'] ?? null, $previousRow['avg_transaction_value'] ?? null);
            $mom['active_licenses']       = $pct($currentRow['active_licenses'] ?? null, $previousRow['active_licenses'] ?? null);
        }

        /**
         * DETAILED ANALYTICS
         * Charts and KPIs are aligned to the selected display month.
         */
        $latestMonthDate = $displayMonthDate; // used for chart filters; same as display month

        /**
         * Executive summary: use saved snapshot for the displayed month if one exists and is published with content.
         * Otherwise default to the dynamic summary from ExecutiveSummaryBuilder (metrics-based).
         */
        $executive_summary = null;
        $executive_summary_is_html = false;
        if ($displayMonthDate !== null) {
            $displayCarbon = Carbon::parse($displayMonthDate);
            $snapshot = MarketPulseSnapshot::query()
                ->whereYear('month_date', $displayCarbon->year)
                ->whereMonth('month_date', $displayCarbon->month)
                ->first();
            if ($snapshot && $snapshot->published_at !== null && trim((string) $snapshot->executive_summary) !== '') {
                $executive_summary = $snapshot->executive_summary;
            } else {
                // No saved snapshot (or not published / empty): use dynamic summary from ExecutiveSummaryBuilder (HTML formatted)
                try {
                    $m = $metrics->forMonth($displayMonthDate);
                    $built = $builder->build($m);
                    $executive_summary = $built['summary'] ?? null;
                    $executive_summary_is_html = true;
                } catch (\Throwable $e) {
                    $executive_summary = null;
                }
            }
        }

        // 1) Monthly Sales Trend (line chart) - months for the filtered year only
        // Schema: month_date, total_sales
        $salesTrendRows = [];
        if ($latestMonthDate !== null) {
            $displayYear = Carbon::parse($latestMonthDate)->year;
            $yearStart = $displayYear . '-01-01';
            $yearEnd = $displayYear . '-12-01';
            $salesTrendRows = $bq->runQueryCached('bq.market_pulse.sales_trend.' . $displayYear, "
              SELECT month_date, total_sales
              FROM `mca-dashboard-456223.terpinsights_mart.market_pulse_sales_trend`
              WHERE month_date >= DATE('$yearStart') AND month_date <= DATE('$yearEnd')
              ORDER BY month_date ASC
            ");
        } else {
            $salesTrendRows = $bq->runQueryCached('bq.market_pulse.sales_trend.all', "
              SELECT month_date, total_sales
              FROM `mca-dashboard-456223.terpinsights_mart.market_pulse_sales_trend`
              ORDER BY month_date ASC
            ");
        }

        $salesTrend = [
            'labels' => array_map(
                fn($r) => isset($r['month_date']) && $r['month_date'] ? Carbon::parse($r['month_date'])->format('M Y') : '',
                $salesTrendRows
            ),
            'values' => array_map(
                fn($r) => isset($r['total_sales']) && $r['total_sales'] !== null ? (float) $r['total_sales'] : 0.0,
                $salesTrendRows
            ),
        ];

        // 2) Active Licenses by Type (bar chart) - show active licenses regardless of month (latest snapshot only)
        $licensesByTypeRows = [];
        try {
            $licensesByTypeRows = $bq->runQueryCached('bq.market_pulse.licenses_by_type', "
              SELECT license_type, active_license_count
              FROM `mca-dashboard-456223.terpinsights_mart.market_pulse_active_licenses_by_type_latest`
              ORDER BY active_license_count DESC
            ");
        } catch (\Throwable $e) {
            $licensesByTypeRows = [];
        }

        $licensesByType = [
            'labels' => array_map(fn($r) => (string) ($r['license_type'] ?? $r['business_type'] ?? ''), $licensesByTypeRows),
            'values' => array_map(fn($r) => isset($r['active_license_count']) && $r['active_license_count'] !== null ? (int) $r['active_license_count'] : 0, $licensesByTypeRows),
        ];

        // 3) Category Revenue Breakdown (doughnut) - from market_pulse_category_breakdown_by_month
        // Schema: month_date, category, category_revenue (no share_pct; we compute it)
        $categoryRows = [];
        if ($latestMonthDate) {
            try {
                $categoryRows = $bq->runQueryCached('bq.market_pulse.category_breakdown.' . $latestMonthDate, "
                  SELECT category, category_revenue
                  FROM `mca-dashboard-456223.terpinsights_mart.market_pulse_category_breakdown_by_month`
                  WHERE month_date = DATE('$latestMonthDate')
                  ORDER BY category_revenue DESC
                ");
            } catch (\Throwable $e) {
                $categoryRows = [];
            }
        }

        $categoryValues = array_map(fn($r) => isset($r['category_revenue']) && $r['category_revenue'] !== null ? (float) $r['category_revenue'] : 0.0, $categoryRows);
        $totalRevenue = array_sum($categoryValues);
        $sharePct = $totalRevenue > 0
            ? array_map(fn($v) => (float) (($v / $totalRevenue) * 100), $categoryValues)
            : array_fill(0, count($categoryValues), null);

        $categoryBreakdown = [
            'labels' => array_map(fn($r) => (string) ($r['category'] ?? ''), $categoryRows),
            'values' => $categoryValues,
            'share_pct' => $sharePct,
        ];

        // 4) Dispensary Distribution by County (horizontal bar) - all available locations, no month filter
        // Schema: county (STRING), dispensary_location_count (INTEGER)
        $dispensaryColumns = ['county', 'dispensary_location_count'];
        $dispensaryRows = [];
        try {
            $rawDispensary = $bq->runQueryCached('bq.market_pulse.dispensary_county', "
              SELECT county, SUM(dispensary_location_count) AS dispensary_location_count
              FROM `mca-dashboard-456223.terpinsights_mart.market_pulse_dispensary_locations_by_county_latest`
              GROUP BY county
              ORDER BY dispensary_location_count DESC
            ");
            foreach ($rawDispensary as $row) {
                $dispensaryRows[] = $this->rowToArray($row, $dispensaryColumns);
            }
        } catch (\Throwable $e) {
            $dispensaryRows = [];
        }
        if (empty($dispensaryRows)) {
            $dispensaryByCounty = [
                'labels' => ['Unknown'],
                'values' => [0],
            ];
        } else {
            $dispensaryByCounty = [
                'labels' => array_map(fn($r) => $this->cleanCountyName((string) ($r['county'] ?? '')), $dispensaryRows),
                'values' => array_map(fn($r) => isset($r['dispensary_location_count']) && $r['dispensary_location_count'] !== null ? (int) $r['dispensary_location_count'] : 0, $dispensaryRows),
            ];
        }

        $printMode = $request->boolean('print');
        View::share('printMode', $printMode);

        // Available months for snapshot dropdown: all months in the database (BigQuery), newest first
        $availableMonths = [];
        try {
            $rows = $bq->runQueryCached('bq.market_pulse.available_months', "
              SELECT DISTINCT month_date
              FROM `mca-dashboard-456223.terpinsights_mart.market_pulse_sales_trend`
              ORDER BY month_date DESC
            ");
            foreach ($rows as $r) {
                $d = $r['month_date'] ?? null;
                if ($d) {
                    $ym = Carbon::parse($d)->format('Y-m');
                    $availableMonths[$ym] = Carbon::parse($d)->format('F Y');
                }
            }
        } catch (\Throwable $e) {
            // Fallback: published snapshots if BigQuery fails
            $published = MarketPulseSnapshot::query()
                ->whereNotNull('published_at')
                ->orderBy('month_date', 'desc')
                ->get();
            foreach ($published as $s) {
                $ym = $s->month_date->format('Y-m');
                $availableMonths[$ym] = $s->month_date->format('F Y');
            }
        }
        if ($displayMonthDate !== null) {
            $currentYm = Carbon::parse($displayMonthDate)->format('Y-m');
            if (!isset($availableMonths[$currentYm])) {
                $availableMonths = [$currentYm => Carbon::parse($displayMonthDate)->format('F Y')] + $availableMonths;
            }
        }
        $currentMonthValue = $displayMonthDate ? Carbon::parse($displayMonthDate)->format('Y-m') : array_key_first($availableMonths);

        // Plan-based gating: Starter can only access first N months that have data (newest first)
        $user = Auth::user();
        if (! $planGate->isMonthAllowed($currentMonthValue, $user, $availableMonths)) {
            $redirectMonth = $planGate->getLatestAllowedMonth($user, $availableMonths);
            return redirect()->route('market-pulse', $redirectMonth ? ['month' => $redirectMonth] : []);
        }

        $partitionedMonths = $planGate->partitionMonths($availableMonths, $user);
        $canViewCountyChart = $planGate->canViewCountyChart($user);

        // Starter: only show allowed months in dropdown. Pro: show all months (locked ones marked).
        $dropdownMonths = $planGate->canAccessCountyData($user)
            ? $availableMonths
            : $partitionedMonths['allowed'];

        // Executive summary
        $monthDate = $request->get('month_date', now()->startOfMonth()->toDateString());
        $data = $metrics->forMonth($monthDate);
        $summary = $builder->build($data);

        return view('market-pulse.index', [
            'kpi' => $kpi,
            'mom' => $mom,

            'displayMonthDate' => $displayMonthDate,
            'executive_summary' => $executive_summary,
            'executive_summary_is_html' => $executive_summary_is_html,

            'latestMonthDate' => $latestMonthDate,

            'salesTrend' => $salesTrend,
            'licensesByType' => $licensesByType,
            'categoryBreakdown' => $categoryBreakdown,
            'dispensaryByCounty' => $dispensaryByCounty,

            'printMode' => $printMode,

            'availableMonths' => $availableMonths,
            'dropdownMonths' => $dropdownMonths,
            'allowedMonths' => $partitionedMonths['allowed'],
            'lockedMonths' => $partitionedMonths['locked'],
            'currentMonthValue' => $currentMonthValue,
            'canViewCountyChart' => $canViewCountyChart,

            'monthDate' => $monthDate,
            'summaryText' => $summary['summary'],
            'summaryMeta' => $summary,
            'metrics' => $metrics,
        ]);
    }

    /**
     * Clean county names for display: fix "Saint Mary'S" -> "Saint Mary's", then title-case.
     */
    private function cleanCountyName(string $name): string
    {
        $name = trim($name);
        if ($name === '') {
            return $name;
        }
        // Lowercase the letter immediately after an apostrophe (e.g. Saint Mary'S -> Saint Mary's)
        $name = preg_replace_callback("/'(.)/u", fn($m) => "'" . mb_strtolower($m[1]), $name);
        // Title-case: capitalize first letter of each word
        return mb_convert_case(mb_strtolower($name), MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * Convert a BigQuery Row (or array) to a plain associative array.
     */
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
}
