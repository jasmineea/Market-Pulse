<x-guest-layout>
    <x-slot:main>
        <section class="py-12 md:py-20 px-6">
            <div class="max-w-2xl mx-auto">
                <a href="{{ route('ai-lab') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 mb-6">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Back to AI Lab
                </a>

                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">Collaborate with Terp Insights AI Lab</h1>
                <p class="text-gray-600 mb-8">
                    Tell us about your institution and how you'd like to work together. We typically respond within 5–7 business days.
                </p>

                @if(session('success'))
                    <div class="mb-6 rounded-lg bg-green-50 border border-green-200 p-4 text-sm text-green-800">{{ session('success') }}</div>
                @endif

                @if($errors->any())
                    <div class="mb-6 rounded-lg bg-red-50 border border-red-200 p-4 text-sm text-red-800">
                        <p class="font-medium mb-2">Please correct the following:</p>
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="post" action="{{ route('ai-lab.collaborate.store') }}" class="space-y-6 bg-white rounded-xl border border-gray-200 p-6 md:p-8 shadow-sm">
                    @csrf

                    {{-- Honeypot: hidden from users, bots may fill it --}}
                    <div class="absolute -left-[9999px]" aria-hidden="true">
                        <label for="institutional_dept">Department</label>
                        <input type="text" id="institutional_dept" name="institutional_dept" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name <span class="text-red-500">*</span></label>
                            <input type="text" id="name" name="name" value="{{ old('name') }}" required
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#16a34a] focus:ring-[#16a34a]">
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" required
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#16a34a] focus:ring-[#16a34a]">
                        </div>
                        <div>
                            <label for="organization" class="block text-sm font-medium text-gray-700 mb-1">Organization / Affiliation <span class="text-red-500">*</span></label>
                            <input type="text" id="organization" name="organization" value="{{ old('organization') }}" required
                                placeholder="University, agency, or organization"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#16a34a] focus:ring-[#16a34a]">
                        </div>
                    </div>

                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Role / Title <span class="text-red-500">*</span></label>
                        <select id="role" name="role" required
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#16a34a] focus:ring-[#16a34a]">
                            <option value="">Select your role</option>
                            @foreach($roles as $r)
                                <option value="{{ $r }}" {{ old('role') === $r ? 'selected' : '' }}>{{ $r }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Collaboration Type <span class="text-red-500">*</span></label>
                        <p class="text-xs text-gray-500 mb-2">Select all that apply</p>
                        <div class="space-y-2">
                            @foreach($collaborationTypes as $value => $label)
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="collaboration_types[]" value="{{ $value }}" {{ in_array($value, old('collaboration_types', [])) ? 'checked' : '' }}
                                        class="rounded border-gray-300 text-[#16a34a] focus:ring-[#16a34a]">
                                    <span class="text-gray-700">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Areas of Interest <span class="text-red-500">*</span></label>
                        <p class="text-xs text-gray-500 mb-2">Select all that apply</p>
                        <div class="space-y-2">
                            @foreach($areasOfInterest as $value => $label)
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="areas_of_interest[]" value="{{ $value }}" {{ in_array($value, old('areas_of_interest', [])) ? 'checked' : '' }}
                                        class="rounded border-gray-300 text-[#16a34a] focus:ring-[#16a34a]">
                                    <span class="text-gray-700">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label for="message" class="block text-sm font-medium text-gray-700 mb-1">Message <span class="text-red-500">*</span></label>
                        <p class="text-xs text-gray-500 mb-1">At least 20 characters</p>
                        <textarea id="message" name="message" rows="5" required minlength="20"
                            placeholder="Describe your research goals, institutional context, and how you'd like to collaborate."
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#16a34a] focus:ring-[#16a34a]">{{ old('message') }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="linkedin_url" class="block text-sm font-medium text-gray-700 mb-1">LinkedIn URL</label>
                            <input type="url" id="linkedin_url" name="linkedin_url" value="{{ old('linkedin_url') }}"
                                placeholder="https://linkedin.com/in/..."
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#16a34a] focus:ring-[#16a34a]">
                        </div>
                        <div>
                            <label for="website" class="block text-sm font-medium text-gray-700 mb-1">Website / Lab page</label>
                            <input type="url" id="website" name="website" value="{{ old('website') }}"
                                placeholder="https://..."
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#16a34a] focus:ring-[#16a34a]">
                        </div>
                    </div>

                    <div>
                        <label for="timeline" class="block text-sm font-medium text-gray-700 mb-1">Timeline</label>
                        <select id="timeline" name="timeline"
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#16a34a] focus:ring-[#16a34a]">
                            <option value="">Select if applicable</option>
                            @foreach($timelines as $value => $label)
                                <option value="{{ $value }}" {{ old('timeline') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="w-full sm:w-auto inline-flex justify-center items-center px-6 py-3 rounded-md bg-[#16a34a] text-white font-medium hover:bg-[#15803d] transition-colors">
                            Submit Collaboration Request
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </x-slot:main>
</x-guest-layout>
