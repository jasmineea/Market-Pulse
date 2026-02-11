<x-app-layout>
    <div class="py-8 px-4 sm:px-6 lg:px-8 bg-[#F8F9FA] min-h-screen">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Outreach CRM</h2>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('admin.outreach.export', request()->query()) }}" class="inline-flex items-center px-4 py-2 rounded-md border border-gray-300 bg-white text-gray-700 text-sm font-medium hover:bg-gray-50">
                        Export CSV
                    </a>
                    <a href="{{ route('admin.outreach.board') }}" class="inline-flex items-center px-4 py-2 rounded-md border border-gray-300 bg-white text-gray-700 text-sm font-medium hover:bg-gray-50">
                        Board view
                    </a>
                    <a href="{{ route('admin.outreach.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-md bg-[#16a34a] text-white text-sm font-medium hover:bg-[#15803d]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add contact
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="mb-6 rounded-lg bg-green-50 border border-green-200 p-4 text-sm text-green-800">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="mb-6 rounded-lg bg-red-50 border border-red-200 p-4 text-sm text-red-800">{{ session('error') }}</div>
            @endif

            {{-- Metrics cards --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-9 gap-3 mb-8">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                    <div class="text-xs text-gray-500 uppercase tracking-wider">Total</div>
                    <div class="text-xl font-bold text-gray-900">{{ number_format($metrics['total_contacts']) }}</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                    <div class="text-xs text-gray-500 uppercase tracking-wider">DMs Sent</div>
                    <div class="text-xl font-bold text-gray-900">{{ number_format($metrics['dm_sent']) }}</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                    <div class="text-xs text-gray-500 uppercase tracking-wider">Replies</div>
                    <div class="text-xl font-bold text-gray-900">{{ number_format($metrics['replies']) }}</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                    <div class="text-xs text-gray-500 uppercase tracking-wider">Calls Sched.</div>
                    <div class="text-xl font-bold text-gray-900">{{ number_format($metrics['calls_scheduled']) }}</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                    <div class="text-xs text-gray-500 uppercase tracking-wider">Calls Done</div>
                    <div class="text-xl font-bold text-gray-900">{{ number_format($metrics['calls_completed']) }}</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                    <div class="text-xs text-gray-500 uppercase tracking-wider">Converted</div>
                    <div class="text-xl font-bold text-gray-900">{{ number_format($metrics['converted']) }}</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                    <div class="text-xs text-gray-500 uppercase tracking-wider">Reply %</div>
                    <div class="text-xl font-bold text-gray-900">{{ $metrics['reply_rate'] }}%</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                    <div class="text-xs text-gray-500 uppercase tracking-wider">Call %</div>
                    <div class="text-xl font-bold text-gray-900">{{ $metrics['call_rate'] }}%</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                    <div class="text-xs text-gray-500 uppercase tracking-wider">Convert %</div>
                    <div class="text-xl font-bold text-gray-900">{{ $metrics['conversion_rate'] }}%</div>
                </div>
            </div>

            {{-- Filters --}}
            <form method="get" action="{{ route('admin.outreach.index') }}" class="mb-4 flex flex-wrap items-center gap-3">
                <select name="status" class="rounded-lg border-gray-300 shadow-sm focus:border-[#16a34a] focus:ring-[#16a34a] text-sm">
                    <option value="">All statuses</option>
                    @foreach($statuses as $s)
                        <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
                <select name="role" class="rounded-lg border-gray-300 shadow-sm focus:border-[#16a34a] focus:ring-[#16a34a] text-sm">
                    <option value="">All roles</option>
                    @foreach($roles as $r)
                        <option value="{{ $r }}" {{ request('role') === $r ? 'selected' : '' }}>{{ $r }}</option>
                    @endforeach
                </select>
                <select name="persona_type" class="rounded-lg border-gray-300 shadow-sm focus:border-[#16a34a] focus:ring-[#16a34a] text-sm">
                    <option value="">All personas</option>
                    @foreach($personaTypes as $value => $label)
                        <option value="{{ $value }}" {{ request('persona_type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="operator_type" class="rounded-lg border-gray-300 shadow-sm focus:border-[#16a34a] focus:ring-[#16a34a] text-sm">
                    <option value="">All operator types</option>
                    @foreach($operatorTypes as $value => $label)
                        <option value="{{ $value }}" {{ request('operator_type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="priority" class="rounded-lg border-gray-300 shadow-sm focus:border-[#16a34a] focus:ring-[#16a34a] text-sm">
                    <option value="">All priorities</option>
                    @foreach($priorities as $p)
                        <option value="{{ $p }}" {{ request('priority') === $p ? 'selected' : '' }}>{{ $p }}</option>
                    @endforeach
                </select>
                <input type="text" name="organization" value="{{ request('organization') }}" placeholder="Organization..." class="rounded-lg border-gray-300 shadow-sm focus:border-[#16a34a] focus:ring-[#16a34a] text-sm w-40">
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="follow_ups_only" value="1" {{ request('follow_ups_only') ? 'checked' : '' }} class="rounded border-gray-300 text-[#16a34a] focus:ring-[#16a34a]">
                    Follow-ups due only
                </label>
                <button type="submit" class="px-3 py-1.5 rounded-md bg-gray-100 text-gray-700 text-sm font-medium hover:bg-gray-200">Filter</button>
                @if(request()->hasAny(['status', 'role', 'priority', 'organization', 'follow_ups_only', 'sort', 'direction']))
                    <a href="{{ route('admin.outreach.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Clear</a>
                @endif
            </form>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                @if($contacts->isEmpty())
                    <div class="p-6 text-gray-600">
                        <p>No contacts yet.</p>
                        <a href="{{ route('admin.outreach.create') }}" class="mt-2 inline-block text-[#16a34a] hover:underline">Add your first contact</a>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'name', 'direction' => request('sort') === 'name' && request('direction') === 'asc' ? 'desc' : 'asc']) }}" class="hover:text-gray-700">Name</a>
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">LinkedIn</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Persona</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Operator type</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Organization</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contacted</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'follow_up_date', 'direction' => request('sort') === 'follow_up_date' && request('direction') === 'asc' ? 'desc' : 'asc']) }}" class="hover:text-gray-700">Follow-up</a>
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($contacts as $contact)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $contact->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @if($contact->linkedin_url)
                                                <a href="{{ $contact->linkedin_url }}" target="_blank" rel="noopener" class="text-[#16a34a] hover:underline">{{ Str::limit($contact->linkedin_url, 30) }}</a>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $personaTypes[$contact->persona_type] ?? '—' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $operatorTypes[$contact->operator_type] ?? '—' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $contact->role ?? '—' }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-600">{{ Str::limit($contact->organization, 25) ?: '—' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $contact->priority ?? '—' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">{{ $contact->status }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $contact->date_contacted?->format('M j, Y') ?? '—' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $contact->follow_up_date?->format('M j, Y') ?? '—' }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-600 max-w-xs">{{ Str::limit($contact->notes, 40) ?: '—' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                            <a href="{{ route('admin.outreach.edit', $contact) }}" class="text-[#16a34a] hover:underline mr-4">Edit</a>
                                            <form method="post" action="{{ route('admin.outreach.destroy', $contact) }}" class="inline" onsubmit="return confirm('Delete this contact?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-3 border-t border-gray-200">
                        {{ $contacts->withQueryString()->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
