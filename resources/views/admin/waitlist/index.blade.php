<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Waitlist Signups</h2>
                <a href="{{ route('admin.waitlist.export', request()->query()) }}" class="inline-flex items-center px-4 py-2 rounded-md border border-gray-300 bg-white text-gray-700 text-sm font-medium hover:bg-gray-50">
                    Export to CSV
                </a>
            </div>

            {{-- Filters --}}
            <form method="get" action="{{ route('admin.waitlist.index') }}" class="mb-4 flex flex-wrap items-center gap-3">
                <label for="use_case" class="text-sm font-medium text-gray-700">Use case</label>
                <select id="use_case" name="use_case" class="rounded-lg border-gray-300 shadow-sm focus:border-[#16a34a] focus:ring-[#16a34a] text-sm">
                    <option value="">All</option>
                    @foreach($useCases as $value => $label)
                        <option value="{{ $value }}" {{ request('use_case') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <label for="persona_type" class="text-sm font-medium text-gray-700">Persona</label>
                <select id="persona_type" name="persona_type" class="rounded-lg border-gray-300 shadow-sm focus:border-[#16a34a] focus:ring-[#16a34a] text-sm">
                    <option value="">All</option>
                    @foreach($personaTypes as $value => $label)
                        <option value="{{ $value }}" {{ request('persona_type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <label for="operator_type" class="text-sm font-medium text-gray-700">Operator type</label>
                <select id="operator_type" name="operator_type" class="rounded-lg border-gray-300 shadow-sm focus:border-[#16a34a] focus:ring-[#16a34a] text-sm">
                    <option value="">All</option>
                    @foreach($operatorTypes as $value => $label)
                        <option value="{{ $value }}" {{ request('operator_type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-3 py-1.5 rounded-md bg-gray-100 text-gray-700 text-sm font-medium hover:bg-gray-200">Filter</button>
            </form>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                @if($signups->isEmpty())
                    <div class="p-6 text-gray-600">
                        <p>No waitlist signups yet.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Organization</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Use case</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Interests</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Source</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duplicate</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($signups as $signup)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $signup->email }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-600">{{ Str::limit($signup->organization, 30) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $signup->persona_type_label ?? '—' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $signup->operator_type_label ?? '—' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $useCases[$signup->use_case] ?? $signup->use_case }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-600">
                                            @if(is_array($signup->interests) && count($signup->interests) > 0)
                                                @php
                                                    $labels = array_map(fn($k) => $interests[$k] ?? $k, $signup->interests);
                                                @endphp
                                                {{ Str::limit(implode(', ', $labels), 40) }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-600 max-w-xs">{{ Str::limit($signup->notes, 50) ?: '—' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $signup->source_page ?: '—' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($signup->is_duplicate)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">Duplicate</span>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $signup->created_at->format('M j, Y g:i A') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-3 border-t border-gray-200">
                        {{ $signups->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
