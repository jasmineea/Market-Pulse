<x-app-layout>
    <div class="py-8 px-4 sm:px-6 lg:px-8 bg-[#F8F9FA] min-h-screen">
        <div class="max-w-full mx-auto">
            <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Outreach Board</h2>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('admin.outreach.index') }}" class="inline-flex items-center px-4 py-2 rounded-md border border-gray-300 bg-white text-gray-700 text-sm font-medium hover:bg-gray-50">
                        Table view
                    </a>
                    <a href="{{ route('admin.outreach.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-md bg-[#16a34a] text-white text-sm font-medium hover:bg-[#15803d]">
                        Add contact
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="mb-6 rounded-lg bg-green-50 border border-green-200 p-4 text-sm text-green-800">{{ session('success') }}</div>
            @endif

            <div class="overflow-x-auto pb-4">
                <div class="flex gap-4 min-w-max">
                    @foreach($statuses as $status)
                        @php
                            $columnContacts = $contactsByStatus[$status] ?? collect();
                        @endphp
                        <div class="flex-shrink-0 w-64 bg-gray-100 rounded-lg p-3 border border-gray-200">
                            <div class="text-sm font-semibold text-gray-700 mb-2 truncate" title="{{ $status }}">{{ $status }}</div>
                            <div class="text-xs text-gray-500 mb-3">{{ $columnContacts->count() }} contact(s)</div>
                            <div class="space-y-2">
                                @foreach($columnContacts as $contact)
                                    <div class="bg-white rounded-lg p-3 shadow-sm border border-gray-200">
                                        <div class="font-medium text-gray-900 text-sm truncate" title="{{ $contact->name }}">{{ $contact->name }}</div>
                                        @if($contact->organization)
                                            <div class="text-xs text-gray-500 truncate" title="{{ $contact->organization }}">{{ $contact->organization }}</div>
                                        @endif
                                        @if($contact->priority)
                                            <span class="inline-block mt-1 text-xs px-1.5 py-0.5 rounded bg-gray-200 text-gray-700">{{ $contact->priority }}</span>
                                        @endif
                                        <div class="mt-2 flex flex-wrap gap-1">
                                            <a href="{{ route('admin.outreach.edit', $contact) }}" class="text-xs text-[#16a34a] hover:underline">Edit</a>
                                            <form method="post" action="{{ route('admin.outreach.update-status', $contact) }}" class="inline">
                                                @csrf
                                                <input type="hidden" name="back" value="{{ url()->current() }}">
                                                <select name="status" class="text-xs border border-gray-300 rounded py-0.5 pr-6 focus:border-[#16a34a] focus:ring-[#16a34a]" onchange="this.form.submit()">
                                                    <option value="">Move to…</option>
                                                    @foreach($statuses as $s)
                                                        @if($s !== $contact->status)
                                                            <option value="{{ $s }}">{{ $s }}</option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
