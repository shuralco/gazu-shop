@php
$slides = $module->getSetting('slides', []);
$autoplay = $module->getSetting('autoplay', true);
$interval = $module->getSetting('interval', 5000);
@endphp

@if(!empty($slides))
<div x-data="{
    current: 0,
    slides: {{ count($slides) }},
    autoplay: {{ $autoplay ? 'true' : 'false' }},
    interval: {{ $interval }},
    timer: null,
    init() {
        if (this.autoplay) {
            this.timer = setInterval(() => this.next(), this.interval);
        }
    },
    next() { this.current = (this.current + 1) % this.slides; },
    prev() { this.current = (this.current - 1 + this.slides) % this.slides; },
    goTo(i) { this.current = i; },
    destroy() { if (this.timer) clearInterval(this.timer); }
}" class="relative overflow-hidden">

    @foreach($slides as $index => $slide)
    <div x-show="current === {{ $index }}"
         x-transition:enter="transition ease-out duration-500"
         x-transition:enter-start="opacity-0 transform translate-x-full"
         x-transition:enter-end="opacity-100 transform translate-x-0"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="min-h-[400px] md:min-h-[500px] flex items-center"
         style="background-color: {{ $slide['bg_color'] ?? '#000' }}; color: {{ $slide['text_color'] ?? '#fff' }}">
        <div class="max-w-screen-2xl mx-auto px-4 md:px-8 py-12 md:py-20 w-full">
            @if(!empty($slide['subtitle']))
            <div class="inline-block border-2 px-4 py-1 text-sm font-bold mb-4" style="border-color: currentColor">{{ \App\Models\HomepageModule::translateValue($slide['subtitle']) }}</div>
            @endif
            <h2 class="text-4xl md:text-7xl font-black mb-4 leading-tight">{{ \App\Models\HomepageModule::translateValue($slide['title'] ?? '') }}</h2>
            @if(!empty($slide['description']))
            <p class="text-lg md:text-xl font-medium mb-8 max-w-xl">{{ \App\Models\HomepageModule::translateValue($slide['description']) }}</p>
            @endif
            @if(!empty($slide['button_text']))
            <a href="{{ $slide['button_url'] ?? '#' }}" class="inline-block border-2 px-8 py-4 font-black text-lg hover:opacity-80 transition-opacity" style="border-color: currentColor">
                {{ \App\Models\HomepageModule::translateValue($slide['button_text']) }}
            </a>
            @endif
        </div>
    </div>
    @endforeach

    {{-- Navigation arrows --}}
    @if(count($slides) > 1)
    <button @click="prev()" class="absolute left-4 top-1/2 -translate-y-1/2 w-12 h-12 bg-white/80 hover:bg-white text-black flex items-center justify-center font-black text-2xl border-2 border-black" aria-label="{{ __('general.slider_prev_aria') }}">&lsaquo;</button>
    <button @click="next()" class="absolute right-4 top-1/2 -translate-y-1/2 w-12 h-12 bg-white/80 hover:bg-white text-black flex items-center justify-center font-black text-2xl border-2 border-black" aria-label="{{ __('general.slider_next_aria') }}">&rsaquo;</button>

    {{-- Dots --}}
    <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2">
        @foreach($slides as $index => $slide)
        <button @click="goTo({{ $index }})"
            :class="current === {{ $index }} ? 'bg-black w-8' : 'bg-gray-400 w-3'"
            class="h-3 transition-all border border-black"
            aria-label="{{ __('general.slider_goto_aria', ['number' => $index + 1]) }}"></button>
        @endforeach
    </div>
    @endif
</div>
@endif
