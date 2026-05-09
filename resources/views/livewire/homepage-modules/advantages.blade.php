{{-- Advantages Module --}}
@php
    $items = $module->getSetting('items', []);
    $colCount = count($items);
    $gridCols = match (true) {
        $colCount <= 2 => 'md:grid-cols-2',
        $colCount === 3 => 'md:grid-cols-3',
        default => 'md:grid-cols-2 lg:grid-cols-4',
    };
@endphp

@if(!empty($items))
<section class="py-16 md:py-24 bg-black text-white">
    <div class="max-w-screen-xl mx-auto px-4 md:px-8">
        @if($module->title)
            <h2 class="text-3xl md:text-6xl font-black mb-8 md:mb-16 text-center">{{ \App\Models\HomepageModule::translateValue($module->title) }}</h2>
        @endif

        <div class="grid {{ $gridCols }} gap-8 md:gap-16">
            @foreach($items as $item)
                <div class="text-center">
                    <div class="w-16 h-16 border-4 border-white mx-auto mb-6 flex items-center justify-center">
                        <span class="text-2xl">{{ $item['icon'] ?? '⭐' }}</span>
                    </div>
                    <h3 class="text-xl md:text-2xl font-black mb-4">{{ \App\Models\HomepageModule::translateValue($item['title'] ?? '') }}</h3>
                    <p class="text-base md:text-lg font-medium leading-relaxed opacity-80">
                        {{ \App\Models\HomepageModule::translateValue($item['text'] ?? '') }}
                    </p>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif
