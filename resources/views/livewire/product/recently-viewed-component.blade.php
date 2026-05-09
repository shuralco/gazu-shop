@if($products->isNotEmpty())
<div class="max-w-screen-2xl mx-auto px-4 md:px-8 py-8 md:py-12">
    <h2 class="text-2xl md:text-3xl font-black mb-6 border-b-4 border-black pb-3">НЕЩОДАВНО ПЕРЕГЛЯНУТІ</h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach($products as $product)
        <a wire:navigate href="{{ locale_url($product->getLocalizedSlug()) }}" class="border-2 border-gray-200 hover:border-black p-3 transition-colors group">
            <div class="aspect-square bg-gray-100 mb-2 flex items-center justify-center">
                @if($product->image)
                <img src="{{ asset($product->getImage()) }}" alt="{{ $product->title }}" class="max-h-full max-w-full object-contain">
                @else
                <span class="text-gray-300 text-3xl">📷</span>
                @endif
            </div>
            <div class="font-bold text-sm truncate group-hover:underline">{{ $product->title }}</div>
            <div class="font-black text-lg">{{ number_format($product->price, 0) }} ₴</div>
        </a>
        @endforeach
    </div>
</div>
@endif
