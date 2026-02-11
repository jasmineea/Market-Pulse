<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="description" content="{{ $metaDescription ?? config('app.meta_description') }}">
        <meta name="robots" content="index, follow">

        @php $appName = (config('app.name') === 'Laravel') ? 'TerpInsights' : config('app.name'); @endphp
        <title>{{ isset($title) ? $title . ' | ' . $appName : $appName }}</title>

        <!-- Favicon -->
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
        <link rel="alternate icon" href="{{ asset('favicon.ico') }}">

        <!-- Open Graph / social -->
        <meta property="og:type" content="website">
        <meta property="og:title" content="{{ isset($title) ? $title . ' | ' . $appName : $appName }}">
        <meta property="og:description" content="{{ $metaDescription ?? config('app.meta_description') }}">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:image" content="{{ asset('favicon.svg') }}">
        <meta name="twitter:card" content="summary">
        <meta name="twitter:title" content="{{ isset($title) ? $title . ' | ' . $appName : $appName }}">
        <meta name="twitter:description" content="{{ $metaDescription ?? config('app.meta_description') }}">

        <!-- Font Awesome (icons for KPIs, etc.) -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer">

        <!-- Fonts: preconnect first, then load async so they don't block render -->
        <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
        <link rel="preload" href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
        <noscript><link rel="stylesheet" href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap"></noscript>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            @media print {
                .no-print { display: none !important; }
                body {
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                    background: #fff !important;
                }
                .min-h-screen { min-height: auto !important; background: #fff !important; }
                main { background: #fff !important; padding-top: 0 !important; }
                /* Keep chart/section cards from breaking across pages */
                .print-keep-together { page-break-inside: avoid; break-inside: avoid; }
            }
        </style>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <!-- Page Heading (hidden in print mode for cleaner PDF) -->
            @if(!($printMode ?? false))
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
