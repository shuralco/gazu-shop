<div class="pt-4 md:pt-6">
    <div class="max-w-screen-2xl mx-auto px-4 md:px-8 mb-16">
        <nav class="flex items-center gap-2 text-sm font-medium mb-4">
            <a wire:navigate href="{{ locale_route('home') }}" class="hover:underline font-bold">{{ __('general.home') }}</a>
            <span>/</span>
            <span class="font-bold">{{ __('general.compare_products') }}</span>
        </nav>

        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <h1 class="text-3xl md:text-5xl font-black">{{ __('general.comparison') }}</h1>
            @if($count > 0)
            <div class="flex gap-3 flex-wrap">
                <label class="flex items-center gap-2 text-sm font-bold cursor-pointer">
                    <input type="checkbox" wire:model.live="showDifferencesOnly" class="w-5 h-5 border-2 border-black">
                    {{ __('general.differences_only') }}
                </label>
                <button wire:click="clearAll" class="border-2 border-black px-4 py-2 font-bold text-sm hover:bg-black hover:text-white transition-colors">
                    {{ __('general.clear_all') }}
                </button>
            </div>
            @endif
        </div>

        @if($count === 0)
        <div class="border-4 border-black p-12 text-center">
            <p class="text-2xl font-black mb-4">{{ __('general.comparison_empty') }}</p>
            <p class="text-gray-600 mb-6">{{ __('general.comparison_empty_text') }}</p>
            <a wire:navigate href="{{ locale_route('home') }}" class="bg-black text-white font-black px-8 py-4 inline-block hover:bg-gray-800 transition-colors">{{ __('general.go_to_catalog') }}</a>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                {{-- Product headers --}}
                <thead>
                    <tr>
                        <th class="border-4 border-black p-4 bg-black text-white font-black text-left min-w-[200px]">{{ __('general.characteristic') }}</th>
                        @foreach($products as $product)
                        <th class="border-4 border-black p-4 min-w-[250px] align-top">
                            <div class="relative">
                                <button wire:click="removeProduct({{ $product->id }})" class="absolute top-0 right-0 text-gray-400 hover:text-red-600 font-black text-xl">&times;</button>
                                <div class="w-full h-48 bg-gray-100 mb-3 flex items-center justify-center border-2 border-gray-200">
                                    @if($product->image)
                                    <img src="{{ asset($product->getImage()) }}" alt="{{ $product->title }}" class="max-h-full max-w-full object-contain">
                                    @else
                                    <div class="flex items-center justify-center w-full h-full">
                                        <span class="text-4xl text-gray-400">📦</span>
                                    </div>
                                    @endif
                                </div>
                                <a wire:navigate href="{{ locale_url($product->getLocalizedSlug()) }}" class="font-black text-sm hover:underline block mb-2">{{ $product->title }}</a>
                                <div class="text-xl font-black">{{ number_format($product->price, 0, ',', ' ') }} ₴</div>
                                @if($product->old_price > 0)
                                <div class="text-sm text-gray-500 line-through">{{ number_format($product->old_price, 0, ',', ' ') }} ₴</div>
                                @endif
                            </div>
                        </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($attributes as $attr)
                    <tr class="hover:bg-gray-50">
                        <td class="border-2 border-black p-3 font-bold text-sm bg-gray-50">{{ $attr['name'] }}</td>
                        @foreach($products as $product)
                        <td class="border-2 border-black p-3 text-sm">{{ $attr['values'][$product->id] ?? '—' }}</td>
                        @endforeach
                    </tr>
                    @endforeach
                    {{-- Buy buttons row --}}
                    <tr>
                        <td class="border-2 border-black p-3 font-bold text-sm bg-gray-50">КУПИТИ</td>
                        @foreach($products as $product)
                        <td class="border-2 border-black p-3 text-center">
                            <a wire:navigate href="{{ locale_url($product->getLocalizedSlug()) }}" class="bg-black text-white font-bold px-6 py-3 inline-block text-sm hover:bg-gray-800 transition-colors">
                                КУПИТИ
                            </a>
                        </td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
