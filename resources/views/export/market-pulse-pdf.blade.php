<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $monthLabel ?? 'Market Pulse' }} - PDF</title>
    <style>
        /* Market Pulse page look + inspo: scorecards, charts, professional header/footer */
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #343a40;
            line-height: 1.5;
            margin: 0;
            padding: 0;
            background: #ffffff;
        }
        .page-wrap {
            max-width: 100%;
            margin: 0 auto;
            padding: 40px 48px 48px;
        }
        .top-line {
            height: 0;
            border-top: 1px solid #dee2e6;
            margin-bottom: 24px;
        }
        /* Branding: logo + TerpInsights (same as dashboard / market-pulse-logo) */
        .header-brand-table {
            margin-bottom: 20px;
            border: none;
        }
        .header-brand-table td {
            border: none;
            padding: 0;
            vertical-align: middle;
        }
        .header-brand-table td:first-child {
            padding-right: 10px;
        }
        .header-logo {
            width: 32px;
            height: 32px;
            display: block;
        }
        .header-brand {
            font-size: 20px;
            font-weight: 600;
            color: #111827;
        }
        .report-title {
            font-size: 28px;
            font-weight: 700;
            color: #111827;
            margin: 0 0 4px 0;
            letter-spacing: -0.02em;
        }
        .subtitle {
            font-size: 13px;
            color: #6b7280;
            margin: 0 0 32px 0;
        }
        /* Executive Summary: same card as Market Pulse (bg-white rounded-xl shadow-sm border border-gray-100 p-6) */
        .summary-box {
            background: #ffffff;
            border: 1px solid #f3f4f6;
            border-radius: 12px;
            padding: 24px 28px;
            margin-bottom: 32px;
        }
        .summary-box h2 {
            font-size: 18px;
            font-weight: 700;
            color: #111827;
            margin: 0 0 12px 0;
        }
        .summary-box p {
            margin: 0;
            font-size: 12px;
            color: #4b5563;
            line-height: 1.6;
        }
        /* Scorecards: exact match to Market Pulse (four separate cards, gap between) */
        .scorecard-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 16px 0;
            margin-bottom: 32px;
        }
        .scorecard-grid td {
            width: 25%;
            padding: 24px;
            background: #ffffff;
            border: 1px solid #f3f4f6;
            border-radius: 12px;
            vertical-align: top;
        }
        .scorecard-label {
            font-size: 14px;
            color: #6b7280;
            margin: 0 0 4px 0;
        }
        .scorecard-value {
            font-size: 24px;
            font-weight: 700;
            color: #111827;
            margin: 0 0 8px 0;
        }
        .scorecard-mom {
            font-size: 14px;
            font-weight: 500;
        }
        .mom-up { color: #16a34a; }
        .mom-down { color: #dc2626; }
        .mom-flat { color: #6b7280; }
        h2.section {
            font-size: 20px;
            font-weight: 700;
            color: #111827;
            margin: 0 0 20px 0;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }
        /* Chart cards: same as Market Pulse (white, rounded, border) */
        .chart-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .chart-card h3 {
            font-size: 16px;
            font-weight: 700;
            color: #111827;
            margin: 0 0 16px 0;
        }
        .chart-card img {
            max-width: 100%;
            height: auto;
            display: block;
        }
        .chart-table-wrap { margin-top: 8px; }
        table.data {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        table.data th,
        table.data td {
            border: 1px solid #e5e7eb;
            padding: 8px 10px;
            text-align: left;
        }
        table.data th {
            background: #f9fafb;
            font-weight: 600;
            color: #374151;
        }
        table.data td { background: #fff; color: #343a40; }
        table.data tr:nth-child(even) td { background: #f9fafb; }
        .footer {
            margin-top: 32px;
            padding-top: 16px;
            border-top: 1px solid #dee2e6;
            font-size: 9px;
            color: #6c757d;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="page-wrap">
        <div class="top-line"></div>

        {{-- Logo + TerpInsights (same as dashboard: green square with trend line) --}}
        <table class="header-brand-table" cellpadding="0" cellspacing="0">
            <tr>
                <td>
                    @php
                        $logoSvg = '<svg viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="32" height="32" rx="6" fill="#16a34a"/><path d="M8 22V14L12 18L16 10L20 14L24 8" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
                        $logoB64 = base64_encode($logoSvg);
                    @endphp
                    <img src="data:image/svg+xml;base64,{{ $logoB64 }}" alt="" class="header-logo" width="32" height="32" />
                </td>
                <td><span class="header-brand">TerpInsights</span></td>
            </tr>
        </table>
        <h1 class="report-title">{{ $monthLabel ?? 'Market Pulse' }} Market Pulse</h1>
        <p class="subtitle">Maryland Cannabis Market Intelligence</p>

        {{-- Executive Summary (Market Pulse card style) --}}
        <div class="summary-box">
            <h2>Executive Summary</h2>
            <p>{{ $executive_summary_plain ?? strip_tags((string)($executive_summary ?? '')) }}</p>
        </div>

        {{-- Scorecards: same order and style as Market Pulse (four cards, no heading) --}}
        <table class="scorecard-grid">
            <tr>
                <td>
                    <div class="scorecard-label">Total Monthly Sales</div>
                    <div class="scorecard-value">{{ isset($kpi['total_monthly_sales']) ? '$' . number_format($kpi['total_monthly_sales'] / 1e6, 1) . 'M' : '—' }}</div>
                    @php $v = $mom['total_monthly_sales'] ?? null; @endphp
                    <div class="scorecard-mom {{ $v !== null && $v > 0 ? 'mom-up' : ($v !== null && $v < 0 ? 'mom-down' : 'mom-flat') }}">
                        @if($v !== null) {{ number_format($v, 1) }}% vs last month @else — @endif
                    </div>
                </td>
                <td>
                    <div class="scorecard-label">Active Licenses</div>
                    <div class="scorecard-value">{{ isset($kpi['active_licenses']) ? number_format($kpi['active_licenses']) : '—' }}</div>
                    @php $v = $mom['active_licenses'] ?? null; @endphp
                    <div class="scorecard-mom {{ $v !== null && $v > 0 ? 'mom-up' : ($v !== null && $v < 0 ? 'mom-down' : 'mom-flat') }}">
                        @if($v !== null) {{ number_format($v, 1) }}% vs last month @else — @endif
                    </div>
                </td>
                <td>
                    <div class="scorecard-label">Avg Transaction</div>
                    <div class="scorecard-value">{{ isset($kpi['avg_transaction_value']) ? '$' . number_format($kpi['avg_transaction_value'], 2) : '—' }}</div>
                    @php $v = $mom['avg_transaction_value'] ?? null; @endphp
                    <div class="scorecard-mom {{ $v !== null && $v > 0 ? 'mom-up' : ($v !== null && $v < 0 ? 'mom-down' : 'mom-flat') }}">
                        @if($v !== null) {{ number_format($v, 1) }}% vs last month @else — @endif
                    </div>
                </td>
                <td>
                    <div class="scorecard-label">Total Transactions</div>
                    <div class="scorecard-value">{{ isset($kpi['total_transactions']) ? number_format($kpi['total_transactions']) : '—' }}</div>
                    @php $v = $mom['total_transactions'] ?? null; @endphp
                    <div class="scorecard-mom {{ $v !== null && $v > 0 ? 'mom-up' : ($v !== null && $v < 0 ? 'mom-down' : 'mom-flat') }}">
                        @if($v !== null) {{ number_format($v, 1) }}% vs last month @else — @endif
                    </div>
                </td>
            </tr>
        </table>

        {{-- Detailed Analytics --}}
        <h2 class="section">Detailed Analytics</h2>

        {{-- 1) Monthly Sales Trends (chart when available, else table) --}}
        <div class="chart-card">
            <h3>Monthly Sales Trends</h3>
            @if(!empty($chartSalesTrendUrl ?? null))
                <img src="{{ $chartSalesTrendUrl }}" alt="Monthly Sales Trends" width="520" height="260" />
            @else
                <table class="data">
                    <thead><tr><th>Month</th><th>Total Sales</th></tr></thead>
                    <tbody>
                        @forelse(($salesTrend['labels'] ?? []) as $i => $label)
                        <tr>
                            <td>{{ $label }}</td>
                            <td>{{ isset($salesTrend['values'][$i]) ? '$' . number_format($salesTrend['values'][$i] / 1e6, 2) . 'M' : '—' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="2">No data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            @endif
        </div>

        {{-- 2) Active Licenses by Type --}}
        <div class="chart-card">
            <h3>Active Licenses by Type</h3>
            @if(!empty($chartLicensesByTypeUrl ?? null))
                <img src="{{ $chartLicensesByTypeUrl }}" alt="Active Licenses by Type" width="520" height="260" />
            @else
                <table class="data">
                    <thead><tr><th>License Type</th><th>Count</th></tr></thead>
                    <tbody>
                        @forelse(($licensesByType['labels'] ?? []) as $i => $label)
                        <tr>
                            <td>{{ $label }}</td>
                            <td>{{ $licensesByType['values'][$i] ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="2">No data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            @endif
        </div>

        {{-- 3) Category Revenue Breakdown --}}
        <div class="chart-card">
            <h3>Category Revenue Breakdown</h3>
            @if(!empty($chartCategoryBreakdownUrl ?? null))
                <img src="{{ $chartCategoryBreakdownUrl }}" alt="Category Revenue Breakdown" width="520" height="260" />
            @else
                <table class="data">
                    <thead><tr><th>Category</th><th>Revenue</th><th>Share</th></tr></thead>
                    <tbody>
                        @forelse(($categoryBreakdown['labels'] ?? []) as $i => $label)
                        <tr>
                            <td>{{ $label }}</td>
                            <td>{{ isset($categoryBreakdown['values'][$i]) ? '$' . number_format($categoryBreakdown['values'][$i] / 1e6, 2) . 'M' : '—' }}</td>
                            <td>{{ isset($categoryBreakdown['share_pct'][$i]) ? number_format($categoryBreakdown['share_pct'][$i], 1) . '%' : '—' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3">No data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            @endif
        </div>

        {{-- 4) Dispensary Distribution by County --}}
        <div class="chart-card">
            <h3>Dispensary Distribution by County</h3>
            @if(!empty($chartDispensaryByCountyUrl ?? null))
                <img src="{{ $chartDispensaryByCountyUrl }}" alt="Dispensary Distribution by County" width="520" height="260" />
            @else
                <table class="data">
                    <thead><tr><th>County</th><th>Locations</th></tr></thead>
                    <tbody>
                        @forelse(($dispensaryByCounty['labels'] ?? []) as $i => $label)
                        <tr>
                            <td>{{ $label }}</td>
                            <td>{{ $dispensaryByCounty['values'][$i] ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="2">No data</td></tr>
                        @endforelse
                    </tbody>
                </table>
            @endif
        </div>

        <div class="footer">
            Data is directly sourced from the Maryland Cannabis Administration (MCA). Updated monthly.<br>
            Page 1 of 1 • Generated by Market Pulse.
        </div>
    </div>
</body>
</html>
