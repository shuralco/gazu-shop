<div class="coupon-section mb-4">
    @if(count($appliedCoupon))
        {{-- Застосований купон --}}
        <div class="p-4 bg-green-50 border-2 border-green-500 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <span class="text-2xl">🎟️</span>
                <div>
                    <p class="font-bold">{{ $appliedCoupon['code'] }}</p>
                    <p class="text-sm text-gray-600">Знижка: {{ formatPrice($appliedCoupon['discount']) }}</p>
                </div>
            </div>
            <button wire:click="removeCoupon" class="delete-btn-sm" wire:loading.attr="disabled">
                ×
            </button>
        </div>
    @else
        {{-- Форма введення купону --}}
        <div class="border-2 border-black p-4 bg-white">
            <h3 class="font-black mb-4 flex items-center gap-2">
                <span class="text-xl">🎟️</span>
                ПРОМОКОД
            </h3>
            <form wire:submit="applyCoupon">
                <div class="flex gap-2">
                    <input type="text" 
                           class="input-field flex-1 @error('couponCode') error @enderror" 
                           placeholder="Введіть промокод" 
                           wire:model="couponCode"
                           maxlength="50">
                    <button type="submit" 
                            class="btn-outline px-4" 
                            wire:loading.attr="disabled">
                        <span wire:loading.remove>✓</span>
                        <span wire:loading>...</span>
                    </button>
                </div>
                
                @error('couponCode')
                    <div class="text-red-600 text-sm mt-2">{{ $message }}</div>
                @enderror
            </form>
        </div>
    @endif

    {{-- Повідомлення --}}
    @if($message)
        <div class="p-3 border-2 {{ $messageType === 'success' ? 'border-green-500 bg-green-50 text-green-800' : 'border-red-500 bg-red-50 text-red-800' }} mt-2">
            <span class="text-lg">{{ $messageType === 'success' ? '✅' : '❌' }}</span>
            {{ $message }}
        </div>
    @endif
</div>

@script
<script>
// Очистити повідомлення
$wire.on('clear-message', () => {
    setTimeout(() => {
        $wire.message = '';
        $wire.messageType = '';
    }, 3000);
});
</script>
@endscript