<x-app-layout>
    <div class="py-8 px-4 sm:px-6 lg:px-8 bg-[#F8F9FA] min-h-screen print:bg-white print:py-6">
        <div class="max-w-6xl mx-auto">
            {{-- Print-only report header (logo + title + month) --}}
            @php
                $printTitle = ($displayMonthDate ?? $kpi['month_date'] ?? null) ? \Carbon\Carbon::parse($displayMonthDate ?? $kpi['month_date'])->format('F Y') . ' Market Pulse' : 'Market Pulse';
            @endphp
            <div class="hidden print:block print:mb-8 print:pb-6 print:border-b print:border-gray-200">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-lg bg-[#16a34a] flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">{{ $printTitle }}</h1>
                        <p class="text-sm text-gray-500">Maryland Cannabis Market Intelligence · TerpInsights</p>
                    </div>
                </div>
            </div>

            {{-- Print / Save as PDF hint (hidden when actually printing) --}}
            @if($printMode ?? false)
            <div class="no-print mb-6 rounded-lg bg-green-50 border border-green-200 p-4 flex items-center justify-between gap-4">
                <p class="text-sm text-green-800">Use your browser’s <strong>Print</strong> (Ctrl+P / Cmd+P) and choose <strong>Save as PDF</strong> to download this report with all charts and KPIs. Data is directly sourced from the Maryland Cannabis Administration (MCA).</p>
                <a href="{{ route('market-pulse', ['month' => isset($displayMonthDate) ? \Carbon\Carbon::parse($displayMonthDate)->format('Y-m') : '']) }}" class="shrink-0 text-sm font-medium text-[#16a34a] hover:underline">View without print mode</a>
            </div>
            @endif

            {{-- Snapshot month dropdown (hidden when printing) --}}
            @if(!($printMode ?? false) && !empty($dropdownMonths ?? []))
            <div class="no-print mb-6 flex flex-wrap items-center gap-3">
                <label for="snapshot-month" class="text-sm font-medium text-gray-700">View snapshot</label>
                <div class="relative inline-block min-w-[200px]">
                    <select id="snapshot-month" class="block w-full appearance-none rounded-lg border border-gray-300 bg-white py-2.5 pl-4 pr-10 text-sm font-medium text-gray-900 shadow-sm transition focus:border-[#16a34a] focus:outline-none focus:ring-2 focus:ring-[#16a34a]/20 hover:border-gray-400">
                        @foreach($dropdownMonths ?? [] as $value => $label)
                            @php $isLocked = array_key_exists($value, $lockedMonths ?? []); @endphp
                            <option value="{{ $value }}" {{ ($currentMonthValue ?? '') === $value ? 'selected' : '' }} data-locked="{{ $isLocked ? '1' : '0' }}">{{ $label }}{{ $isLocked ? ' (Pro)' : '' }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                        <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </div>
            </div>
            {{-- Inline modal for locked month selection --}}
            <div id="locked-month-modal" class="no-print hidden fixed inset-0 z-50 overflow-y-auto" aria-hidden="true">
                <div class="flex min-h-full items-center justify-center p-4">
                    <div class="fixed inset-0 bg-black/40" onclick="document.getElementById('locked-month-modal').classList.add('hidden')"></div>
                    <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                        <p class="text-gray-700">Historical market snapshots are available on the Professional plan.</p>
                        <p class="mt-2 text-sm text-gray-500">Unlock full market history and advanced analytics with Professional.</p>
                        <div class="mt-6 flex justify-end gap-3">
                            <button type="button" onclick="document.getElementById('locked-month-modal').classList.add('hidden')" class="px-4 py-2 rounded-md border border-gray-300 text-gray-700 hover:bg-gray-50">Close</button>
                            <a href="{{ route('welcome') }}#pricing" class="px-4 py-2 rounded-md bg-[#16a34a] text-white hover:bg-[#15803d]">View plans</a>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Main title and subtitle --}}
            <h1 class="text-3xl font-bold text-gray-900">
                {{ ($displayMonthDate ?? $kpi['month_date'] ?? null) ? \Carbon\Carbon::parse($displayMonthDate ?? $kpi['month_date'])->format('F Y') . ' ' : '' }}Market Pulse
            </h1>
            <p class="text-gray-500 mt-1">Maryland Cannabis Market Intelligence</p>

            {{-- Executive Summary card (from published snapshot when available) --}}
            <div class="mt-8 bg-white rounded-xl shadow-sm border border-gray-100 p-6 md:p-8 print-keep-together">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Executive Summary</h2>
                <p class="text-gray-600 leading-relaxed">
                    @if(!empty(trim((string) ($executive_summary ?? ''))))
                        @if($executive_summary_is_html ?? false)
                            {!! $executive_summary !!}
                        @else
                            {{ $executive_summary }}
                        @endif
                    @else
                        No executive summary published for this month yet.
                    @endif
                </p>
            </div>

            {{-- Key metrics cards --}}
            <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 print-keep-together">
                {{-- Total Monthly Sales --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="text-sm text-gray-500 mb-1">Total Monthly Sales</div>
                    <div class="text-2xl font-bold text-gray-900">
                        @if($kpi && $kpi['total_monthly_sales'] !== null)
                            ${{ number_format($kpi['total_monthly_sales'] / 1000000, 1) }}M
                        @else
                            —
                        @endif
                    </div>
                    @php
                        $momSales = $mom['total_monthly_sales'] ?? null;
                        $isUp = $momSales !== null && $momSales > 0;
                        $colorClass = $momSales === null ? 'text-gray-500' : ($isUp ? 'text-green-500' : 'text-red-500');
                    @endphp
                    <div class="mt-2 flex items-center gap-1.5 text-sm {{ $colorClass }}">
                        @if($momSales !== null)
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                @if($isUp)
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v7"/>
                                @else
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                                @endif
                            </svg>
                        @endif
                        <span class="font-medium">{{ $momSales !== null ? number_format($momSales, 1) . '%' : '—' }} vs last month</span>
                    </div>
                </div>

                {{-- Active Licenses --}}
                @php
                    $momLicenses = $mom['active_licenses'] ?? null;
                    $licUp = $momLicenses !== null && $momLicenses > 0;
                    $licColor = $momLicenses === null ? 'text-gray-500' : ($licUp ? 'text-green-500' : 'text-red-500');
                @endphp
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="text-sm text-gray-500 mb-1">Active Licenses</div>
                    <div class="text-2xl font-bold text-gray-900">
                        @if($kpi && isset($kpi['active_licenses']) && $kpi['active_licenses'] !== null)
                            {{ number_format($kpi['active_licenses']) }}
                        @else
                            —
                        @endif
                    </div>
                    <div class="mt-2 flex items-center gap-1.5 text-sm {{ $licColor }}">
                        @if($momLicenses !== null)
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                @if($licUp)
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v7"/>
                                @else
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                                @endif
                            </svg>
                        @endif
                        <span class="font-medium">{{ $momLicenses !== null ? number_format($momLicenses, 1) . '%' : '—' }} vs last month</span>
                    </div>
                </div>

                {{-- Avg Transaction --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="text-sm text-gray-500 mb-1">Avg Transaction</div>
                    <div class="text-2xl font-bold text-gray-900">
                        @if($kpi && $kpi['avg_transaction_value'] !== null)
                            ${{ number_format($kpi['avg_transaction_value'], 2) }}
                        @else
                            —
                        @endif
                    </div>
                    @php
                        $momAvg = $mom['avg_transaction_value'] ?? null;
                        $avgUp = $momAvg !== null && $momAvg > 0;
                        $avgColor = $momAvg === null ? 'text-gray-500' : ($avgUp ? 'text-green-500' : 'text-red-500');
                    @endphp
                    <div class="mt-2 flex items-center gap-1.5 text-sm {{ $avgColor }}">
                        @if($momAvg !== null)
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                @if($avgUp)
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v7"/>
                                @else
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                                @endif
                            </svg>
                        @endif
                        <span class="font-medium">{{ $momAvg !== null ? number_format($momAvg, 1) . '%' : '—' }} vs last month</span>
                    </div>
                </div>

                {{-- Total Transactions --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="text-sm text-gray-500 mb-1">Total Transactions</div>
                    <div class="text-2xl font-bold text-gray-900">
                        @if($kpi && $kpi['total_transactions'] !== null)
                            {{ number_format($kpi['total_transactions']) }}
                        @else
                            —
                        @endif
                    </div>
                    @php
                        $momTx = $mom['total_transactions'] ?? null;
                        $txUp = $momTx !== null && $momTx > 0;
                        $txColor = $momTx === null ? 'text-gray-500' : ($txUp ? 'text-green-500' : 'text-red-500');
                    @endphp
                    <div class="mt-2 flex items-center gap-1.5 text-sm {{ $txColor }}">
                        @if($momTx !== null)
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                @if($txUp)
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v7"/>
                                @else
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l7-7m7 7V3"/>
                                @endif
                            </svg>
                        @endif
                        <span class="font-medium">{{ $momTx !== null ? number_format($momTx, 1) . '%' : '—' }} vs last month</span>
                    </div>
                </div>
            </div>

            {{-- Detailed Analytics --}}
            <h2 class="text-2xl font-bold text-gray-900 mt-12 mb-4 print:mt-8 print:mb-4">Detailed Analytics</h2>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                {{-- 1) Monthly Sales Trends (line chart) --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 print-keep-together">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-gray-900">Monthly Sales Trends</h3>
                        <div class="flex items-center gap-2 no-print">
                            <button type="button" data-chart-id="chartSalesTrend" data-csv-name="month" data-csv-value="sales" class="chart-export-png p-2 rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700" title="Download PNG">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            </button>
                            <button type="button" data-chart-id="chartSalesTrend" data-csv-name="month" data-csv-value="sales" class="chart-export-csv p-2 rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700" title="Download CSV">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </button>
                        </div>
                    </div>
                    <div class="h-72">
                        <canvas id="chartSalesTrend" height="260"></canvas>
                    </div>
                </div>

                {{-- 2) Active Licenses by Type (vertical bar, blue) --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 print-keep-together">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-gray-900">Active Licenses by Type</h3>
                        <div class="flex items-center gap-2 no-print">
                            <button type="button" data-chart-id="chartLicensesByType" data-csv-name="type" data-csv-value="count" class="chart-export-png p-2 rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700" title="Download PNG">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            </button>
                            <button type="button" data-chart-id="chartLicensesByType" data-csv-name="type" data-csv-value="count" class="chart-export-csv p-2 rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700" title="Download CSV">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </button>
                        </div>
                    </div>
                    <div class="h-72">
                        <canvas id="chartLicensesByType" height="260"></canvas>
                    </div>
                </div>

                {{-- 3) Category Revenue Breakdown (doughnut, multi-color) --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 print-keep-together">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-gray-900">Category Revenue Breakdown</h3>
                        <div class="flex items-center gap-2 no-print">
                            <button type="button" data-chart-id="chartCategoryBreakdown" data-csv-name="category" data-csv-value="value" class="chart-export-png p-2 rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700" title="Download PNG">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            </button>
                            <button type="button" data-chart-id="chartCategoryBreakdown" data-csv-name="category" data-csv-value="value" class="chart-export-csv p-2 rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700" title="Download CSV">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </button>
                        </div>
                    </div>
                    <div class="h-72 flex items-center justify-center">
                        <canvas id="chartCategoryBreakdown" height="260"></canvas>
                    </div>
                </div>

                {{-- 4) Dispensary Distribution by County (horizontal bar) - Pro only for Starter --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 print-keep-together relative">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-gray-900">Dispensary Distribution by County</h3>
                        @if($canViewCountyChart ?? true)
                        <div class="flex items-center gap-2 no-print">
                            <button type="button" data-chart-id="chartDispensaryByCounty" data-csv-name="county" data-csv-value="dispensary_count" class="chart-export-png p-2 rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700" title="Download PNG">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            </button>
                            <button type="button" data-chart-id="chartDispensaryByCounty" data-csv-name="county" data-csv-value="dispensary_count" class="chart-export-csv p-2 rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700" title="Download CSV">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </button>
                        </div>
                        @endif
                    </div>
                    @if($canViewCountyChart ?? true)
                    <div class="h-72">
                        <canvas id="chartDispensaryByCounty" height="260"></canvas>
                    </div>
                    @else
                    {{-- Blurred placeholder + upgrade CTA for Starter plan --}}
                    <div class="h-72 relative flex items-center justify-center overflow-hidden rounded-lg bg-gray-100">
                        <div class="absolute inset-0 backdrop-blur-sm bg-gray-200/60 flex flex-col items-center justify-center p-6 text-center">
                            <svg class="w-12 h-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            <p class="text-gray-600 font-medium">County-level analytics</p>
                            <p class="text-sm text-gray-500 mt-1">Unlock full market history and advanced analytics with Professional.</p>
                            <a href="{{ route('welcome') }}#pricing" class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-md bg-[#16a34a] text-white text-sm font-medium hover:bg-[#15803d]">View plans</a>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <p class="mt-8 text-sm text-gray-500 print:mt-6">Data is directly sourced from the Maryland Cannabis Administration (MCA). Updated monthly.</p>

            {{-- Print-only footer (repeated at bottom of report) --}}
            <div class="hidden print:block mt-10 pt-4 border-t border-gray-200 text-xs text-gray-500">
                {{ $printTitle }} · TerpInsights · Data: Maryland Cannabis Administration (MCA)
            </div>
        </div>
    </div>

    {{-- Chart.js and chart initialization --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        (function () {
            const salesTrend = @json($salesTrend ?? ['labels' => [], 'values' => []]);
            const licensesByType = @json($licensesByType ?? ['labels' => [], 'values' => []]);
            const categoryBreakdown = @json($categoryBreakdown ?? ['labels' => [], 'values' => [], 'share_pct' => []]);
            const dispensaryByCounty = @json($dispensaryByCounty ?? ['labels' => [], 'values' => []]);

            const chartInstances = {};

            // 1) Monthly Sales Trends - line chart (green, polished)
            const salesCtx = document.getElementById('chartSalesTrend');
            if (salesCtx) {
                chartInstances.chartSalesTrend = new Chart(salesCtx, {
                    type: 'line',
                    data: {
                        labels: salesTrend.labels || [],
                        datasets: [{
                            label: 'Monthly Sales ($)',
                            data: salesTrend.values || [],
                            borderColor: 'rgb(22, 163, 74)',
                            backgroundColor: 'rgba(22, 163, 74, 0.12)',
                            fill: true,
                            tension: 0.3,
                            borderWidth: 2,
                            pointRadius: 2,
                            pointHoverRadius: 5
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        layout: { padding: { top: 8, right: 8, bottom: 4, left: 4 } },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function (ctx) {
                                        const v = ctx.parsed?.y ?? 0;
                                        const s = v >= 1e6 ? '$' + (v / 1e6).toFixed(1) + 'M' : '$' + Number(v).toLocaleString();
                                        return 'Sales: ' + s;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { color: 'rgba(0,0,0,0.06)' },
                                ticks: { maxTicksLimit: 12, font: { size: 11 }, padding: 6 }
                            },
                            y: {
                                beginAtZero: true,
                                grid: { color: 'rgba(0,0,0,0.06)' },
                                ticks: {
                                    callback: function (v) {
                                        return v >= 1e6 ? '$' + (v / 1e6).toFixed(1) + 'M' : '$' + v;
                                    },
                                    padding: 6
                                }
                            }
                        }
                    }
                });
            }

            // 2) Active Licenses by Type - vertical bar (color-by-tier, rounded)
            const licensesCtx = document.getElementById('chartLicensesByType');
            if (licensesCtx) {
                const licensesMax = Math.max(...(licensesByType.values || [0]), 1);
                chartInstances.chartLicensesByType = new Chart(licensesCtx, {
                    type: 'bar',
                    data: {
                        labels: licensesByType.labels || [],
                        datasets: [{
                            label: 'Active Licenses',
                            data: licensesByType.values || [],
                            backgroundColor: function (ctx) {
                                const v = (ctx.parsed && typeof ctx.parsed.y === 'number') ? ctx.parsed.y : 0;
                                const t = licensesMax;
                                const p = t ? v / t : 0;
                                return p >= 0.7 ? 'rgba(22, 163, 74, 0.85)' : p >= 0.4 ? 'rgba(249, 115, 22, 0.85)' : 'rgba(59, 130, 246, 0.75)';
                            },
                            borderColor: function (ctx) {
                                const v = (ctx.parsed && typeof ctx.parsed.y === 'number') ? ctx.parsed.y : 0;
                                const t = licensesMax;
                                const p = t ? v / t : 0;
                                return p >= 0.7 ? 'rgb(22, 163, 74)' : p >= 0.4 ? 'rgb(249, 115, 22)' : 'rgb(59, 130, 246)';
                            },
                            borderWidth: 1,
                            borderRadius: { topLeft: 4, topRight: 4 },
                            barPercentage: 0.75,
                            categoryPercentage: 0.85
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        layout: { padding: { left: 4, right: 8 } },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function (ctx) {
                                        const n = ctx.raw ?? 0;
                                        return 'Active licenses: ' + Number(n).toLocaleString();
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { font: { size: 11 }, padding: 8 }
                            },
                            y: {
                                beginAtZero: true,
                                grid: { color: 'rgba(0,0,0,0.06)' },
                                ticks: { maxTicksLimit: 8, padding: 6 }
                            }
                        }
                    }
                });
            }

            // 3) Category Revenue Breakdown - doughnut (polished spacing & legend)
            const categoryCtx = document.getElementById('chartCategoryBreakdown');
            const categoryValues = categoryBreakdown.share_pct?.length ? (categoryBreakdown.share_pct || []) : (categoryBreakdown.category_sales || categoryBreakdown.values || []);
            if (categoryCtx) {
                chartInstances.chartCategoryBreakdown = new Chart(categoryCtx, {
                    type: 'doughnut',
                    data: {
                        labels: categoryBreakdown.labels || [],
                        datasets: [{
                            data: categoryValues,
                            backgroundColor: [
                                'rgb(22, 163, 74)',
                                'rgba(59, 130, 246, 0.9)',
                                'rgba(249, 115, 22, 0.9)',
                                'rgba(147, 51, 234, 0.85)',
                                'rgba(22, 163, 74, 0.65)',
                                'rgba(59, 130, 246, 0.6)'
                            ],
                            borderWidth: 1.5,
                            borderColor: '#fff',
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        layout: { padding: 12 },
                        cutout: 58,
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: { padding: 14, font: { size: 12 }, usePointStyle: true }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        const raw = context.raw ?? 0;
                                        const num = Number(raw);
                                        const total = context.dataset.data.reduce(function (a, b) { return a + b; }, 0);
                                        const pct = total ? ((num / total) * 100).toFixed(1) : '0.0';
                                        const dollarVal = categoryBreakdown.values && categoryBreakdown.values[context.dataIndex] != null
                                            ? categoryBreakdown.values[context.dataIndex]
                                            : (num > 100 ? num : null);
                                        const formatted = dollarVal != null
                                            ? '$' + Number(dollarVal).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
                                            : (num.toLocaleString('en-US', { maximumFractionDigits: 1 }) + '%');
                                        return context.label + ': ' + formatted + (total ? ' (' + pct + '%)' : '');
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // 4) Dispensary Distribution by County - horizontal bar (rounded, gradient, cleaner)
            const dispensaryCtx = document.getElementById('chartDispensaryByCounty');
            if (dispensaryCtx) {
                const maxVal = Math.max(...(dispensaryByCounty.values || [0]), 1);
                chartInstances.chartDispensaryByCounty = new Chart(dispensaryCtx, {
                    type: 'bar',
                    data: {
                        labels: dispensaryByCounty.labels || [],
                        datasets: [{
                            label: 'Dispensary locations',
                            data: dispensaryByCounty.values || [],
                            backgroundColor: function (ctx) {
                                const v = (ctx.parsed && typeof ctx.parsed.x === 'number') ? ctx.parsed.x : 0;
                                const t = maxVal;
                                const p = t ? v / t : 0;
                                return p >= 0.7 ? 'rgba(22, 163, 74, 0.85)' : p >= 0.4 ? 'rgba(249, 115, 22, 0.85)' : 'rgba(59, 130, 246, 0.75)';
                            },
                            borderColor: function (ctx) {
                                const v = (ctx.parsed && typeof ctx.parsed.x === 'number') ? ctx.parsed.x : 0;
                                const t = maxVal;
                                const p = t ? v / t : 0;
                                return p >= 0.7 ? 'rgb(22, 163, 74)' : p >= 0.4 ? 'rgb(249, 115, 22)' : 'rgb(59, 130, 246)';
                            },
                            borderWidth: 1,
                            borderRadius: { topRight: 4, bottomRight: 4 },
                            barPercentage: 0.75,
                            categoryPercentage: 0.85
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        indexAxis: 'y',
                        layout: { padding: { left: 4, right: 8 } },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function (ctx) {
                                        const n = ctx.raw ?? 0;
                                        return 'Dispensary locations: ' + n.toLocaleString();
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                ticks: { maxTicksLimit: 10 },
                                grid: { color: 'rgba(0,0,0,0.06)' }
                            },
                            y: {
                                grid: { display: false },
                                ticks: { font: { size: 12 }, padding: 8 }
                            }
                        }
                    }
                });
            }

            // Export PNG: download canvas as image
            document.querySelectorAll('.chart-export-png').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const chartId = this.getAttribute('data-chart-id');
                    const canvas = document.getElementById(chartId);
                    if (!canvas) return;
                    const link = document.createElement('a');
                    link.download = (chartId.replace('chart', '') + '-chart.png').replace(/([A-Z])/g, '-$1').toLowerCase().replace(/^-/, '') + '.png';
                    link.href = canvas.toDataURL('image/png');
                    link.click();
                });
            });

            // Export CSV: build from chart data
            document.querySelectorAll('.chart-export-csv').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const chartId = this.getAttribute('data-chart-id');
                    const nameCol = this.getAttribute('data-csv-name') || 'label';
                    const valueCol = this.getAttribute('data-csv-value') || 'value';
                    const chart = chartInstances[chartId];
                    if (!chart || !chart.data.labels) return;
                    const labels = chart.data.labels;
                    const values = chart.data.datasets[0]?.data || [];
                    const rows = [nameCol + ',' + valueCol];
                    labels.forEach(function (l, i) {
                        rows.push('"' + String(l).replace(/"/g, '""') + '",' + (values[i] ?? ''));
                    });
                    const blob = new Blob([rows.join('\n')], { type: 'text/csv' });
                    const link = document.createElement('a');
                    link.download = (chartId.replace('chart', '') + '-data.csv').replace(/([A-Z])/g, '-$1').toLowerCase().replace(/^-/, '') + '.csv';
                    link.href = URL.createObjectURL(blob);
                    link.click();
                    URL.revokeObjectURL(link.href);
                });
            });

            // Snapshot month dropdown: navigate to selected month, or show upgrade modal for locked months
            var snapshotSelect = document.getElementById('snapshot-month');
            if (snapshotSelect) {
                var currentVal = snapshotSelect.value;
                snapshotSelect.addEventListener('change', function () {
                    var opt = this.options[this.selectedIndex];
                    var locked = opt && opt.getAttribute('data-locked') === '1';
                    if (locked) {
                        document.getElementById('locked-month-modal').classList.remove('hidden');
                        this.value = currentVal; // revert selection
                        return;
                    }
                    currentVal = this.value;
                    var month = this.value;
                    var base = '{{ route("market-pulse") }}';
                    var url = month ? (base + '?month=' + encodeURIComponent(month)) : base;
                    @if($printMode ?? false)
                    url += (url.indexOf('?') !== -1 ? '&' : '?') + 'print=1';
                    @endif
                    window.location.href = url;
                });
            }
        })();
    </script>
</x-app-layout>
