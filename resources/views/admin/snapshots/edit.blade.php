<x-app-layout>
    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit snapshot – {{ $snapshot->month_date->format('F Y') }}</h2>
                <a href="{{ route('admin.snapshots.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Back to snapshots</a>
            </div>
            @if(session('info'))
                <div class="mb-4 rounded-lg bg-blue-50 p-4 text-sm text-blue-800">{{ session('info') }}</div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <p class="text-sm text-gray-500 mb-4">Month: <strong>{{ $snapshot->month_date->format('F Y') }}</strong>. This summary will appear on the Market Pulse page when published and when viewing this month.</p>
                <p class="mb-4">
                    <a href="{{ route('market-pulse', ['month' => $snapshot->month_date->format('Y-m')]) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 px-4 py-2 rounded-md bg-[#16a34a] text-white text-sm font-medium hover:bg-[#15803d]">
                        View on Market Pulse
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                    <span class="ml-2 text-sm text-gray-500">Opens /market-pulse?month={{ $snapshot->month_date->format('Y-m') }}</span>
                </p>

                <form method="POST" action="{{ route('admin.snapshots.update', $snapshot) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="executive_summary" value="Executive summary" />
                        <textarea
                            id="executive_summary"
                            name="executive_summary"
                            rows="8"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#16a34a] focus:ring-[#16a34a]"
                            placeholder="Enter or edit the executive summary for this month."
                        >{{ old('executive_summary', $snapshot->executive_summary) }}</textarea>
                        <x-input-error :messages="$errors->get('executive_summary')" class="mt-2" />
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <x-primary-button type="submit">Save</x-primary-button>
                        <button type="submit" name="publish" value="1" class="inline-flex items-center px-4 py-2 rounded-md bg-[#16a34a] text-white text-sm font-medium hover:bg-[#15803d]">
                            Save &amp; Publish
                        </button>
                        <a href="{{ route('admin.snapshots.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">Cancel</a>
                    </div>
                </form>

                @if($snapshot->isPublished())
                    <p class="mt-4 text-sm text-green-600">This snapshot is published. It will appear on the Market Pulse page for this month.</p>
                @else
                    <p class="mt-4 text-sm text-gray-500">This snapshot is a draft. Click "Save & Publish" to make the summary visible on the Market Pulse page.</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
