{{-- Brands Module --}}
@php
    $limit = (int) $module->getSetting('limit', 12);
    $brands = \App\Models\Brand::query()
        ->where('is_active', true)
        ->orderBy('sort_order')
        ->take($limit)
        ->get();
@endphp

@if($brands->isNotEmpty())
<section class="py-16 md:py-24 bg-gray-100">
    <div class="max-w-screen-2xl mx-auto px-4 md:px-8">
        @if($module->title)
            <h2 class="text-3xl md:text-6xl font-black text-black mb-8 md:mb-16 text-center">{{ \App\Models\HomepageModule::translateValue($module->title) }}</h2>
        @endif

        <div class="grid grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4 md:gap-6">
            @foreach($brands as $brand)
                <a wire:navigate href="{{ locale_url('brands/' . $brand->slug) }}"
                   class="aspect-square border-4 border-black bg-white flex items-center justify-center p-4 hover:bg-black hover:text-white transition-all group"
                   title="{{ $brand->name }}">
                    @if($brand->logo)
                        <img src="{{ asset($brand->logo) }}"
                             alt="{{ $brand->name }}"
                             class="max-w-full max-h-full object-contain group-hover:invert transition-all">
                    @else
                        @php
                            $brandName = mb_strtoupper($brand->name);
                            $nameLength = mb_strlen($brandName);
                            $fontSize = $nameLength <= 4 ? 'text-2xl' : ($nameLength <= 8 ? 'text-lg' : 'text-sm');
                        @endphp
                        <span class="{{ $fontSize }} font-black text-center leading-tight">{{ $brandName }}</span>
                    @endif
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif
