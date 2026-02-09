<x-guest-layout>
    <h1 class="text-2xl font-bold text-gray-900 text-center">Create your account</h1>
    <p class="text-gray-500 text-center mt-1 mb-8">Get free access during our beta period.</p>

    <form method="POST" action="{{ route('register') }}" class="space-y-6">
        @csrf

        {{-- Full name --}}
        <div>
            <x-input-label for="name" value="Full name" />
            <x-text-input
                id="name"
                type="text"
                name="name"
                :value="old('name')"
                required
                autofocus
                autocomplete="name"
                placeholder="Jane Smith"
                class="block mt-1.5 w-full rounded-lg border-gray-300 bg-white focus:border-[#16a34a] focus:ring-[#16a34a] focus:bg-[#FFFBEB]"
            />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        {{-- Email --}}
        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input
                id="email"
                type="email"
                name="email"
                :value="old('email')"
                required
                autocomplete="username"
                class="block mt-1.5 w-full rounded-lg border-gray-300 bg-white focus:border-[#16a34a] focus:ring-[#16a34a] focus:bg-[#FFFBEB]"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        {{-- Password --}}
        <div>
            <x-input-label for="password" value="Password" />
            <x-text-input
                id="password"
                type="password"
                name="password"
                required
                autocomplete="new-password"
                class="block mt-1.5 w-full rounded-lg border-gray-300 bg-white focus:border-[#16a34a] focus:ring-[#16a34a] focus:bg-[#FFFBEB]"
            />
            <p class="mt-1.5 text-sm text-gray-500">Must be at least 8 characters</p>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        {{-- Confirm Password --}}
        <div>
            <x-input-label for="password_confirmation" value="Confirm password" />
            <x-text-input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
                class="block mt-1.5 w-full rounded-lg border-gray-300 bg-white focus:border-[#16a34a] focus:ring-[#16a34a] focus:bg-[#FFFBEB]"
            />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <button type="submit" class="w-full flex justify-center items-center py-3 px-4 rounded-lg bg-[#16a34a] text-white font-medium hover:bg-[#15803d] focus:outline-none focus:ring-2 focus:ring-[#16a34a] focus:ring-offset-2 transition-colors">
            Create account
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-gray-600">
        Already have an account?
        <a href="{{ route('login') }}" class="font-medium text-[#16a34a] hover:text-[#15803d] focus:outline-none focus:ring-2 focus:ring-[#16a34a] focus:ring-offset-0 rounded">Log in</a>
    </p>
</x-guest-layout>
