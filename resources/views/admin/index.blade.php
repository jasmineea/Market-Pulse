<x-app-layout>
    <div class="py-8 px-4 sm:px-6 lg:px-8 bg-[#F8F9FA] min-h-screen">
        <div class="max-w-7xl mx-auto">
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Admin Dashboard</h2>
            <p class="text-gray-600 mb-8">Manage users, Market Pulse snapshots, and waitlist signups.</p>

            {{-- Stats cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-10">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm text-gray-500 mb-1">Total Users</div>
                            <div class="text-2xl font-bold text-gray-900">{{ number_format($userCount ?? 0) }}</div>
                        </div>
                        <div class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm text-gray-500 mb-1">Market Pulse Snapshots</div>
                            <div class="text-2xl font-bold text-gray-900">{{ number_format($snapshotCount ?? 0) }}</div>
                        </div>
                        <div class="w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm text-gray-500 mb-1">Published Snapshots</div>
                            <div class="text-2xl font-bold text-gray-900">{{ number_format($publishedSnapshotCount ?? 0) }}</div>
                        </div>
                        <div class="w-12 h-12 rounded-lg bg-emerald-100 flex items-center justify-center">
                            <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm text-gray-500 mb-1">Waitlist Signups</div>
                            <div class="text-2xl font-bold text-gray-900">{{ number_format($waitlistCount ?? 0) }}</div>
                        </div>
                        <div class="w-12 h-12 rounded-lg bg-amber-100 flex items-center justify-center">
                            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Quick actions --}}
            <h3 class="text-lg font-bold text-gray-900 mb-4">Quick Actions</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
                <a href="{{ route('admin.users.index') }}" class="block bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md hover:border-[#16a34a]/30 transition-all group">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center shrink-0 group-hover:bg-blue-200 transition-colors">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900 group-hover:text-[#16a34a] transition-colors">Manage Users</h4>
                            <p class="text-sm text-gray-500 mt-1">Create, edit, and delete user accounts. Assign roles.</p>
                            <span class="inline-flex items-center gap-1 mt-2 text-sm font-medium text-[#16a34a] group-hover:underline">
                                Go to users →
                            </span>
                        </div>
                    </div>
                </a>
                <a href="{{ route('admin.snapshots.index') }}" class="block bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md hover:border-[#16a34a]/30 transition-all group">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center shrink-0 group-hover:bg-green-200 transition-colors">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900 group-hover:text-[#16a34a] transition-colors">Market Pulse Snapshots</h4>
                            <p class="text-sm text-gray-500 mt-1">Create, edit, and publish executive summaries for each month.</p>
                            <span class="inline-flex items-center gap-1 mt-2 text-sm font-medium text-[#16a34a] group-hover:underline">
                                Manage snapshots →
                            </span>
                        </div>
                    </div>
                </a>
                <a href="{{ route('admin.waitlist.index') }}" class="block bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md hover:border-[#16a34a]/30 transition-all group">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-lg bg-amber-100 flex items-center justify-center shrink-0 group-hover:bg-amber-200 transition-colors">
                            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900 group-hover:text-[#16a34a] transition-colors">Waitlist Signups</h4>
                            <p class="text-sm text-gray-500 mt-1">View, filter, and export Professional/Enterprise waitlist submissions.</p>
                            <span class="inline-flex items-center gap-1 mt-2 text-sm font-medium text-[#16a34a] group-hover:underline">
                                View waitlist →
                            </span>
                        </div>
                    </div>
                </a>
                <a href="{{ route('admin.outreach.index') }}" class="block bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md hover:border-[#16a34a]/30 transition-all group">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-lg bg-indigo-100 flex items-center justify-center shrink-0 group-hover:bg-indigo-200 transition-colors">
                            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900 group-hover:text-[#16a34a] transition-colors">Outreach CRM</h4>
                            <p class="text-sm text-gray-500 mt-1">Track LinkedIn prospects, log conversations, and follow up.</p>
                            @if(($followUpsDueCount ?? 0) > 0)
                                <a href="{{ route('admin.outreach.index', ['follow_ups_only' => 1]) }}" class="inline-block mt-2 text-sm font-medium text-amber-600 hover:underline">{{ number_format($followUpsDueCount) }} follow-ups due →</a>
                            @else
                                <span class="inline-flex items-center gap-1 mt-2 text-sm font-medium text-[#16a34a] group-hover:underline">
                                    Go to outreach →
                                </span>
                            @endif
                        </div>
                    </div>
                </a>
            </div>

            <p class="text-sm text-gray-500">Data is directly sourced from the Maryland Cannabis Administration (MCA). Updated monthly.</p>
        </div>
    </div>
</x-app-layout>
