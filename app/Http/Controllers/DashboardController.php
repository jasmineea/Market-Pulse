<?php

namespace App\Http\Controllers;

use App\Models\MarketPulseSnapshot;
use App\Services\BigQueryService;
use App\Services\PlanGate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Dashboard: Current Market Status (KPIs), Trend Previews (3 charts), Market Pulse History (all months with data).
     */
    public function index(Request $request, BigQueryService $bq, PlanGate $planGate): View
    {
        $kpi = null;
        $mom = [
            'total_monthly_sales' => null,
            'total_transactions' => null,
            'avg_transaction_value' => null,
            'active_licenses' => null,
        ];
        $salesTrendPreview = ['labels' => [], 'values' => []];
        $licenseGrowthPreview = ['labels' => [], 'values' => []];
        $categoryMixPreview = ['labels' => [], 'values' => [], 'topLabel' => null, 'topPct' => null];

        try {
            $kpiColumns = ['month_date', 'total_monthly_sales', 'total_transactions', 'avg_transaction_value', 'active_licenses'];
            $kpiSql = "
              SELECT month_date, total_monthly_sales, total_transactions, avg_transaction_value, active_licenses
              FROM `mca-dashboard-456223.terpinsights_mart.market_pulse_monthly_kpis_joined`
              ORDER BY month_date DESC
              LIMIT 2
            ";
            $kpiRows = $bq->runQuery($kpiSql);
            $currentRow = isset($kpiRows[0]) ? $this->rowToArray($kpiRows[0], $kpiColumns) : null;
            $previousRow = isset($kpiRows[1]) ? $this->rowToArray($kpiRows[1], $kpiColumns) : null;

            if ($currentRow !== null) {
                $kpi = [
                    'month_date' => $currentRow['month_date'] ?? null,
                    'total_monthly_sales' => isset($currentRow['total_monthly_sales']) ? (float) $currentRow['total_monthly_sales'] : null,
                    'total_transactions' => isset($currentRow['total_transactions']) ? (int) $currentRow['total_transactions'] : null,
                    'avg_transaction_value' => isset($currentRow['avg_transaction_value']) ? (float) $currentRow['avg_transaction_value'] : null,
                    'active_licenses' => isset($currentRow['active_licenses']) ? (int) $currentRow['active_licenses'] : null,
                ];
            }

            if ($currentRow && $previousRow) {
                $pct = fn($curr, $prev) => ($prev === null || (float) $prev == 0)
                    ? null
                    : (is_numeric($curr) && is_numeric($prev) ? (($curr - $prev) / $prev) * 100 : null);
                $mom['total_monthly_sales'] = $pct($currentRow['total_monthly_sales'] ?? null, $previousRow['total_monthly_sales'] ?? null);
                $mom['total_transactions'] = $pct($currentRow['total_transactions'] ?? null, $previousRow['total_transactions'] ?? null);
                $mom['avg_transaction_value'] = $pct($currentRow['avg_transaction_value'] ?? null, $previousRow['avg_transaction_value'] ?? null);
                $mom['active_licenses'] = $pct($currentRow['active_licenses'] ?? null, $previousRow['active_licenses'] ?? null);
            }

            // Sales trend: last 12 months for preview
            $salesRows = $bq->runQuery("
              SELECT month_date, total_sales
              FROM `mca-dashboard-456223.terpinsights_mart.market_pulse_sales_trend`
              ORDER BY month_date DESC
              LIMIT 12
            ");
            $salesRows = array_reverse($salesRows);
            $salesTrendPreview = [
                'labels' => array_map(fn($r) => isset($r['month_date']) && $r['month_date'] ? Carbon::parse($r['month_date'])->format('M Y') : '', $salesRows),
                'values' => array_map(fn($r) => isset($r['total_sales']) && $r['total_sales'] !== null ? (float) $r['total_sales'] : 0.0, $salesRows),
            ];

            // License growth: past quarter only (last 3 months), chronological order
            $licenseRows = $bq->runQuery("
              SELECT month_date, active_licenses
              FROM `mca-dashboard-456223.terpinsights_mart.market_pulse_monthly_kpis_joined`
              ORDER BY month_date DESC
              LIMIT 3
            ");
            $licenseRows = array_reverse($licenseRows);
            $licenseGrowthPreview = [
                'labels' => array_map(fn($r) => isset($r['month_date']) && $r['month_date'] ? Carbon::parse($r['month_date'])->format('M Y') : '', $licenseRows),
                'values' => array_map(fn($r) => isset($r['active_licenses']) && $r['active_licenses'] !== null ? (int) $r['active_licenses'] : 0, $licenseRows),
            ];

            // Category mix: latest month; top category share computed from chart data so subtitle matches donut
            $latestMonth = $kpi['month_date'] ?? null;
            $dateFilter = $latestMonth ? " WHERE month_date = DATE('" . Carbon::parse($latestMonth)->format('Y-m-d') . "')" : '';
            $catRows = $bq->runQuery("
              SELECT category, category_sales, share_pct
              FROM `mca-dashboard-456223.terpinsights_mart.market_pulse_category_breakdown_latest`
              $dateFilter
              ORDER BY category_sales DESC
            ");
            $catValues = array_map(fn($r) => isset($r['category_sales']) ? (float) $r['category_sales'] : 0.0, $catRows);
            $totalSales = array_sum($catValues);
            $topPct = null;
            if (!empty($catRows) && $totalSales > 0 && isset($catRows[0]['category_sales'])) {
                $topPct = ((float) $catRows[0]['category_sales'] / $totalSales) * 100;
            }
            $categoryMixPreview = [
                'labels' => array_map(fn($r) => (string) ($r['category'] ?? ''), $catRows),
                'values' => $catValues,
                'topLabel' => isset($catRows[0]['category']) ? (string) $catRows[0]['category'] : null,
                'topPct' => $topPct,
            ];
        } catch (\Throwable $e) {
            // BigQuery or config failure: pass empty structures so dashboard still renders
        }

        // Market Pulse History: all months that have data (last 12), with preview from snapshot or builder
        $historyItems = [];
        try {
            $monthRows = $bq->runQuery("
              SELECT DISTINCT month_date
              FROM `mca-dashboard-456223.terpinsights_mart.market_pulse_sales_trend`
              ORDER BY month_date DESC
              LIMIT 12
            ");
            $monthDates = array_map(fn($r) => isset($r['month_date']) ? Carbon::parse($r['month_date'])->startOfMonth()->format('Y-m-d') : null, $monthRows);
            $monthDates = array_filter($monthDates);

            $snapshotsByMonth = [];
            if (!empty($monthDates)) {
                $snapshots = MarketPulseSnapshot::query()
                    ->whereIn('month_date', $monthDates)
                    ->get();
                foreach ($snapshots as $s) {
                    $key = $s->month_date->format('Y-m-d');
                    $snapshotsByMonth[$key] = $s;
                }
            }

            foreach ($monthDates as $monthDate) {
                $snapshot = $snapshotsByMonth[$monthDate] ?? null;
                $historyItems[] = [
                    'month_date' => $monthDate,
                    'month_label' => Carbon::parse($monthDate)->format('F Y'),
                    'month_param' => Carbon::parse($monthDate)->format('Y-m'),
                    'snapshot' => $snapshot,
                ];
            }
        } catch (\Throwable $e) {
            // leave $historyItems empty
        }

        // Plan-based limit: Starter sees only latest 3 history items
        $limit = $planGate->historyLimit(Auth::user());
        if ($limit > 0) {
            $historyItems = array_slice($historyItems, 0, $limit);
        }

        // Paginate Market Pulse History (e.g. 10 per page)
        $perPage = 10;
        $currentPage = max(1, (int) $request->get('page', 1));
        $total = count($historyItems);
        $slice = array_slice($historyItems, ($currentPage - 1) * $perPage, $perPage);
        $historyItems = new LengthAwarePaginator(
            $slice,
            $total,
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('dashboard', [
            'kpi' => $kpi,
            'mom' => $mom,
            'salesTrendPreview' => $salesTrendPreview,
            'licenseGrowthPreview' => $licenseGrowthPreview,
            'categoryMixPreview' => $categoryMixPreview,
            'historyItems' => $historyItems,
            'canDownloadFullPdf' => $planGate->canDownloadFullPdf(Auth::user()),
        ]);
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
}
