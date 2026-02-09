<x-app-layout>
    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create snapshot</h2>
                <a href="{{ route('admin.snapshots.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Back to snapshots</a>
            </div>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('admin.snapshots.store') }}" class="space-y-6">
                    @csrf

                    <div>
                        <x-input-label for="month" value="Month" />
                        <input
                            id="month"
                            type="month"
                            name="month"
                            value="{{ old('month', now()->format('Y-m')) }}"
                            required
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#16a34a] focus:ring-[#16a34a]"
                        />
                        <x-input-error :messages="$errors->get('month')" class="mt-2" />
                        <p class="mt-1 text-sm text-gray-500">Pick the year and month for this snapshot (e.g. December 2025).</p>
                    </div>

                    <div>
                        <x-input-label for="executive_summary" value="Executive summary (optional)" />
                        <textarea
                            id="executive_summary"
                            name="executive_summary"
                            rows="6"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#16a34a] focus:ring-[#16a34a]"
                            placeholder="Enter the executive summary for this month. You can edit and publish it after creating."
                        >{{ old('executive_summary') }}</textarea>
                        <x-input-error :messages="$errors->get('executive_summary')" class="mt-2" />
                    </div>

                    <div class="flex gap-3">
                        <x-primary-button type="submit">Create snapshot</x-primary-button>
                        <a href="{{ route('admin.snapshots.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
