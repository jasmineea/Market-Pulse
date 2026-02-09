<?php

namespace App\Services\MarketPulse;

class ExecutiveSummaryBuilder
{
    public function build(array $m): array
    {
        $monthLabel = date('F Y', strtotime($m['month_date']));

        $salesMoM = $this->pctChange($m['total_sales'], $m['prev_total_sales']);
        $avgMoM   = $this->pctChange($m['avg_transaction'], $m['prev_avg_transaction']);
        $txMoM    = $this->pctChange($m['total_transactions'], $m['prev_total_transactions']);

        $salesDir = $this->directionPhrase($salesMoM);
        $avgDir   = $this->directionPhrase($avgMoM);
        $txDir    = $this->directionPhrase($txMoM);

        $top = $m['top_categories'] ?? [];
        $top1 = $top[0]['category'] ?? 'the leading category';
        $top2 = $top[1]['category'] ?? null;
        $top3 = $top[2]['category'] ?? null;

        $categoryLine = $this->categorySentenceHtml($top1, $top2, $top3);

        // Simple “insight rules” (you can refine later)
        $consumerInsight = $avgMoM >= 2 ? 'modestly higher per-purchase spend' : ($avgMoM <= -2 ? 'slightly lower per-purchase spend' : 'stable basket sizes');
        $activityInsight = $txMoM >= 2 ? 'sustained demand supported by transaction volume' : ($txMoM <= -2 ? 'softening activity versus last month' : 'steady market activity');

        $takeaway = $this->takeaway($salesMoM, $txMoM, $avgMoM);

        $salesAmt = $this->money($m['total_sales']);
        $avgAmt = $this->money($m['avg_transaction']);
        $txNum = $this->num($m['total_transactions']);
        $salesPct = $this->fmtPct($salesMoM);

        $text = 'In <strong class="text-gray-900">' . e($monthLabel) . '</strong>, Maryland\'s cannabis market recorded <strong class="text-gray-900">' . e($salesAmt) . '</strong> in total sales, '
              . e($salesDir) . ' vs. the prior month (' . $this->pctSpan($salesMoM, $salesPct) . '). '
              . 'Average transaction value was <strong class="text-gray-900">' . e($avgAmt) . '</strong>, indicating ' . e($consumerInsight) . '. '
              . 'Total transactions reached <strong class="text-gray-900">' . e($txNum) . '</strong>, suggesting ' . e($activityInsight) . '. '
              . $categoryLine . ' '
              . 'Overall, <strong class="text-gray-900">' . e($takeaway) . '</strong>.';

        return [
            'month_label' => $monthLabel,
            'summary' => $text,

            // optional: expose computed fields for UI badges
            'sales_mom' => $salesMoM,
            'avg_tx_mom' => $avgMoM,
            'tx_mom' => $txMoM,
        ];
    }

    private function pctChange(float|int $current, float|int $previous): float
    {
        if ($previous == 0) return 0;
        return (($current - $previous) / $previous) * 100;
    }

    private function directionPhrase(float $pct): string
    {
        if ($pct > 0.25) return 'up';
        if ($pct < -0.25) return 'down';
        return 'roughly flat';
    }

    private function categorySentence(string $top1, ?string $top2, ?string $top3): string
    {
        $parts = array_values(array_filter([$top1, $top2, $top3]));
        if (count($parts) === 1) return "Revenue was led by {$parts[0]}.";
        if (count($parts) === 2) return "Revenue was led by {$parts[0]}, followed by {$parts[1]}.";
        return "Revenue remained concentrated in {$parts[0]}, followed by {$parts[1]} and {$parts[2]}.";
    }

    /** Same as categorySentence but wraps category names in <strong> for emphasis. */
    private function categorySentenceHtml(string $top1, ?string $top2, ?string $top3): string
    {
        $parts = array_values(array_filter([$top1, $top2, $top3]));
        $strong = fn(string $s) => '<strong class="text-gray-900">' . e($s) . '</strong>';
        if (count($parts) === 1) return 'Revenue was led by ' . $strong($parts[0]) . '.';
        if (count($parts) === 2) return 'Revenue was led by ' . $strong($parts[0]) . ', followed by ' . $strong($parts[1]) . '.';
        return 'Revenue remained concentrated in ' . $strong($parts[0]) . ', followed by ' . $strong($parts[1]) . ' and ' . $strong($parts[2]) . '.';
    }

    /** Wrap percentage for display: green (positive), red (negative), gray (flat). */
    private function pctSpan(float $pct, string $formatted): string
    {
        if ($pct > 0.25) {
            return '<span class="text-[#16a34a] font-semibold">' . e($formatted) . '</span>';
        }
        if ($pct < -0.25) {
            return '<span class="text-red-600 font-semibold">' . e($formatted) . '</span>';
        }
        return '<span class="text-gray-500 font-medium">' . e($formatted) . '</span>';
    }

    private function takeaway(float $salesMoM, float $txMoM, float $avgMoM): string
    {
        // Tiny rule system
        if ($salesMoM > 2 && $txMoM > 2) return 'growth was demand-driven with strong market participation';
        if ($salesMoM > 2 && $avgMoM > 2) return 'growth appears supported by higher per-transaction spend';
        if ($salesMoM < -2 && $txMoM < -2) return 'the market cooled month-over-month with lower activity';
        return 'the market remained stable with modest month-to-month movement';
    }

    private function money(float $v): string
    {
        // show M for big numbers
        if ($v >= 1000000) return '$' . number_format($v / 1000000, 1) . 'M';
        return '$' . number_format($v, 2);
    }

    private function num(int $v): string
    {
        return number_format($v);
    }

    private function fmtPct(float $v): string
    {
        $sign = $v > 0 ? '+' : '';
        return $sign . number_format($v, 1) . '%';
    }
}
