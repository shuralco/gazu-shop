<div x-data="{ show: @entangle('show'), visible: false }"
     x-init="if (show) setTimeout(() => visible = true, 5000)"
     x-show="show && visible" x-cloak x-transition.opacity
     class="fixed inset-0 z-[99999] flex items-center justify-center p-4" style="background: rgba(0,0,0,0.7)">
    <div class="bg-white border-4 border-black max-w-md w-full p-8 relative">
        <button wire:click="dismiss" class="absolute top-3 right-3 text-gray-400 hover:text-black font-black text-2xl" aria-label="{{ __('general.close') }}">&times;</button>

        <div class="text-center">
            <div class="text-6xl mb-4">🎁</div>
            <h2 class="text-2xl font-black mb-2">{{ __('general.promo_discount_title') }}</h2>
            <p class="text-gray-600 mb-6">{{ __('general.promo_discount_text') }}</p>

            <form wire:submit="subscribe" class="space-y-3">
                <input type="email" wire:model="email" placeholder="{{ __('general.newsletter_email_placeholder') }}"
                    class="w-full border-2 border-black px-4 py-3 font-bold text-center" required>
                @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                <button type="submit" class="w-full bg-black text-white font-black py-3 text-lg hover:bg-gray-800 transition-colors">
                    {{ __('general.promo_get_discount') }}
                </button>
            </form>

            <button wire:click="dismiss" class="mt-4 text-sm text-gray-500 hover:text-black underline">
                {{ __('general.promo_no_thanks') }}
            </button>
        </div>
    </div>
</div>
