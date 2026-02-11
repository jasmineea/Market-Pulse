@props(['sourcePage' => ''])

@php
    $useCases = \App\Http\Controllers\WaitlistController::USE_CASES;
    $interests = \App\Http\Controllers\WaitlistController::INTERESTS;
    $personaTypes = config('terpinsights.persona_types', []);
    $operatorTypes = config('terpinsights.operator_types', []);
@endphp

<div x-data="{
    submitted: false,
    error: null,
    loading: false,
    personaType: '',
    async submitForm() {
        this.loading = true;
        this.error = null;
        const form = this.$refs.form;
        const data = new FormData(form);
        const body = {
            email: data.get('email'),
            organization: data.get('organization'),
            persona_type: data.get('persona_type'),
            operator_type: data.get('persona_type') === 'operator' ? data.get('operator_type') : null,
            use_case: data.get('use_case'),
            notes: data.get('notes') || null,
            source_page: data.get('source_page') || null,
            interests: form.querySelectorAll('input[name=\'interests[]\']:checked').length ? Array.from(form.querySelectorAll('input[name=\'interests[]\']:checked')).map(el => el.value) : null
        };
        try {
            const res = await fetch('{{ route('waitlist.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || ''
                },
                body: JSON.stringify(body)
            });
            const json = await res.json();
            if (res.ok && json.success) {
                this.submitted = true;
            } else {
                this.error = json.message || 'Something went wrong. Please try again.';
            }
        } catch (e) {
            this.error = 'Something went wrong. Please try again.';
        }
        this.loading = false;
    }
}" x-cloak>
    {{-- Success state --}}
    <div x-show="submitted" x-transition class="rounded-lg bg-green-50 border border-green-200 p-6 text-gray-800">
        <p class="font-semibold text-green-800 mb-2">You're on the waitlist.</p>
        <p class="text-sm mb-2">We're inviting early users in small batches and will follow up soon.</p>
        <p class="text-sm">If you'd like to share more context, feel free to reply to the confirmation email.</p>
    </div>

    {{-- Form --}}
    <div x-show="!submitted">
        <form x-ref="form" @submit.prevent="submitForm()" class="space-y-4">
            @csrf
            @if($sourcePage)
                <input type="hidden" name="source_page" value="{{ $sourcePage }}">
            @endif

            <div>
                <label for="waitlist-email" class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                <input type="email" id="waitlist-email" name="email" required
                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#16a34a] focus:ring-[#16a34a] text-gray-900">
            </div>

            <div>
                <label for="waitlist-organization" class="block text-sm font-medium text-gray-700 mb-1">Organization / Affiliation <span class="text-red-500">*</span></label>
                <input type="text" id="waitlist-organization" name="organization" required
                    placeholder="Organization, outlet, or affiliation"
                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#16a34a] focus:ring-[#16a34a] text-gray-900">
            </div>

            <div>
                <label for="waitlist-persona_type" class="block text-sm font-medium text-gray-700 mb-1">Primary Persona <span class="text-red-500">*</span></label>
                <select id="waitlist-persona_type" name="persona_type" required
                    x-model="personaType"
                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#16a34a] focus:ring-[#16a34a] text-gray-900">
                    <option value="">Select...</option>
                    @foreach($personaTypes as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div x-show="personaType === 'operator'" x-cloak x-transition class="space-y-1">
                <label for="waitlist-operator_type" class="block text-sm font-medium text-gray-700 mb-1">Business Type <span class="text-red-500">*</span></label>
                <select id="waitlist-operator_type" name="operator_type" :required="personaType === 'operator'"
                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#16a34a] focus:ring-[#16a34a] text-gray-900">
                    <option value="">Select...</option>
                    @foreach($operatorTypes as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="waitlist-use_case" class="block text-sm font-medium text-gray-700 mb-1">Primary Use Case <span class="text-red-500">*</span></label>
                <select id="waitlist-use_case" name="use_case" required
                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#16a34a] focus:ring-[#16a34a] text-gray-900">
                    <option value="">Select...</option>
                    @foreach($useCases as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <span class="block text-sm font-medium text-gray-700 mb-2">Areas of Interest (optional)</span>
                <div class="space-y-2">
                    @foreach($interests as $value => $label)
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="interests[]" value="{{ $value }}"
                                class="rounded border-gray-300 text-[#16a34a] focus:ring-[#16a34a]">
                            <span class="text-sm text-gray-700">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div>
                <label for="waitlist-notes" class="block text-sm font-medium text-gray-700 mb-1">Anything specific you're hoping to get from Market Pulse? (optional)</label>
                <textarea id="waitlist-notes" name="notes" rows="3" maxlength="500"
                    placeholder="1–2 sentences"
                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#16a34a] focus:ring-[#16a34a] text-gray-900"></textarea>
            </div>

            <div x-show="error" x-transition class="rounded-lg bg-red-50 border border-red-200 p-3 text-sm text-red-800" x-text="error"></div>

            <button type="submit" :disabled="loading"
                class="w-full inline-flex justify-center items-center px-4 py-2.5 rounded-md bg-[#16a34a] text-white text-sm font-medium hover:bg-[#15803d] disabled:opacity-50 disabled:cursor-not-allowed">
                <span x-show="!loading">Join Waitlist</span>
                <span x-show="loading">Submitting...</span>
            </button>
        </form>
    </div>
</div>
