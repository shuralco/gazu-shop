<div x-data="{ show: @entangle('show') }" x-show="show" x-cloak x-transition.opacity
     class="fixed bottom-0 left-0 right-0 bg-black text-white z-[9999] border-t-4 border-white">
    <div class="max-w-screen-2xl mx-auto px-4 py-4 flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="text-sm md:text-base">
            <strong>🍪 Цей сайт використовує cookies</strong> для покращення вашого досвіду.
            <a wire:navigate href="{{ locale_route('privacy') }}" class="underline hover:no-underline">{{ __('general.privacy_policy') }}</a>
        </div>
        <div class="flex gap-3 flex-shrink-0">
            <button wire:click="accept" class="bg-white text-black font-black px-6 py-2 text-sm hover:bg-gray-200 transition-colors">
                ПРИЙНЯТИ
            </button>
        </div>
    </div>
</div>
