<x-app-layout>
    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6">
                <a href="{{ route('admin.outreach.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Back to contacts</a>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h2 class="font-semibold text-xl text-gray-800 mb-6">Add Contact</h2>

                @if($errors->any())
                    <div class="mb-6 rounded-lg bg-red-50 border border-red-200 p-4 text-sm text-red-800">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="post" action="{{ route('admin.outreach.store') }}" class="space-y-6" x-data="{ personaType: '{{ old('persona_type', '') }}' }">
                    @csrf

                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#16a34a] focus:ring-[#16a34a]">
                    </div>

                    <div>
                        <label for="linkedin_url" class="block text-sm font-medium text-gray-700 mb-1">LinkedIn URL <span class="text-red-500">*</span></label>
                        <input type="url" id="linkedin_url" name="linkedin_url" value="{{ old('linkedin_url') }}" required placeholder="https://linkedin.com/in/..."
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#16a34a] focus:ring-[#16a34a]">
                    </div>

                    <div>
                        <label for="persona_type" class="block text-sm font-medium text-gray-700 mb-1">Primary Persona <span class="text-red-500">*</span></label>
                        <select id="persona_type" name="persona_type" required
                            x-model="personaType"
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#16a34a] focus:ring-[#16a34a]">
                            <option value="">Select persona</option>
                            @foreach($personaTypes as $value => $label)
                                <option value="{{ $value }}" {{ old('persona_type') === (string) $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div x-show="personaType === 'operator'" x-cloak x-transition class="space-y-1">
                        <label for="operator_type" class="block text-sm font-medium text-gray-700 mb-1">Business Type</label>
                        <select id="operator_type" name="operator_type" :required="personaType === 'operator'"
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#16a34a] focus:ring-[#16a34a]">
                            <option value="">Select business type</option>
                            @foreach($operatorTypes as $value => $label)
                                <option value="{{ $value }}" {{ old('operator_type') === (string) $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="organization" class="block text-sm font-medium text-gray-700 mb-1">Organization</label>
                        <input type="text" id="organization" name="organization" value="{{ old('organization') }}"
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#16a34a] focus:ring-[#16a34a]">
                    </div>

                    <div>
                        <label for="location" class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                        <input type="text" id="location" name="location" value="{{ old('location') }}"
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#16a34a] focus:ring-[#16a34a]">
                    </div>

                    <div>
                        <label for="why_selected" class="block text-sm font-medium text-gray-700 mb-1">Why selected</label>
                        <textarea id="why_selected" name="why_selected" rows="2" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#16a34a] focus:ring-[#16a34a]">{{ old('why_selected') }}</textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="priority" class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                            <select id="priority" name="priority" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#16a34a] focus:ring-[#16a34a]">
                                <option value="">—</option>
                                @foreach($priorities as $p)
                                    <option value="{{ $p }}" {{ old('priority') === $p ? 'selected' : '' }}>{{ $p }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="source" class="block text-sm font-medium text-gray-700 mb-1">Source</label>
                            <input type="text" id="source" name="source" value="{{ old('source') }}"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#16a34a] focus:ring-[#16a34a]">
                        </div>
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                        <select id="status" name="status" required class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#16a34a] focus:ring-[#16a34a]">
                            @foreach($statuses as $s)
                                <option value="{{ $s }}" {{ old('status', 'Not Contacted') === $s ? 'selected' : '' }}>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="date_contacted" class="block text-sm font-medium text-gray-700 mb-1">Date contacted</label>
                            <input type="date" id="date_contacted" name="date_contacted" value="{{ old('date_contacted') }}"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#16a34a] focus:ring-[#16a34a]">
                        </div>
                        <div>
                            <label for="follow_up_date" class="block text-sm font-medium text-gray-700 mb-1">Follow-up date</label>
                            <input type="date" id="follow_up_date" name="follow_up_date" value="{{ old('follow_up_date') }}"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#16a34a] focus:ring-[#16a34a]">
                        </div>
                    </div>

                    <div>
                        <label for="response_summary" class="block text-sm font-medium text-gray-700 mb-1">Response summary</label>
                        <textarea id="response_summary" name="response_summary" rows="3" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#16a34a] focus:ring-[#16a34a]">{{ old('response_summary') }}</textarea>
                    </div>

                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea id="notes" name="notes" rows="3" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#16a34a] focus:ring-[#16a34a]">{{ old('notes') }}</textarea>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="px-4 py-2 rounded-md bg-[#16a34a] text-white text-sm font-medium hover:bg-[#15803d]">
                            Create contact
                        </button>
                        <a href="{{ route('admin.outreach.index') }}" class="px-4 py-2 rounded-md border border-gray-300 text-gray-700 text-sm font-medium hover:bg-gray-50">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
