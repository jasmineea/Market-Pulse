@if(!($printMode ?? false))
<nav x-data="{ open: false }" class="bg-white border-b border-gray-200 no-print relative z-20">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <!-- Logo: same as welcome page (green rounded square + trend line + TerpInsights) -->
                <x-market-pulse-logo :href="url('/')" class="shrink-0" />

                <!-- Navigation Links: AI Lab (core brand), Dashboard, Market Pulse, Export Data, Admin (super admin only) -->
                <div class="hidden space-x-8 sm:flex sm:ms-10">
                    <x-nav-link :href="route('ai-lab')" :active="request()->routeIs('ai-lab')">
                        AI Lab
                    </x-nav-link>
                    @auth
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                            {{ __('Dashboard') }}
                        </x-nav-link>
                    @endauth
                    <x-nav-link :href="route('market-pulse')" :active="request()->routeIs('market-pulse')">
                        Market Pulse
                    </x-nav-link>
                    <x-nav-link :href="route('export.index')" :active="request()->routeIs('export.*')">
                        Export Data
                    </x-nav-link>
                    @auth
                        @if(Auth::user()->isSuperAdmin())
                            <x-nav-link :href="route('admin.index')" :active="request()->routeIs('admin.*')">
                                Admin
                            </x-nav-link>
                        @endif
                    @endauth
                </div>
            </div>

            <!-- Right side: user dropdown or guest links -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                @auth
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            @php
                                $displayName = explode(' ', trim(Auth::user()->name))[0] ?? Auth::user()->name;
                            @endphp
                            <span>{{ ucfirst($displayName) }}</span>
                            <svg class="fill-current h-4 w-4 text-gray-500 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Profile') }}
                            </x-dropdown-link>

                            <form method="POST" action="{{ route('logout') }}" class="[&_button]:block [&_button]:w-full [&_button]:text-start">
                                @csrf
                                <button type="submit" class="px-4 py-2 text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out w-full text-left">
                                    {{ __('Log Out') }}
                                </button>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-medium text-gray-500 hover:text-gray-700 ms-4">Log in</a>
                    <a href="{{ route('register') }}" class="ms-4 inline-flex items-center px-4 py-2 rounded-md bg-[#16a34a] text-white text-sm font-medium hover:bg-[#15803d]">Get Started</a>
                @endauth
            </div>

            <!-- Hamburger: 44px min touch target for mobile, stop propagation so tap always toggles -->
            <div class="-me-2 flex items-center sm:hidden">
                <button type="button"
                    @click.stop="open = !open"
                    :aria-expanded="open"
                    aria-label="Toggle menu"
                    class="inline-flex items-center justify-center min-w-[44px] min-h-[44px] p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 focus:ring-2 focus:ring-[#16a34a] transition duration-150 ease-in-out"
                    style="touch-action: manipulation;">
                    <svg class="h-6 w-6 pointer-events-none" stroke="currentColor" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                        <path x-show="!open" x-transition:enter="transition ease-out" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path x-show="open" x-transition:enter="transition ease-out" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu (visible on mobile when open) -->
    <div x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="sm:hidden border-t border-gray-200 bg-white shadow-sm"
        style="display: none;">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('ai-lab')" :active="request()->routeIs('ai-lab')">
                AI Lab
            </x-responsive-nav-link>
            @auth
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    {{ __('Dashboard') }}
                </x-responsive-nav-link>
            @endauth
            <x-responsive-nav-link :href="route('market-pulse')" :active="request()->routeIs('market-pulse')">
                Market Pulse
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('export.index')" :active="request()->routeIs('export.*')">
                Export Data
            </x-responsive-nav-link>
            @auth
                @if(Auth::user()->isSuperAdmin())
                    <x-responsive-nav-link :href="route('admin.index')" :active="request()->routeIs('admin.*')">
                        Admin
                    </x-responsive-nav-link>
                @endif
            @endauth
        </div>

        @auth
            <!-- Responsive Settings Options (logged in only) -->
            <div class="pt-4 pb-1 border-t border-gray-200">
                <div class="px-4">
                    <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                </div>

                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('profile.edit')">
                        {{ __('Profile') }}
                    </x-responsive-nav-link>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <x-responsive-nav-link :href="route('logout')"
                                onclick="event.preventDefault();
                                            this.closest('form').submit();">
                            {{ __('Log Out') }}
                        </x-responsive-nav-link>
                    </form>
                </div>
            </div>
        @else
            <div class="pt-4 pb-1 border-t border-gray-200 space-y-2 px-4">
                <a href="{{ route('login') }}" class="block text-gray-600 hover:text-gray-900 font-medium">Log in</a>
                <a href="{{ route('register') }}" class="block text-[#16a34a] hover:text-[#15803d] font-medium">Get Free Access</a>
            </div>
        @endauth
    </div>
</nav>
@endif
