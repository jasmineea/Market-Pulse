<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Market Pulse Snapshots</h2>
                <a href="{{ route('admin.snapshots.create') }}" class="inline-flex items-center px-4 py-2 rounded-md bg-[#16a34a] text-white text-sm font-medium hover:bg-[#15803d]">
                    Create snapshot
                </a>
            </div>
            @if(session('success'))
                <div class="mb-4 rounded-lg bg-green-50 p-4 text-sm text-green-800">{{ session('success') }}</div>
            @endif
            @if(session('info'))
                <div class="mb-4 rounded-lg bg-blue-50 p-4 text-sm text-blue-800">{{ session('info') }}</div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                @if($snapshots->isEmpty())
                    <div class="p-6 text-gray-600">
                        <p>No snapshots yet. Create one to save and publish an executive summary for a month.</p>
                        <a href="{{ route('admin.snapshots.create') }}" class="mt-4 inline-block text-[#16a34a] hover:underline">Create snapshot</a>
                    </div>
                @else
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Month</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($snapshots as $snapshot)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $snapshot->month_date->format('F Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        @if($snapshot->isPublished())
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Published</span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Draft</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                        <a href="{{ route('admin.snapshots.edit', $snapshot) }}" class="text-[#16a34a] hover:underline">Edit</a>
                                        <span class="text-gray-300 mx-1">|</span>
                                        <a href="{{ route('market-pulse', ['month' => $snapshot->month_date->format('Y-m')]) }}" class="text-gray-600 hover:underline" target="_blank" rel="noopener">View</a>
                                        <span class="text-gray-300 mx-1">|</span>
                                        <form action="{{ route('admin.snapshots.destroy', $snapshot) }}" method="POST" class="inline" onsubmit="return confirm('Delete this snapshot? That month will show the auto-generated executive summary instead.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
