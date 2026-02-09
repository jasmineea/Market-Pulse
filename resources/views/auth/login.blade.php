<x-guest-layout>
    {{-- Session status (e.g. password reset confirmation) --}}
    <x-auth-session-status class="mb-4 text-center text-sm text-gray-600" :status="session('status')" />

    <h1 class="text-2xl font-bold text-gray-900 text-center">Welcome back</h1>
    <p class="text-gray-500 text-center mt-1 mb-8">Log in to access your market briefing.</p>

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        {{-- Email --}}
        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input
                id="email"
                type="email"
                name="email"
                :value="old('email')"
                required
                autofocus
                autocomplete="username"
                class="block mt-1.5 w-full rounded-lg border-gray-300 bg-white focus:border-[#16a34a] focus:ring-[#16a34a] focus:bg-[#FFFDD0]"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        {{-- Password + Forgot link on same row --}}
        <div>
            <div class="flex items-center justify-between">
                <x-input-label for="password" value="Password" />
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-sm text-gray-500 hover:text-[#16a34a] focus:outline-none focus:ring-2 focus:ring-[#16a34a] focus:ring-offset-0 rounded">
                        Forgot password?
                    </a>
                @endif
            </div>
            <x-text-input
                id="password"
                type="password"
                name="password"
                required
                autocomplete="current-password"
                class="block mt-1.5 w-full rounded-lg border-gray-300 bg-white focus:border-[#16a34a] focus:ring-[#16a34a] focus:bg-[#FFFDD0]"
            />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        {{-- Remember me (optional, subtle) --}}
        <div class="flex items-center">
            <input id="remember_me" type="checkbox" name="remember" class="rounded border-gray-300 text-[#16a34a] shadow-sm focus:ring-[#16a34a]">
            <label for="remember_me" class="ms-2 text-sm text-gray-600">Remember me</label>
        </div>

        <button type="submit" class="w-full flex justify-center items-center py-3 px-4 rounded-lg bg-[#16a34a] text-white font-medium hover:bg-[#15803d] focus:outline-none focus:ring-2 focus:ring-[#16a34a] focus:ring-offset-2 transition-colors">
            Log in
        </button>
    </form>

    <p class="mt-6 text-center text-sm text-gray-600">
        Don't have an account?
        <a href="{{ route('register') }}" class="font-medium text-[#16a34a] hover:text-[#15803d] focus:outline-none focus:ring-2 focus:ring-[#16a34a] focus:ring-offset-0 rounded">Sign up</a>
    </p>
</x-guest-layout>
