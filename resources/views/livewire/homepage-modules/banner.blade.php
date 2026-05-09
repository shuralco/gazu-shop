{{-- Banner Module --}}
@php
    $text = $module->getTranslatedSetting('text', 'banner_text_default', '');
    $subtext = $module->getTranslatedSetting('subtext', 'banner_subtext_default', '');
    $buttonText = $module->getTranslatedSetting('button_text', 'banner_button_default', '');
    $buttonUrl = $module->getSetting('button_url', '#');
    $bgColor = $module->getSetting('bg_color', '#000000');
    $textColor = $module->getSetting('text_color', '#ffffff');
@endphp

<section class="py-16 md:py-24" style="background-color: {{ $bgColor }}; color: {{ $textColor }}">
    <div class="max-w-screen-xl mx-auto px-4 md:px-8 text-center">
        @if($text)
            <h2 class="text-3xl md:text-5xl lg:text-6xl font-black mb-4 md:mb-6">{{ $text }}</h2>
        @endif

        @if($subtext)
            <p class="text-lg md:text-2xl font-medium mb-8 md:mb-12 opacity-80">{{ $subtext }}</p>
        @endif

        @if($buttonText)
            <a href="{{ $buttonUrl }}"
               class="inline-block px-8 md:px-12 py-4 md:py-5 border-4 font-black text-lg md:text-xl uppercase tracking-wider transition-all hover:scale-105"
               style="border-color: {{ $textColor }}; color: {{ $textColor }}">
                {{ $buttonText }}
            </a>
        @endif
    </div>
</section>
