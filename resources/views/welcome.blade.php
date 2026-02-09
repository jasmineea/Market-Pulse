<x-guest-layout>
    <x-slot:main>
        {{-- Hero --}}
        <section class="py-16 md:py-24 px-6">
            <div class="max-w-4xl mx-auto text-center">
                <span class="inline-block px-4 py-1.5 rounded-full bg-gray-100 text-gray-600 text-sm font-medium mb-6">Beta Access — Free During Early Access</span>
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 leading-tight mb-6">
                    Maryland cannabis market intelligence, <span class="text-[#16a34a]">simplified</span>
                </h1>
                <p class="text-lg md:text-xl text-gray-600 max-w-2xl mx-auto mb-10">
                Clear, monthly insights on Maryland’s cannabis market — designed for policymakers, researchers, journalists, and operators who need answers, not spreadsheets.
                </p>
                <p class="text-sm text-gray-500 max-w-2xl mx-auto mb-10">Powered by official Maryland Cannabis Administration (MCA) data and transparent market analysis.</p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('register') }}" class="inline-flex justify-center items-center px-6 py-3.5 rounded-md bg-[#16a34a] text-white font-medium hover:bg-[#15803d] transition-colors">Get Free Market Access</a>
                    <a href="{{ route('login') }}" class="inline-flex justify-center items-center px-6 py-3.5 rounded-md border-2 border-gray-300 text-gray-800 font-medium hover:border-gray-400 hover:bg-gray-50 transition-colors">Log in</a>
                </div>
            </div>
        </section>

        {{-- Features --}}
        <section class="py-16 md:py-24 px-6 bg-gray-50">
            <div class="max-w-6xl mx-auto">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 text-center mb-4">Everything you need, nothing you don't</h2>
                <p class="text-gray-600 text-center max-w-2xl mx-auto mb-12">We cut through the noise to deliver a clear, monthly view of how Maryland’s market is evolving — what changed, why it matters, and what to watch next.</p>
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach([
                        ['title' => 'Monthly Sales Trends', 'desc' => "Understand how Maryland’s cannabis market is trending month over month with clear, contextual sales insights.", 'icon' => 'chart'],
                        ['title' => 'License Analytics', 'desc' => 'Track active dispensary, cultivator, processor, and ancillary licenses across the state..', 'icon' => 'bar'],
                        ['title' => 'Category Breakdown', 'desc' => 'See how revenue is distributed across product categories, including flower, vapes, edibles, and concentrates', 'icon' => 'pie'],
                        ['title' => 'Regional Insights', 'desc' => 'Explore how different Maryland regions and counties are participating in the cannabis market', 'icon' => 'map'],
                        ['title' => 'Easy Export', 'desc' => 'Download charts and datasets for reports, presentations, research, and policy briefings', 'icon' => 'download'],
                        ['title' => 'Trusted Data', 'desc' => 'All insights are sourced from publicly available Maryland Cannabis Administration data and updated monthly', 'icon' => 'shield'],
                    ] as $feature)
                        <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm hover:shadow-md transition-shadow">
                            <div class="w-10 h-10 rounded-lg bg-[#16a34a]/10 flex items-center justify-center mb-4 text-[#16a34a]">
                                @if($feature['icon'] === 'chart')
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
                                @elseif($feature['icon'] === 'bar')
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                @elseif($feature['icon'] === 'pie')
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                @elseif($feature['icon'] === 'map')
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                @elseif($feature['icon'] === 'download')
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                @else
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                @endif
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $feature['title'] }}</h3>
                            <p class="text-gray-600 text-sm leading-relaxed">{{ $feature['desc'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- Pricing --}}
        <section id="pricing" class="py-16 md:py-24 px-6">
            <div class="max-w-6xl mx-auto">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 text-center mb-4">Simple, transparent pricing</h2>
                <p class="text-gray-600 text-center max-w-2xl mx-auto mb-12">Start free during our beta period. Paid tiers coming soon with additional features.</p>
                <div class="grid md:grid-cols-3 gap-8">
                    {{-- Starter --}}
                    <div class="bg-white rounded-lg border border-gray-200 p-8 shadow-sm flex flex-col">
                        <h3 class="text-lg font-semibold text-gray-900 mb-1">Starter</h3>
                        <p class="text-3xl font-bold text-gray-900 mb-1">Free <span class="text-base font-normal text-gray-500">during beta</span></p>
                        <ul class="space-y-3 mt-6 mb-8 flex-1 text-gray-600 text-sm">
                            <li class="flex items-center gap-2"><span class="text-[#16a34a]">✓</span> Monthly market summary</li>
                            <li class="flex items-center gap-2"><span class="text-[#16a34a]">✓</span> 4 key charts</li>
                            <li class="flex items-center gap-2"><span class="text-[#16a34a]">✓</span> CSV data export</li>
                        </ul>
                        <a href="{{ route('register') }}" class="block text-center py-3 rounded-md border-2 border-[#16a34a] text-[#16a34a] font-medium hover:bg-[#16a34a]/5 transition-colors">Get Started</a>
                    </div>
                    {{-- Professional --}}
                    <div class="bg-white rounded-lg border border-gray-200 p-8 shadow-sm flex flex-col relative">
                        <span class="absolute top-4 right-4 px-3 py-1 rounded-full bg-[#16a34a] text-white text-xs font-medium">Coming Soon</span>
                        <h3 class="text-lg font-semibold text-gray-900 mb-1">Professional</h3>
                        <p class="text-3xl font-bold text-gray-900 mb-1">$49 <span class="text-base font-normal text-gray-500">/month</span></p>
                        <ul class="space-y-3 mt-6 mb-8 flex-1 text-gray-600 text-sm">
                            <li class="flex items-center gap-2"><span class="text-[#16a34a]">✓</span> Everything in Starter</li>
                            <li class="flex items-center gap-2"><span class="text-[#16a34a]">✓</span> Historical data access</li>
                            <li class="flex items-center gap-2"><span class="text-[#16a34a]">✓</span> PDF reports</li>
                            <li class="flex items-center gap-2"><span class="text-[#16a34a]">✓</span> Priority support</li>
                        </ul>
                        <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'waitlist' }))" class="w-full block text-center py-3 rounded-md bg-[#16a34a] text-white font-medium hover:bg-[#15803d] transition-colors cursor-pointer">Join Waitlist</button>
                    </div>
                    {{-- Enterprise --}}
                    <div class="bg-white rounded-lg border border-gray-200 p-8 shadow-sm flex flex-col relative">
                        <span class="absolute top-4 right-4 px-3 py-1 rounded-full bg-[#16a34a] text-white text-xs font-medium">Coming Soon</span>
                        <h3 class="text-lg font-semibold text-gray-900 mb-1">Enterprise</h3>
                        <p class="text-3xl font-bold text-gray-900 mb-1">Custom</p>
                        <ul class="space-y-3 mt-6 mb-8 flex-1 text-gray-600 text-sm">
                            <li class="flex items-center gap-2"><span class="text-[#16a34a]">✓</span> Everything in Pro</li>
                            <li class="flex items-center gap-2"><span class="text-[#16a34a]">✓</span> Custom analysis</li>
                            <li class="flex items-center gap-2"><span class="text-[#16a34a]">✓</span> API access</li>
                            <li class="flex items-center gap-2"><span class="text-[#16a34a]">✓</span> Dedicated support</li>
                        </ul>
                        <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'waitlist' }))" class="w-full block text-center py-3 rounded-md border-2 border-gray-300 text-gray-700 font-medium hover:border-gray-400 hover:bg-gray-50 transition-colors cursor-pointer">Join Waitlist</button>
                    </div>
                </div>
            </div>
        </section>

        {{-- Waitlist modal (opened from Pricing "Join Waitlist" buttons) --}}
        <x-waitlist-modal source-page="pricing" />
    </x-slot:main>
</x-guest-layout>
