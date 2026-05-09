<div x-data="{ show: {{ $count > 0 ? 'true' : 'false' }} }" x-show="show" x-cloak x-transition.opacity
     class="fixed bottom-0 left-0 right-0 bg-white border-t-4 border-black shadow-2xl z-[9998]">
    <div class="max-w-screen-2xl mx-auto px-4 py-3 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3 overflow-x-auto">
            @foreach($products as $product)
            <div class="flex items-center gap-2 bg-gray-100 px-3 py-2 border-2 border-black flex-shrink-0">
                <span class="text-sm font-bold truncate max-w-[120px]">{{ $product->title }}</span>
                <button wire:click="removeProduct({{ $product->id }})" class="text-gray-400 hover:text-red-600 font-black">&times;</button>
            </div>
            @endforeach
        </div>
        <a wire:navigate href="{{ locale_route('comparison') }}" class="bg-black text-white font-black px-6 py-3 flex-shrink-0 text-sm hover:bg-gray-800 transition-colors">
            {{ __('general.compare') }} ({{ $count }})
        </a>
    </div>
</div>
