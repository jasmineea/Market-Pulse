<x-app-layout>
    <div class="py-8 px-4 sm:px-6 lg:px-8 bg-[#F8F9FA] min-h-screen">
        <div class="max-w-7xl mx-auto">
            {{-- Current Market Status --}}
            <h3 class="text-lg font-bold text-gray-900 mb-4">Current Market Status</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-10">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="text-sm text-gray-500 mb-1">Total Monthly Sales</div>
                    <div class="text-2xl font-bold text-gray-900">
                        @if($kpi && $kpi['total_monthly_sales'] !== null)
                            ${{ number_format($kpi['total_monthly_sales'] / 1000000, 1) }}M
                        @else
                            —
                        @endif
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="text-sm text-gray-500 mb-1">Mom Change</div>
                    @php
                        $momSales = $mom['total_monthly_sales'] ?? null;
                        $isUp = $momSales !== null && (float) $momSales > 0;
                        $isFlat = $momSales === null || (float) $momSales == 0;
                        $colorClass = $isFlat ? 'text-gray-500' : ($isUp ? 'text-green-500' : 'text-red-500');
                    @endphp
                    <div class="text-2xl font-bold {{ $colorClass }}">
                        @if($momSales !== null)
                            {{ number_format($momSales, 1) }}%
                        @else
                            —
                        @endif
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="text-sm text-gray-500 mb-1">Active Licenses</div>
                    <div class="text-2xl font-bold text-gray-900">
                        @if($kpi && isset($kpi['active_licenses']) && $kpi['active_licenses'] !== null)
                            {{ number_format($kpi['active_licenses']) }}
                        @else
                            —
                        @endif
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="text-sm text-gray-500 mb-1">Avg Transaction</div>
                    <div class="text-2xl font-bold text-gray-900">
                        @if($kpi && $kpi['avg_transaction_value'] !== null)
                            ${{ number_format($kpi['avg_transaction_value'], 2) }}
                        @else
                            —
                        @endif
                    </div>
                </div>
            </div>

            {{-- Trend Previews --}}
            <h3 class="text-lg font-bold text-gray-900 mb-4">Trend Previews</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <h4 class="font-semibold text-gray-900">Monthly Sales Trend</h4>
                    <p class="text-sm text-gray-500 mb-3">Last 12 months</p>
                    <div class="h-40">
                        <canvas id="chart-sales-preview" height="160"></canvas>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <h4 class="font-semibold text-gray-900">License Growth</h4>
                    <p class="text-sm text-gray-500 mb-3">Quarterly trend</p>
                    <div class="h-40">
                        <canvas id="chart-license-preview" height="160"></canvas>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <h4 class="font-semibold text-gray-900">Category Mix</h4>
                    <p class="text-sm text-gray-500 mb-3">
                        @if(!empty($categoryMixPreview['topLabel']) && $categoryMixPreview['topPct'] !== null)
                            Top: {{ $categoryMixPreview['topLabel'] }} ({{ number_format($categoryMixPreview['topPct'], 1) }}%)
                        @else
                            Top categories
                        @endif
                    </p>
                    <div class="h-40 flex items-center justify-center">
                        <canvas id="chart-category-preview" height="160"></canvas>
                    </div>
                </div>
            </div>

            {{-- Market Pulse History: all months with data, preview + View / Download --}}
            <h3 class="text-lg font-bold text-gray-900 mb-4">Market Pulse History</h3>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                @if($historyItems->isEmpty())
                    <div class="p-8 text-center text-gray-500">
                        <p>No market data yet. Executive summaries will appear here once data is available.</p>
                        <a href="{{ route('market-pulse') }}" class="mt-4 inline-block text-[#16a34a] hover:underline">View Market Pulse</a>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Month</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($historyItems as $item)
                                    <tr class="hover:bg-gray-50/50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                            {{ $item['month_label'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                            @php
                                                $monthParam = $item['month_param'] ?? '';
                                                $viewUrl = $monthParam ? (route('market-pulse') . '?month=' . rawurlencode($monthParam)) : route('market-pulse');
                                                $printUrl = $monthParam ? (route('market-pulse') . '?month=' . rawurlencode($monthParam) . '&print=1') : (route('market-pulse') . '?print=1');
                                            @endphp
                                            <span class="inline-flex items-center gap-2 flex-wrap justify-end">
                                                <a href="{{ $viewUrl }}" class="inline-flex items-center px-3 py-1.5 rounded-md text-sm font-medium text-[#16a34a] hover:bg-green-50">
                                                    View
                                                </a>
                                                @if($canDownloadFullPdf ?? true)
                                                    @if($item['snapshot'] && $item['snapshot']->published_at)
                                                        <a href="{{ route('snapshots.download', $item['snapshot']) }}" class="inline-flex items-center px-3 py-1.5 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100" target="_blank" rel="noopener">
                                                            Download PDF
                                                        </a>
                                                    @else
                                                        <a href="{{ $printUrl }}" class="inline-flex items-center px-3 py-1.5 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100" target="_blank" rel="noopener">
                                                            Download
                                                        </a>
                                                    @endif
                                                @else
                                                    <a href="{{ $printUrl }}" class="inline-flex items-center px-3 py-1.5 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100" target="_blank" rel="noopener" title="Summary view. Upgrade for polished PDF reports.">
                                                        Summary
                                                    </a>
                                                    <a href="{{ route('welcome') }}#pricing" class="inline-flex items-center px-3 py-1.5 rounded-md text-sm font-medium text-[#16a34a] hover:bg-green-50">
                                                        Upgrade for PDF
                                                    </a>
                                                @endif
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($historyItems->hasPages())
                        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                            {{ $historyItems->links('pagination.theme') }}
                        </div>
                    @endif
                @endif
            </div>

            <p class="mt-6 text-sm text-gray-500">Data is directly sourced from the Maryland Cannabis Administration (MCA). Updated monthly.</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        (function () {
            const salesCtx = document.getElementById('chart-sales-preview');
            if (salesCtx && typeof Chart !== 'undefined') {
                new Chart(salesCtx, {
                    type: 'line',
                    data: {
                        labels: @json($salesTrendPreview['labels'] ?? []),
                        datasets: [{
                            label: 'Sales',
                            data: @json($salesTrendPreview['values'] ?? []),
                            borderColor: '#16a34a',
                            backgroundColor: 'rgba(22, 163, 74, 0.1)',
                            fill: true,
                            tension: 0.3,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function (ctx) {
                                        const v = ctx.parsed?.y ?? ctx.raw ?? 0;
                                        const formatted = '$' + Number(v).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                        return 'Sales: ' + formatted;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: { display: true, ticks: { maxRotation: 45, maxTicksLimit: 6 } },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function (value) {
                                        const v = typeof value === 'number' ? value : (value ?? 0);
                                        if (v >= 1e6) return '$' + (v / 1e6).toFixed(0) + 'M';
                                        if (v >= 1e3) return '$' + (v / 1e3).toFixed(0) + 'K';
                                        return '$' + v;
                                    }
                                }
                            }
                        }
                    }
                });
            }

            const licenseCtx = document.getElementById('chart-license-preview');
            if (licenseCtx && typeof Chart !== 'undefined') {
                new Chart(licenseCtx, {
                    type: 'line',
                    data: {
                        labels: @json($licenseGrowthPreview['labels'] ?? []),
                        datasets: [{
                            label: 'Licenses',
                            data: @json($licenseGrowthPreview['values'] ?? []),
                            borderColor: '#2563eb',
                            backgroundColor: 'rgba(37, 99, 235, 0.1)',
                            fill: true,
                            tension: 0.3,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { display: true, ticks: { maxRotation: 45, maxTicksLimit: 6 } },
                            y: { beginAtZero: true }
                        }
                    }
                });
            }

            const catCtx = document.getElementById('chart-category-preview');
            if (catCtx && typeof Chart !== 'undefined') {
                const labels = @json($categoryMixPreview['labels'] ?? []);
                const values = @json($categoryMixPreview['values'] ?? []);
                const colors = ['#16a34a', '#2563eb', '#dc2626', '#f59e0b', '#8b5cf6', '#06b6d4'];
                new Chart(catCtx, {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: values,
                            backgroundColor: labels.map(function (_, i) { return colors[i % colors.length]; }),
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 10 } } },
                            tooltip: {
                                callbacks: {
                                    label: function (context) {
                                        const dollarVal = values[context.dataIndex] != null ? values[context.dataIndex] : (context.raw ?? 0);
                                        const total = values.reduce(function (a, b) { return a + b; }, 0);
                                        const pct = total ? ((Number(dollarVal) / total) * 100).toFixed(1) : '0.0';
                                        const formatted = '$' + Number(dollarVal).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                        return context.label + ': ' + formatted + ' (' + pct + '%)';
                                    }
                                }
                            }
                        },
                        cutout: '60%'
                    }
                });
            }
        })();
    </script>
</x-app-layout>
