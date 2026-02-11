<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="description" content="{{ $metaDescription ?? config('app.meta_description') }}">
        <meta name="robots" content="index, follow">

        <title>{{ isset($title) ? $title . ' | ' . config('app.name') : config('app.name') }}</title>

        <!-- Favicon -->
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
        <link rel="alternate icon" href="{{ asset('favicon.ico') }}">

        <!-- Open Graph / social -->
        <meta property="og:type" content="website">
        <meta property="og:title" content="{{ isset($title) ? $title . ' | ' . config('app.name') : config('app.name') }}">
        <meta property="og:description" content="{{ $metaDescription ?? config('app.meta_description') }}">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:image" content="{{ asset('favicon.svg') }}">
        <meta name="twitter:card" content="summary">
        <meta name="twitter:title" content="{{ isset($title) ? $title . ' | ' . config('app.name') : config('app.name') }}">
        <meta name="twitter:description" content="{{ $metaDescription ?? config('app.meta_description') }}">

        <!-- Fonts: preconnect first, then load async so they don't block render -->
        <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
        <link rel="preload" href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
        <noscript><link rel="stylesheet" href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600&display=swap"></noscript>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            body { font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif; }
        </style>
    </head>
    <body class="min-h-screen bg-white text-gray-800 antialiased flex flex-col">
        {{-- Header: logo + nav (different when auth vs guest) --}}
        <header class="border-b border-gray-200 bg-white" x-data="{ mobileMenuOpen: false }">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 py-3 sm:py-4 flex items-center justify-between gap-4">
                <x-market-pulse-logo class="min-w-0" />
                {{-- Desktop nav --}}
                <nav class="hidden md:flex items-center gap-5 lg:gap-6 shrink-0">
                    @auth
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">{{ __('Dashboard') }}</x-nav-link>
                        <x-nav-link :href="route('market-pulse')" :active="request()->routeIs('market-pulse')">Market Pulse</x-nav-link>
                        <x-nav-link :href="route('export.index')" :active="request()->routeIs('export.*')">Export Data</x-nav-link>
                        @if(Auth::user()->isSuperAdmin())
                            <x-nav-link :href="route('admin.index')" :active="request()->routeIs('admin.*')">Admin</x-nav-link>
                        @endif
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center gap-1.5 px-2 py-2 border border-transparent text-sm font-medium rounded-md text-gray-700 bg-white hover:text-gray-900 focus:outline-none transition">
                                    @php $displayName = explode(' ', trim(Auth::user()->name))[0] ?? Auth::user()->name; @endphp
                                    <span>{{ ucfirst($displayName) }}</span>
                                    <svg class="fill-current h-4 w-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link :href="route('profile.edit')">{{ __('Profile') }}</x-dropdown-link>
                                <form method="POST" action="{{ route('logout') }}">@csrf<x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">{{ __('Log Out') }}</x-dropdown-link></form>
                            </x-slot>
                        </x-dropdown>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-700 hover:text-gray-900 font-medium whitespace-nowrap py-2">Log in</a>
                        <a href="{{ route('register') }}" class="bg-[#16a34a] text-white px-5 py-2.5 rounded-md font-medium hover:bg-[#15803d] transition-colors whitespace-nowrap">Get Free Access</a>
                    @endauth
                </nav>
                {{-- Mobile: hamburger --}}
                <button type="button" @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2.5 -mr-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-[#16a34a] aria-label="Toggle menu">
                    <svg x-show="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <svg x-show="mobileMenuOpen" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            {{-- Mobile menu panel --}}
            <div x-show="mobileMenuOpen" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="md:hidden border-t border-gray-200 bg-gray-50">
                <nav class="max-w-6xl mx-auto px-4 py-4 flex flex-col gap-1">
                    @auth
                        <x-responsive-nav-link :href="route('dashboard')" @click="mobileMenuOpen = false" :active="request()->routeIs('dashboard')">{{ __('Dashboard') }}</x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('market-pulse')" @click="mobileMenuOpen = false" :active="request()->routeIs('market-pulse')">Market Pulse</x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('export.index')" @click="mobileMenuOpen = false" :active="request()->routeIs('export.*')">Export Data</x-responsive-nav-link>
                        @if(Auth::user()->isSuperAdmin())
                            <x-responsive-nav-link :href="route('admin.index')" @click="mobileMenuOpen = false" :active="request()->routeIs('admin.*')">Admin</x-responsive-nav-link>
                        @endif
                        <div class="pt-4 mt-4 border-t border-gray-200">
                            <div class="font-medium text-sm text-gray-800 mb-2">{{ Auth::user()->name }}</div>
                            <a href="{{ route('profile.edit') }}" @click="mobileMenuOpen = false" class="block py-2 text-gray-600 hover:text-gray-900 font-medium">Profile</a>
                            <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="block w-full text-left py-2 text-gray-600 hover:text-gray-900 font-medium">Log Out</button></form>
                        </div>
                    @else
                        <a href="{{ route('login') }}" @click="mobileMenuOpen = false" class="block py-3.5 px-4 rounded-md text-gray-700 font-medium hover:bg-white hover:text-gray-900 active:bg-gray-100 transition-colors">Log in</a>
                        <a href="{{ route('register') }}" @click="mobileMenuOpen = false" class="block py-3.5 px-4 rounded-md bg-[#16a34a] text-white font-medium text-center hover:bg-[#15803d] active:bg-[#15803d] transition-colors">Get Started</a>
                    @endauth
                </nav>
            </div>
        </header>

        {{-- Main: default slot = centered (auth forms); named 'main' = full width (landing) --}}
        <main class="flex-1">
            @isset($main)
                {{ $main }}
            @else
                <div class="flex items-center justify-center py-12 px-6 bg-[#F8F9FA] min-h-[calc(100vh-8rem)]">
                    <div class="w-full max-w-lg rounded-xl bg-white p-8 md:p-10 shadow-md border border-gray-100">
                        {{ $slot }}
                    </div>
                </div>
            @endisset
        </main>

        {{-- Footer: logo + tagline + copyright --}}
        <footer class="border-t border-gray-200 bg-white py-6">
            <div class="max-w-6xl mx-auto px-6 grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                <x-market-pulse-logo />
                <span class="text-sm text-gray-500 text-center">Maryland-first cannabis market intelligence</span>
                <span class="text-sm text-gray-500 text-left md:text-right">© 2026 TerpInsights. All rights reserved.</span>
            </div>
        </footer>
    </body>
</html>
