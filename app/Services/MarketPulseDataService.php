<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Serves dashboard and Market Pulse data from the app database (synced from BigQuery).
 * Falls back to BigQuery when local tables are empty (e.g. before first sync).
 */
class MarketPulseDataService
{
    public function hasLocalData(): bool
    {
        return DB::table('market_pulse_kpis')->exists();
    }

    /**
     * Rows with keys: month_date, total_monthly_sales, total_transactions, avg_transaction_value, active_licenses.
     * Ordered by month_date DESC, limit $limit.
     */
    public function getKpisLatest(int $limit = 2): array
    {
        $rows = DB::table('market_pulse_kpis')
            ->orderByDesc('month_date')
            ->limit($limit)
            ->get();

        return $rows->map(fn ($r) => [
            'month_date' => $r->month_date,
            'total_monthly_sales' => $r->total_monthly_sales,
            'total_transactions' => $r->total_transactions,
            'avg_transaction_value' => $r->avg_transaction_value,
            'active_licenses' => $r->active_licenses,
        ])->all();
    }

    /**
     * Rows: month_date, total_sales.
     * If $year given: that year only (chronological).
     * If $year null and $limit given: last $limit months DESC (for dashboard).
     * If $year null and $limit null: all months ASC (for Market Pulse "all").
     */
    public function getSalesTrend(?string $year = null, ?int $limit = 12): array
    {
        $q = DB::table('market_pulse_sales_trend');
        if ($year !== null) {
            $q->whereYear('month_date', $year)->orderBy('month_date');
        } elseif ($limit !== null) {
            $q->orderByDesc('month_date')->limit($limit);
        } else {
            $q->orderBy('month_date');
        }
        $rows = $q->get();

        return $rows->map(fn ($r) => [
            'month_date' => $r->month_date,
            'total_sales' => $r->total_sales,
        ])->all();
    }

    /**
     * Last N months of KPI for license growth (month_date, active_licenses).
     */
    public function getLicenseGrowth(int $limit = 3): array
    {
        $rows = DB::table('market_pulse_kpis')
            ->orderByDesc('month_date')
            ->limit($limit)
            ->get(['month_date', 'active_licenses']);

        return $rows->map(fn ($r) => [
            'month_date' => $r->month_date,
            'active_licenses' => $r->active_licenses,
        ])->all();
    }

    /**
     * Category breakdown for a month. Returns rows with category, category_sales (alias for category_revenue), share_pct (computed).
     */
    public function getCategoryMix(string $monthDate): array
    {
        $rows = DB::table('market_pulse_category_breakdown')
            ->where('month_date', $monthDate)
            ->orderByDesc('category_revenue')
            ->get();
        $total = $rows->sum('category_revenue');
        if ((float) $total === 0.0) {
            return $rows->map(fn ($r) => [
                'category' => $r->category,
                'category_sales' => $r->category_revenue,
                'share_pct' => null,
            ])->all();
        }

        return $rows->map(fn ($r) => [
            'category' => $r->category,
            'category_sales' => $r->category_revenue,
            'share_pct' => $r->category_revenue ? (float) (($r->category_revenue / $total) * 100) : null,
        ])->all();
    }

    /**
     * Distinct month_date for history, newest first, limit $limit.
     */
    public function getHistoryMonths(int $limit = 12): array
    {
        $rows = DB::table('market_pulse_sales_trend')
            ->select('month_date')
            ->orderByDesc('month_date')
            ->limit($limit)
            ->get();

        return $rows->map(fn ($r) => ['month_date' => $r->month_date])->all();
    }

    public function getLatestMonth(): ?string
    {
        $row = DB::table('market_pulse_kpis')->orderByDesc('month_date')->first('month_date');

        return $row ? Carbon::parse($row->month_date)->format('Y-m-d') : null;
    }

    /**
     * KPI rows for display month and previous (2 rows, DESC).
     */
    public function getKpiForMonth(string $displayMonthDate): array
    {
        $rows = DB::table('market_pulse_kpis')
            ->where('month_date', '<=', $displayMonthDate)
            ->orderByDesc('month_date')
            ->limit(2)
            ->get();

        return $rows->map(fn ($r) => [
            'month_date' => $r->month_date,
            'total_monthly_sales' => $r->total_monthly_sales,
            'total_transactions' => $r->total_transactions,
            'avg_transaction_value' => $r->avg_transaction_value,
            'active_licenses' => $r->active_licenses,
        ])->all();
    }

    /**
     * Category breakdown for Market Pulse (category, category_revenue).
     */
    public function getCategoryBreakdown(string $monthDate): array
    {
        $rows = DB::table('market_pulse_category_breakdown')
            ->where('month_date', $monthDate)
            ->orderByDesc('category_revenue')
            ->get();

        return $rows->map(fn ($r) => [
            'category' => $r->category,
            'category_revenue' => $r->category_revenue,
        ])->all();
    }

    public function getLicensesByType(): array
    {
        $rows = DB::table('market_pulse_licenses_by_type')
            ->orderByDesc('active_license_count')
            ->get();

        return $rows->map(fn ($r) => [
            'license_type' => $r->license_type,
            'active_license_count' => $r->active_license_count,
        ])->all();
    }

    public function getDispensaryByCounty(): array
    {
        $rows = DB::table('market_pulse_dispensary_by_county')
            ->orderByDesc('dispensary_location_count')
            ->get();

        return $rows->map(fn ($r) => [
            'county' => $r->county,
            'dispensary_location_count' => $r->dispensary_location_count,
        ])->all();
    }

    /**
     * Available months for dropdown (month_date), newest first.
     */
    public function getAvailableMonths(): array
    {
        $rows = DB::table('market_pulse_sales_trend')
            ->select('month_date')
            ->orderByDesc('month_date')
            ->get();

        return $rows->map(fn ($r) => ['month_date' => $r->month_date])->all();
    }

    public function getLastSyncedAt(): ?\DateTimeInterface
    {
        $row = DB::table('market_pulse_sync_meta')->where('id', 1)->first();

        return $row && $row->last_synced_at ? Carbon::parse($row->last_synced_at) : null;
    }
}
