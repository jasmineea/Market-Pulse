@props(['sourcePage' => ''])

<x-modal name="waitlist" maxWidth="2xl">
    <div class="p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Join the waitlist</h2>
        <p class="text-sm text-gray-600 mb-4">Get early access to Professional or Enterprise when we launch.</p>
        <x-waitlist-form :source-page="$sourcePage" />
    </div>
</x-modal>
