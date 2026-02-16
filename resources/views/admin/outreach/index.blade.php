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

            {{-- View toggle: All | Outreach | AI Lab Requests --}}
            @php
                $currentView = request('view', 'all');
                $inboundCount = \App\Models\OutreachContact::where('source', 'ai_lab_collaboration')->where('status', 'Inbound Request')->count();
            @endphp
            <div class="flex flex-wrap items-center gap-2 mb-4">
                {{-- View toggle resets filters so you get a clean view when switching --}}
                <a href="{{ route('admin.outreach.index', ['view' => 'all']) }}"
                    class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium transition-colors {{ $currentView === 'all' ? 'bg-[#16a34a] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    All Contacts
                </a>
                <a href="{{ route('admin.outreach.index', ['view' => 'outreach']) }}"
                    class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium transition-colors {{ $currentView === 'outreach' ? 'bg-[#16a34a] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    Outreach (LinkedIn)
                </a>
                <a href="{{ route('admin.outreach.index', ['view' => 'ai_lab']) }}"
                    class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium transition-colors {{ $currentView === 'ai_lab' ? 'bg-[#16a34a] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    AI Lab Requests
                    @if($inboundCount > 0)
                        <span class="ml-2 px-2 py-0.5 rounded-full text-xs {{ $currentView === 'ai_lab' ? 'bg-white/20' : 'bg-[#16a34a]/20 text-[#16a34a]' }}">{{ $inboundCount }}</span>
                    @endif
                </a>
            </div>

            {{-- Filters --}}
            <form method="get" action="{{ route('admin.outreach.index') }}" class="mb-4 flex flex-wrap items-center gap-3">
                <input type="hidden" name="view" value="{{ $currentView }}">
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
                @if(request()->hasAny(['status', 'role', 'priority', 'source', 'organization', 'follow_ups_only', 'view', 'sort', 'direction']))
                    <a href="{{ route('admin.outreach.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Clear all</a>
                @endif
            </form>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg" x-data="{ selectedContact: null }">
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
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">LinkedIn</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Persona</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Operator type</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Organization</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Message / Details</th>
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
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $contact->email ?: '—' }}</td>
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
                                        <td class="px-6 py-4 text-sm text-gray-600 max-w-xs">
                                            @php
                                                $details = $contact->response_summary ? Str::limit($contact->response_summary, 100) : ($contact->why_selected ? Str::limit($contact->why_selected, 100) : '—');
                                                $hasDetails = ($contact->response_summary || $contact->why_selected);
                                            @endphp
                                            @if($hasDetails)
                                                <button type="button"
                                                    @click="selectedContact = {{ json_encode(['name' => $contact->name, 'email' => $contact->email, 'organization' => $contact->organization, 'role' => $contact->role, 'response_summary' => $contact->response_summary, 'why_selected' => $contact->why_selected, 'source' => $contact->source]) }}"
                                                    class="text-left w-full text-[#16a34a] hover:text-[#15803d] hover:underline focus:outline-none focus:ring-0">
                                                    {{ $details }}
                                                    <span class="text-xs text-gray-400 ml-1">↗</span>
                                                </button>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $contact->priority ?? '—' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">{{ $contact->status }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $contact->date_contacted?->format('M j, Y') ?? '—' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $contact->follow_up_date?->format('M j, Y') ?? '—' }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-600 max-w-xs">{{ Str::limit($contact->notes, 40) ?: '—' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                            @if($contact->response_summary || $contact->why_selected)
                                                <button type="button" @click="selectedContact = {{ json_encode(['name' => $contact->name, 'email' => $contact->email, 'organization' => $contact->organization, 'role' => $contact->role, 'response_summary' => $contact->response_summary, 'why_selected' => $contact->why_selected, 'source' => $contact->source]) }}"
                                                    class="text-[#16a34a] hover:underline mr-4">View</button>
                                            @endif
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

                {{-- Contact detail modal --}}
                <div x-show="selectedContact" x-cloak
                    class="fixed inset-0 z-50 overflow-y-auto"
                    x-transition:enter="ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0">
                    <div class="flex min-h-full items-center justify-center p-4">
                        <div class="fixed inset-0 bg-gray-500/75" @click="selectedContact = null"></div>
                        <div class="relative bg-white rounded-xl shadow-xl max-w-2xl w-full p-6 max-h-[90vh] overflow-y-auto"
                            @click.stop>
                            <div class="flex justify-between items-start mb-4">
                                <h3 class="text-lg font-semibold text-gray-900" x-text="selectedContact?.name || 'Contact details'"></h3>
                                <button type="button" @click="selectedContact = null" class="text-gray-400 hover:text-gray-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                            <template x-if="selectedContact">
                                <div class="space-y-4 text-sm">
                                    <div x-show="selectedContact?.email" class="flex gap-2">
                                        <span class="font-medium text-gray-500 w-28 shrink-0">Email</span>
                                        <a :href="'mailto:' + selectedContact?.email" class="text-[#16a34a] hover:underline" x-text="selectedContact?.email"></a>
                                    </div>
                                    <div x-show="selectedContact?.organization" class="flex gap-2">
                                        <span class="font-medium text-gray-500 w-28 shrink-0">Organization</span>
                                        <span class="text-gray-800" x-text="selectedContact?.organization"></span>
                                    </div>
                                    <div x-show="selectedContact?.role" class="flex gap-2">
                                        <span class="font-medium text-gray-500 w-28 shrink-0">Role</span>
                                        <span class="text-gray-800" x-text="selectedContact?.role"></span>
                                    </div>
                                    <div x-show="selectedContact?.response_summary" class="mt-4">
                                        <span class="block font-medium text-gray-500 mb-1">Message</span>
                                        <div class="rounded-lg bg-gray-50 p-4 text-gray-800 whitespace-pre-wrap" x-text="selectedContact?.response_summary"></div>
                                    </div>
                                    <div x-show="selectedContact?.why_selected" class="mt-4">
                                        <span class="block font-medium text-gray-500 mb-2">Details</span>
                                        <dl class="rounded-lg bg-gray-50 divide-y divide-gray-200 overflow-hidden">
                                            <template x-for="(section, i) in (selectedContact?.why_selected || '').split('. ').filter(s => s.trim())" :key="i">
                                                <div class="px-4 py-3 flex gap-3" x-show="section.includes(':')">
                                                    <dt class="font-medium text-gray-500 shrink-0 min-w-[8rem]" x-text="section.split(':')[0]"></dt>
                                                    <dd class="text-gray-800 m-0" x-text="section.split(':').slice(1).join(':').trim()"></dd>
                                                </div>
                                            </template>
                                        </dl>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
