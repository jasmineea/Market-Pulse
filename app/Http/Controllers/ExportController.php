<?php

namespace App\Http\Controllers;

use App\Services\BigQueryService;
use App\Services\MarketPulse\MarketPulseReportData;
use App\Services\MarketPulseDataService;
use App\Services\PlanGate;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use ZipArchive;

/**
 * Export page and CSV/ZIP downloads. Data sourced from Maryland Cannabis Administration (MCA).
 */
class ExportController extends Controller
{
    /**
     * List of export types that match the dataset (sales, category, licenses, regional).
     */
    public const EXPORT_TYPES = ['sales', 'category', 'licenses', 'regional'];

    /**
     * Available months from BigQuery (newest first). Used for export dropdown and plan-gate checks.
     *
     * @return array<string, string>  ['Y-m' => 'F Y', ...]
     */
    private function getAvailableMonthsMap(BigQueryService $bq): array
    {
        $allMonthsMap = [];
        try {
            $rows = $bq->runQueryCached('bq.export.available_months', "
              SELECT DISTINCT month_date
              FROM `mca-dashboard-456223.terpinsights_mart.market_pulse_sales_trend`
              ORDER BY month_date DESC
              LIMIT 24
            ");
            foreach ($rows as $r) {
                $d = $r['month_date'] ?? null;
                if ($d) {
                    $ym = Carbon::parse($d)->format('Y-m');
                    $allMonthsMap[$ym] = Carbon::parse($d)->format('F Y');
                }
            }
        } catch (\Throwable $e) {
            for ($i = 0; $i < 12; $i++) {
                $d = now()->subMonths($i);
                $allMonthsMap[$d->format('Y-m')] = $d->format('F Y');
            }
        }
        if (empty($allMonthsMap)) {
            $allMonthsMap[now()->format('Y-m')] = now()->format('F Y');
        }

        return $allMonthsMap;
    }

    /**
     * Export page: month selector and available exports.
     */
    public function index(BigQueryService $bq, MarketPulseDataService $dataService, PlanGate $planGate): View
    {
        $allMonthsMap = $this->getAvailableMonthsMap($bq, $dataService);

        $allowedMonthsMap = $planGate->getAllowedMonths($allMonthsMap, Auth::user());
        $months = [];
        foreach ($allowedMonthsMap as $value => $label) {
            $months[] = ['value' => $value, 'label' => $label];
        }
        if (empty($months)) {
            $months[] = ['value' => array_key_first($allMonthsMap), 'label' => reset($allMonthsMap)];
        }

        return view('export.index', [
            'months' => $months,
            'canExportRegional' => $planGate->canExportRegional(Auth::user()),
            'canDownloadFullPdf' => $planGate->canDownloadFullPdf(Auth::user()),
        ]);
    }

    /**
     * Download Market Pulse report as PDF for the selected month (one-click, no print dialog).
     * Includes chart images via QuickChart so the PDF matches the Market Pulse page.
     */
    public function marketPulsePdf(Request $request, MarketPulseReportData $reportData)
    {
        $request->validate([
            'month' => ['required', 'string', 'regex:/^\d{4}-\d{2}$/'],
        ]);
        $month = $request->input('month');
        $data = $reportData->forMonth($month);
        $data['monthLabel'] = Carbon::parse($month . '-01')->format('F Y');
        $data['executive_summary_plain'] = strip_tags((string) ($data['executive_summary'] ?? ''));
        $data['chartSalesTrendUrl'] = $this->buildQuickChartSalesTrendUrl($data['salesTrend'] ?? []);
        $data['chartLicensesByTypeUrl'] = $this->buildQuickChartLicensesByTypeUrl($data['licensesByType'] ?? []);
        $data['chartCategoryBreakdownUrl'] = $this->buildQuickChartCategoryBreakdownUrl($data['categoryBreakdown'] ?? []);
        $data['chartDispensaryByCountyUrl'] = $this->buildQuickChartDispensaryByCountyUrl($data['dispensaryByCounty'] ?? []);
        $pdf = Pdf::loadView('export.market-pulse-pdf', $data)
            ->setPaper('a4', 'portrait')
            ->setOption('isRemoteEnabled', true);
        $filename = 'Market_Pulse_' . Carbon::parse($month . '-01')->format('F_Y') . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Build QuickChart.io URL for line chart (Monthly Sales Trends). Chart.js-style config.
     */
    private function buildQuickChartSalesTrendUrl(array $salesTrend): ?string
    {
        $labels = array_slice($salesTrend['labels'] ?? [], 0, 24);
        $values = array_slice($salesTrend['values'] ?? [], 0, 24);
        if (empty($labels) || empty($values)) {
            return null;
        }
        $config = [
            'type' => 'line',
            'data' => [
                'labels' => $labels,
                'datasets' => [[
                    'label' => 'Monthly Sales',
                    'data' => $values,
                    'borderColor' => '#16a34a',
                    'backgroundColor' => 'rgba(22, 163, 74, 0.12)',
                    'fill' => true,
                    'tension' => 0.3,
                    'borderWidth' => 2,
                ]],
            ],
            'options' => [
                'plugins' => ['legend' => ['display' => false]],
                'scales' => [
                    'y' => ['beginAtZero' => true],
                ],
            ],
        ];
        return $this->quickChartUrl($config, 520, 260);
    }

    /**
     * Build QuickChart.io URL for vertical bar chart (Active Licenses by Type).
     */
    private function buildQuickChartLicensesByTypeUrl(array $licensesByType): ?string
    {
        $labels = array_slice($licensesByType['labels'] ?? [], 0, 15);
        $values = array_slice($licensesByType['values'] ?? [], 0, 15);
        if (empty($labels) || empty($values)) {
            return null;
        }
        $config = [
            'type' => 'bar',
            'data' => [
                'labels' => $labels,
                'datasets' => [[
                    'label' => 'Active Licenses',
                    'data' => $values,
                    'backgroundColor' => 'rgba(22, 163, 74, 0.85)',
                    'borderColor' => '#16a34a',
                    'borderWidth' => 1,
                ]],
            ],
            'options' => [
                'plugins' => ['legend' => ['display' => false]],
                'scales' => [
                    'y' => ['beginAtZero' => true],
                ],
            ],
        ];
        return $this->quickChartUrl($config, 520, 260);
    }

    /**
     * Build QuickChart.io URL for doughnut chart (Category Revenue Breakdown).
     */
    private function buildQuickChartCategoryBreakdownUrl(array $categoryBreakdown): ?string
    {
        $labels = array_slice($categoryBreakdown['labels'] ?? [], 0, 12);
        $pct = array_slice($categoryBreakdown['share_pct'] ?? [], 0, 12);
        if (empty($labels) || empty($pct)) {
            return null;
        }
        $colors = [
            '#16a34a', '#3b82f6', '#f97316', '#9333ea', '#eab308', '#ec4899',
            '#14b8a6', '#6366f1', '#84cc16', '#f43f5e', '#0ea5e9', '#78716c',
        ];
        $config = [
            'type' => 'doughnut',
            'data' => [
                'labels' => $labels,
                'datasets' => [[
                    'data' => $pct,
                    'backgroundColor' => array_slice($colors, 0, count($labels)),
                    'borderWidth' => 1.5,
                    'borderColor' => '#fff',
                ]],
            ],
            'options' => [
                'cutout' => '58%',
                'plugins' => [
                    'legend' => ['position' => 'right'],
                ],
            ],
        ];
        return $this->quickChartUrl($config, 520, 260);
    }

    /**
     * Build QuickChart.io URL for horizontal bar chart (Dispensary by County).
     */
    private function buildQuickChartDispensaryByCountyUrl(array $dispensaryByCounty): ?string
    {
        $labels = array_slice($dispensaryByCounty['labels'] ?? [], 0, 15);
        $values = array_slice($dispensaryByCounty['values'] ?? [], 0, 15);
        if (empty($labels) || empty($values)) {
            return null;
        }
        $config = [
            'type' => 'bar',
            'data' => [
                'labels' => $labels,
                'datasets' => [[
                    'label' => 'Dispensary locations',
                    'data' => $values,
                    'backgroundColor' => 'rgba(22, 163, 74, 0.85)',
                    'borderColor' => '#16a34a',
                    'borderWidth' => 1,
                ]],
            ],
            'options' => [
                'indexAxis' => 'y',
                'plugins' => ['legend' => ['display' => false]],
                'scales' => [
                    'x' => ['beginAtZero' => true],
                ],
            ],
        ];
        return $this->quickChartUrl($config, 520, 260);
    }

    /**
     * Return QuickChart.io image URL for a Chart.js config. Used for PDF chart images.
     */
    private function quickChartUrl(array $config, int $width = 520, int $height = 260): string
    {
        $json = json_encode($config, JSON_UNESCAPED_SLASHES);
        $encoded = rawurlencode($json);
        return 'https://quickchart.io/chart?c=' . $encoded . '&width=' . $width . '&height=' . $height;
    }

    /**
     * Download one or more CSVs as a ZIP, or a single CSV. POST with month + exports[].
     * Starter plan: regional export blocked.
     */
    public function download(Request $request, BigQueryService $bq, MarketPulseDataService $dataService, PlanGate $planGate)
    {
        $request->validate([
            'month' => ['required', 'string', 'regex:/^\d{4}-\d{2}$/'],
            'exports' => ['required', 'array', 'min:1'],
            'exports.*' => ['string', 'in:' . implode(',', self::EXPORT_TYPES)],
        ]);

        $exports = array_unique($request->input('exports'));
        $month = $request->input('month');
        $user = Auth::user();

        if (in_array('regional', $exports) && ! $planGate->canExportRegional($user)) {
            return redirect()->route('export.index')->with('error', 'County-level exports are available on the Professional plan.');
        }
        // Use same "first N months from data" as export index / Market Pulse for Starter
        $availableMonths = $this->getAvailableMonthsMap($bq, $dataService);
        if (! $planGate->isMonthAllowed($month, $user, $availableMonths)) {
            return redirect()->route('export.index')->with('error', 'That month is outside your plan\'s access window. Upgrade for full historical access.');
        }
        $monthDate = Carbon::parse($month . '-01')->format('Y-m-d');
        $monthLabel = Carbon::parse($month . '-01')->format('F_Y');

        $files = [];
        try {
            if (in_array('sales', $exports)) {
                $files[] = ['name' => "monthly_sales_summary_{$monthLabel}.csv", 'content' => $this->salesCsv($bq, $dataService, $monthDate)];
            }
            if (in_array('category', $exports)) {
                $files[] = ['name' => "category_revenue_breakdown_{$monthLabel}.csv", 'content' => $this->categoryCsv($bq, $dataService, $monthDate)];
            }
            if (in_array('licenses', $exports)) {
                $files[] = ['name' => "license_counts_by_type_{$monthLabel}.csv", 'content' => $this->licensesCsv($bq, $dataService, $monthDate)];
            }
            if (in_array('regional', $exports)) {
                $files[] = ['name' => "dispensary_distribution_by_county_{$monthLabel}.csv", 'content' => $this->regionalCsv($bq, $dataService)];
            }
        } catch (\Throwable $e) {
            return redirect()->route('export.index')->with('error', 'Export failed. Please try again.');
        }

        if (empty($files)) {
            return redirect()->route('export.index')->with('error', 'Select at least one export.');
        }

        if (count($files) === 1) {
            return response($files[0]['content'], 200, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $files[0]['name'] . '"',
            ]);
        }

        $zipPath = storage_path('app/temp/export_' . uniqid() . '.zip');
        @mkdir(dirname($zipPath), 0755, true);
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return redirect()->route('export.index')->with('error', 'Could not create zip file.');
        }
        foreach ($files as $f) {
            $zip->addFromString($f['name'], $f['content']);
        }
        $zip->close();
        $zipName = 'market_pulse_exports_' . $monthLabel . '.zip';
        return response()->download($zipPath, $zipName)->deleteFileAfterSend(true);
    }

    /** Cache TTL for export queries (1 hour) so repeated exports don't hit BQ every time. */
    private const EXPORT_CACHE_TTL = 3600;

    private function salesCsv(BigQueryService $bq, MarketPulseDataService $dataService, string $monthDate): string
    {
        if ($dataService->hasLocalData()) {
            $salesRow = DB::table('market_pulse_sales_trend')->where('month_date', $monthDate)->first();
            $kpiRow = DB::table('market_pulse_kpis')->where('month_date', $monthDate)->first();
            $salesRow = $salesRow ? (array) $salesRow : null;
            $kpiRow = $kpiRow ? (array) $kpiRow : null;
        } else {
            $rows = $bq->runQueryCached('bq.export.sales.' . $monthDate, "
              SELECT month_date, total_sales
              FROM `mca-dashboard-456223.terpinsights_mart.market_pulse_sales_trend`
              WHERE month_date = DATE('$monthDate')
            ", self::EXPORT_CACHE_TTL);
            $kpiRows = $bq->runQueryCached('bq.export.kpi.' . $monthDate, "
              SELECT month_date, total_monthly_sales, total_transactions, avg_transaction_value, active_licenses
              FROM `mca-dashboard-456223.terpinsights_mart.market_pulse_monthly_kpis_joined`
              WHERE month_date = DATE('$monthDate')
              LIMIT 1
            ", self::EXPORT_CACHE_TTL);
            $salesRow = $rows[0] ?? null;
            $kpiRow = $kpiRows[0] ?? null;
        }
        $out = "month,total_sales,total_monthly_sales,total_transactions,avg_transaction_value,active_licenses\n";
        $monthStr = $salesRow && isset($salesRow['month_date']) ? Carbon::parse($salesRow['month_date'])->format('Y-m-d') : $monthDate;
        $totalSales = $salesRow && isset($salesRow['total_sales']) ? (float) $salesRow['total_sales'] : '';
        $totalMonthly = $kpiRow && isset($kpiRow['total_monthly_sales']) ? (float) $kpiRow['total_monthly_sales'] : '';
        $transactions = $kpiRow && isset($kpiRow['total_transactions']) ? (int) $kpiRow['total_transactions'] : '';
        $avgVal = $kpiRow && isset($kpiRow['avg_transaction_value']) ? (float) $kpiRow['avg_transaction_value'] : '';
        $licenses = $kpiRow && isset($kpiRow['active_licenses']) ? (int) $kpiRow['active_licenses'] : '';
        $out .= sprintf("%s,%s,%s,%s,%s,%s\n", $monthStr, $totalSales, $totalMonthly, $transactions, $avgVal, $licenses);
        $out .= "\nData sourced from Maryland Cannabis Administration (MCA).\n";
        return $out;
    }

    private function categoryCsv(BigQueryService $bq, MarketPulseDataService $dataService, string $monthDate): string
    {
        if ($dataService->hasLocalData()) {
            $rows = $dataService->getCategoryBreakdown($monthDate);
        } else {
            $rows = $bq->runQueryCached('bq.export.category.' . $monthDate, "
              SELECT category, category_revenue
              FROM `mca-dashboard-456223.terpinsights_mart.market_pulse_category_breakdown_by_month`
              WHERE month_date = DATE('$monthDate')
              ORDER BY category_revenue DESC
            ", self::EXPORT_CACHE_TTL);
        }
        $out = "category,category_revenue,share_pct\n";
        $total = 0;
        foreach ($rows as $r) {
            $total += isset($r['category_revenue']) ? (float) $r['category_revenue'] : 0;
        }
        foreach ($rows as $r) {
            $cat = str_replace('"', '""', (string) ($r['category'] ?? ''));
            $revenue = isset($r['category_revenue']) ? (float) $r['category_revenue'] : 0;
            $pct = $total > 0 ? ($revenue / $total) * 100 : 0;
            $out .= sprintf("\"%s\",%s,%.2f\n", $cat, $revenue, $pct);
        }
        $out .= "\nData sourced from Maryland Cannabis Administration (MCA).\n";
        return $out;
    }

    private function licensesCsv(BigQueryService $bq, MarketPulseDataService $dataService, string $monthDate): string
    {
        $rows = $dataService->hasLocalData()
            ? $dataService->getLicensesByType()
            : $bq->runQueryCached('bq.export.licenses', "
              SELECT license_type, active_license_count
              FROM `mca-dashboard-456223.terpinsights_mart.market_pulse_active_licenses_by_type_latest`
              ORDER BY active_license_count DESC
            ", self::EXPORT_CACHE_TTL);
        $out = "license_type,active_license_count\n";
        foreach ($rows as $r) {
            $type = str_replace('"', '""', (string) ($r['license_type'] ?? ''));
            $count = isset($r['active_license_count']) ? (int) $r['active_license_count'] : 0;
            $out .= sprintf("\"%s\",%s\n", $type, $count);
        }
        $out .= "\nData sourced from Maryland Cannabis Administration (MCA).\n";
        return $out;
    }

    private function regionalCsv(BigQueryService $bq, MarketPulseDataService $dataService): string
    {
        $rows = $dataService->hasLocalData()
            ? $dataService->getDispensaryByCounty()
            : $bq->runQueryCached('bq.export.regional', "
              SELECT county, SUM(dispensary_location_count) AS dispensary_location_count
              FROM `mca-dashboard-456223.terpinsights_mart.market_pulse_dispensary_locations_by_county_latest`
              GROUP BY county
              ORDER BY dispensary_location_count DESC
            ", self::EXPORT_CACHE_TTL);
        $out = "county,dispensary_location_count\n";
        foreach ($rows as $r) {
            $county = str_replace('"', '""', (string) ($r['county'] ?? ''));
            $count = isset($r['dispensary_location_count']) ? (int) $r['dispensary_location_count'] : 0;
            $out .= sprintf("\"%s\",%s\n", $county, $count);
        }
        $out .= "\nData sourced from Maryland Cannabis Administration (MCA).\n";
        return $out;
    }
}
