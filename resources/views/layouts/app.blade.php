<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @if(config('services.google_tag_manager_id'))
        <!-- Google Tag Manager -->
        <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','{{ config('services.google_tag_manager_id') }}');</script>
        <!-- End Google Tag Manager -->
        @endif
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

        <!-- Google Analytics (gtag.js) -->
        @if(config('services.google_analytics_id'))
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('services.google_analytics_id') }}"></script>
        <script>
          window.dataLayer = window.dataLayer || [];
          function gtag(){dataLayer.push(arguments);}
          gtag('js', new Date());
          gtag('config', '{{ config('services.google_analytics_id') }}');
        </script>
        @endif

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
        @if(config('services.google_tag_manager_id'))
        <!-- Google Tag Manager (noscript) -->
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ config('services.google_tag_manager_id') }}"
        height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
        <!-- End Google Tag Manager (noscript) -->
        @endif
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
