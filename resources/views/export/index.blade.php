<x-app-layout>
    <div class="py-8 px-4 sm:px-6 lg:px-8 bg-[#F8F9FA] min-h-screen">
        <div class="max-w-2xl mx-auto">
            @if(session('error'))
                <div class="mb-6 rounded-lg bg-red-50 border border-red-200 p-4 text-sm text-red-800">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="mb-6 rounded-lg bg-red-50 border border-red-200 p-4 text-sm text-red-800">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if(session('success'))
                <div class="mb-6 rounded-lg bg-green-50 border border-green-200 p-4 text-sm text-green-800">{{ session('success') }}</div>
            @endif

            {{-- Select Report Month --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Select Report Month</h3>
                <form id="export-form" method="post" action="{{ route('export.download') }}" class="space-y-6">
                    @csrf
                    <div>
                        <label for="month" class="block text-sm font-medium text-gray-700 mb-1">Month</label>
                        <select id="month" name="month" required class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#16a34a] focus:ring-[#16a34a]">
                            @foreach($months as $m)
                                <option value="{{ $m['value'] }}" {{ $loop->first ? 'selected' : '' }}>{{ $m['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>

            @if(!($canDownloadFullPdf ?? true))
            <div class="mb-6 rounded-lg bg-amber-50 border border-amber-200 p-4 text-sm text-amber-800">
                Starter: Aggregate and category-level exports only. Unlock full exports with Professional.
            </div>
            @endif

            {{-- Available Exports --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Available Exports</h3>
                <ul class="space-y-3">
                    <li class="flex items-start gap-3 p-3 rounded-lg border border-gray-100 hover:bg-gray-50/50">
                        <input type="checkbox" id="export-sales" name="exports[]" value="sales" form="export-form" class="mt-1 rounded border-gray-300 text-[#16a34a] focus:ring-[#16a34a]">
                        <label for="export-sales" class="flex-1 cursor-pointer">
                            <span class="font-medium text-gray-900">Monthly Sales Summary (CSV)</span>
                            <span class="block text-sm text-gray-500">Sales data and KPIs for the selected month.</span>
                        </label>
                    </li>
                    <li class="flex items-start gap-3 p-3 rounded-lg border border-gray-100 hover:bg-gray-50/50">
                        <input type="checkbox" id="export-category" name="exports[]" value="category" form="export-form" class="mt-1 rounded border-gray-300 text-[#16a34a] focus:ring-[#16a34a]">
                        <label for="export-category" class="flex-1 cursor-pointer">
                            <span class="font-medium text-gray-900">Category Revenue Breakdown (CSV)</span>
                            <span class="block text-sm text-gray-500">Revenue by category.</span>
                        </label>
                    </li>
                    <li class="flex items-start gap-3 p-3 rounded-lg border border-gray-100 hover:bg-gray-50/50">
                        <input type="checkbox" id="export-licenses" name="exports[]" value="licenses" form="export-form" class="mt-1 rounded border-gray-300 text-[#16a34a] focus:ring-[#16a34a]">
                        <label for="export-licenses" class="flex-1 cursor-pointer">
                            <span class="font-medium text-gray-900">License Counts by Type (CSV)</span>
                            <span class="block text-sm text-gray-500">Active licenses breakdown.</span>
                        </label>
                    </li>
                    <li class="flex items-start gap-3 p-3 rounded-lg border border-gray-100 {{ ($canExportRegional ?? true) ? 'hover:bg-gray-50/50' : 'bg-gray-50/50' }}">
                        @if($canExportRegional ?? true)
                        <input type="checkbox" id="export-regional" name="exports[]" value="regional" form="export-form" class="mt-1 rounded border-gray-300 text-[#16a34a] focus:ring-[#16a34a]">
                        <label for="export-regional" class="flex-1 cursor-pointer">
                            <span class="font-medium text-gray-900">Dispensary Distribution by County (CSV)</span>
                            <span class="block text-sm text-gray-500">Dispensary locations by region.</span>
                        </label>
                        @else
                        <span class="mt-1 text-gray-400" title="County-level exports are available on the Professional plan">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </span>
                        <label class="flex-1 cursor-not-allowed opacity-75">
                            <span class="font-medium text-gray-900">Dispensary Distribution by County (CSV)</span>
                            <span class="block text-sm text-gray-500">County-level data — Pro only. <a href="{{ route('welcome') }}#pricing" class="text-[#16a34a] hover:underline">Upgrade</a></span>
                        </label>
                        @endif
                    </li>
                    <li class="flex items-start gap-3 p-3 rounded-lg border border-gray-100 bg-gray-50/50">
                        <span class="mt-1 text-gray-400" title="Use Download PDF button below">—</span>
                        <div class="flex-1">
                            <span class="font-medium text-gray-900">Market Pulse PDF</span>
                            <span class="block text-sm text-gray-500">One-click PDF report for the selected month (executive summary, KPIs, and data tables). Use the Download PDF button below.</span>
                        </div>
                    </li>
                </ul>
            </div>

            {{-- Export Actions --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Export Actions</h3>
                <div class="flex flex-wrap gap-3">
                    <button type="submit" form="export-form" id="download-csv-btn" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-md bg-[#16a34a] text-white text-sm font-medium hover:bg-[#15803d] disabled:opacity-50 disabled:cursor-not-allowed" title="Select at least one CSV export above">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        <span id="download-csv-label">Download Selected CSVs</span>
                    </button>
                    @if($canDownloadFullPdf ?? true)
                    <a href="{{ route('export.market-pulse-pdf', ['month' => $months[0]['value'] ?? now()->format('Y-m')]) }}" id="download-pdf-link" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-md bg-[#16a34a] text-white text-sm font-medium hover:bg-[#15803d]" title="Download Market Pulse report as PDF for the selected month">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Download PDF
                    </a>
                    @else
                    <span class="inline-flex items-center gap-2 px-4 py-2.5 rounded-md border border-gray-200 bg-gray-50 text-gray-400 text-sm font-medium cursor-not-allowed" title="Full PDF reports are available on the Professional plan">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        Market Pulse PDF (Pro)
                    </span>
                    @endif
                    <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'waitlist' }))" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-md border border-gray-300 bg-white text-gray-700 text-sm font-medium hover:bg-gray-50 hover:border-[#16a34a] transition-colors" title="Join waitlist for Pro / Enterprise">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                        Join Waitlist
                    </button>
                </div>
            </div>

            <p class="text-sm text-gray-500 mb-2">Export Note: These exports are for reports, presentations, and policy briefings. Data reflects the selected report month.</p>
            <p class="text-sm text-gray-500">Data is directly sourced from the Maryland Cannabis Administration (MCA). Updated monthly.</p>
        </div>

        {{-- Waitlist modal (opened from "Join Waitlist" button) --}}
        <x-waitlist-modal source-page="export" />
    </div>

    <script>
        (function () {
            var form = document.getElementById('export-form');
            var month = document.getElementById('month');
            var label = document.getElementById('download-csv-label');
            var csvBtn = document.getElementById('download-csv-btn');
            var pdfLink = document.getElementById('download-pdf-link');
            var pdfBaseUrl = '{{ route("export.market-pulse-pdf") }}';

            function updatePdfLink() {
                if (!pdfLink) return;
                var m = month ? month.value : '';
                pdfLink.href = pdfBaseUrl + (pdfBaseUrl.indexOf('?') !== -1 ? '&' : '?') + 'month=' + encodeURIComponent(m);
            }
            function getCheckedCount() {
                return document.querySelectorAll('input[name="exports[]"]:checked').length;
            }
            function updateExportState() {
                var count = getCheckedCount();
                if (label) label.textContent = count ? 'Download Selected CSVs (' + count + ')' : 'Download Selected CSVs';
                if (csvBtn) csvBtn.disabled = count === 0;
            }
            if (month) month.addEventListener('change', updatePdfLink);
            document.querySelectorAll('input[name="exports[]"]').forEach(function (el) {
                el.addEventListener('change', updateExportState);
            });
            if (form) {
                form.addEventListener('submit', function (e) {
                    if (getCheckedCount() === 0) {
                        e.preventDefault();
                        alert('Please select at least one export.');
                    }
                });
            }
            updatePdfLink();
            updateExportState();
        })();
    </script>
</x-app-layout>
