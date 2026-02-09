@props(['href' => '/', 'text' => 'TerpInsights'])
{{-- Market Pulse logo: green square with upward trend line + text --}}
<a href="{{ $href }}" {{ $attributes->merge(['class' => 'flex items-center gap-2 no-underline text-inherit']) }}>
    <svg class="w-8 h-8 shrink-0" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <rect width="32" height="32" rx="6" fill="#16a34a"/>
        <path d="M8 22V14L12 18L16 10L20 14L24 8" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
    <span class="text-xl font-semibold text-gray-900">{{ $text }}</span>
</a>
