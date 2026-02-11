@props(['align' => 'right', 'width' => '48', 'contentClasses' => 'py-1 bg-white'])

@php
$alignmentClasses = match ($align) {
    'left' => 'ltr:origin-top-left rtl:origin-top-right start-0',
    'top' => 'origin-top',
    default => 'ltr:origin-top-right rtl:origin-top-left end-0',
};

$width = match ($width) {
    '48' => 'w-48',
    default => $width,
};
@endphp

{{-- Use native <details>/<summary> so dropdown works when JavaScript (e.g. Alpine) fails to load on production --}}
<div class="relative group/details">
    <details class="relative">
        <summary class="list-none cursor-pointer [&::-webkit-details-marker]:hidden [&::marker]:content-none">
            <span class="inline-flex items-center gap-1.5 px-2 py-2 border border-transparent text-sm font-medium rounded-md text-gray-700 bg-white hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#16a34a]/20 transition ease-in-out duration-150">
                {{ $trigger }}
            </span>
        </summary>
        <div class="absolute z-50 mt-2 {{ $width }} rounded-md shadow-lg ring-1 ring-black ring-opacity-5 {{ $alignmentClasses }} py-1 bg-white"
             style="min-width: 10rem;">
            <div class="rounded-md {{ $contentClasses }}">
                {{ $content }}
            </div>
        </div>
    </details>
</div>
