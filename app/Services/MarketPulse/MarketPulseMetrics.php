<?php

namespace App\Services\MarketPulse;

use App\Services\BigQueryService;

class MarketPulseMetrics
{
    public function __construct(private BigQueryService $bq) {}

    public function forMonth(string $monthDate): array
    {
        // monthDate example: "2025-12-01" (or whatever your month_date stores)
        $current = $this->getSnapshot($monthDate);
        $previous = $this->getSnapshot($this->prevMonth($monthDate));

        return [
            'month_date' => $monthDate,

            // Current month values
            'total_sales'       => (float) ($current['total_sales'] ?? 0),
            'avg_transaction'   => (float) ($current['avg_transaction'] ?? 0),
            'total_transactions'=> (int)   ($current['total_transactions'] ?? 0),

            // Previous month values
            'prev_total_sales'       => (float) ($previous['total_sales'] ?? 0),
            'prev_avg_transaction'   => (float) ($previous['avg_transaction'] ?? 0),
            'prev_total_transactions'=> (int)   ($previous['total_transactions'] ?? 0),

            // Category breakdown (top 3 + concentration)
            'top_categories' => $this->getTopCategories($monthDate), // array of ['category' => ..., 'revenue' => ..., 'share' => ...]
        ];
    }

    private function getSnapshot(string $monthDate): array
    {
        $sql = "
          SELECT
            month_date,
            total_monthly_sales,
            avg_transaction_value,
            total_transactions
          FROM `mca-dashboard-456223.terpinsights_mart.market_pulse_monthly_kpis` 
          WHERE month_date = @month_date
          LIMIT 1
        ";

        $rows = $this->bq->runParameterizedQuery($sql, [
            'month_date' => $monthDate,
        ]);
        if (empty($rows)) {
            return [];
        }

        $row = (array) $rows[0];

        // Map view columns (total_monthly_sales, avg_transaction_value) to keys ExecutiveSummaryBuilder expects
        return [
            'total_sales' => (float) ($row['total_monthly_sales'] ?? $row['total_sales'] ?? 0),
            'avg_transaction' => (float) ($row['avg_transaction_value'] ?? $row['avg_transaction'] ?? 0),
            'total_transactions' => (int) ($row['total_transactions'] ?? 0),
        ];
    }

    private function getTopCategories(string $monthDate): array
    {
        // Pull category revenue for the month, compute shares, return top 3
        // Schema: product_category_name, total_price (not category/revenue)
        $sql = "
          WITH base AS (
            SELECT
              product_category_name AS category,
              SUM(total_price) AS revenue
            FROM `mca-dashboard-456223.terpinsights_core.fact_market_sales_monthly`
            WHERE month_date = @month_date
            GROUP BY product_category_name
          ),
          totals AS (
            SELECT SUM(revenue) AS total_revenue FROM base
          )
          SELECT
            b.category,
            b.revenue,
            SAFE_DIVIDE(b.revenue, t.total_revenue) AS share
          FROM base b
          CROSS JOIN totals t
          ORDER BY b.revenue DESC
          LIMIT 3
        ";

        $rows = $this->bq->runParameterizedQuery($sql, [
            'month_date' => $monthDate,
        ]);

        return array_map(function ($r) {
            $r = (array) $r;
            return [
                'category' => (string) ($r['category'] ?? ''),
                'revenue'  => (float)  ($r['revenue'] ?? 0),
                'share'    => (float)  ($r['share'] ?? 0),
            ];
        }, $rows);
    }

    private function prevMonth(string $monthDate): string
    {
        return date('Y-m-01', strtotime($monthDate . ' -1 month'));
    }
}
