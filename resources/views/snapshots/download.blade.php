<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Market Pulse – {{ $snapshot->month_date->format('F Y') }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body class="font-sans antialiased bg-white text-gray-900">
    <div class="max-w-3xl mx-auto py-8 px-6">
        {{-- Print / Save as PDF hint (hidden when printing) --}}
        <div class="no-print mb-6 flex items-center justify-between rounded-lg bg-gray-100 p-4">
            <p class="text-sm text-gray-600">Use your browser’s <strong>Print</strong> (or Ctrl+P / Cmd+P) and choose <strong>Save as PDF</strong> to download this snapshot.</p>
            <a href="{{ route('market-pulse', ['month' => $snapshot->month_date->format('Y-m')]) }}" class="text-sm font-medium text-[#16a34a] hover:underline">View full report</a>
        </div>

        <header class="border-b border-gray-200 pb-4 mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Market Pulse – {{ $snapshot->month_date->format('F Y') }}</h1>
            <p class="text-gray-500 mt-1">Maryland Cannabis Market Intelligence</p>
            <p class="text-sm text-gray-500 mt-1">Data sourced from the Maryland Cannabis Administration (MCA).</p>
            <p class="text-sm text-gray-400 mt-2">Snapshot generated {{ $snapshot->published_at?->format('M j, Y') ?? $snapshot->updated_at->format('M j, Y') }}</p>
        </header>

        <section>
            <h2 class="text-lg font-bold text-gray-900 mb-3">Executive Summary</h2>
            <div class="text-gray-700 leading-relaxed whitespace-pre-wrap">{{ $snapshot->executive_summary ?? 'No summary.' }}</div>
        </section>

        <p class="mt-8 text-sm text-gray-500">Data is directly sourced from the Maryland Cannabis Administration (MCA). Updated monthly.</p>
    </div>
</body>
</html>
