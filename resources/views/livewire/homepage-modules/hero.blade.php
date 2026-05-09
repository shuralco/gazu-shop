{{-- Hero Section Module --}}
@php
    $subtitle = $module->getTranslatedSetting('subtitle', 'hero_subtitle_default', 'E-COMMERCE 2025');
    $titleLine1 = $module->getTranslatedSetting('title_line1', 'hero_title_line1_default', 'СУЧАСНИЙ');
    $titleLine2 = $module->getTranslatedSetting('title_line2', 'hero_title_line2_default', 'МАГАЗИН');
    $description = $module->getTranslatedSetting('description', 'hero_description_default', '');
    $buttonText = $module->getTranslatedSetting('button_text', 'hero_button_default', 'ПОЧАТИ ПОКУПКИ');
    $buttonUrl = $module->getSetting('button_url', '/specials');
    $bgColor = $module->getSetting('bg_color', '#ffffff');
@endphp

<section class="grid-pattern relative overflow-hidden" style="background-color: {{ $bgColor }}">
    <div class="mobile-decoration mobile-decoration-1"></div>
    <div class="mobile-decoration mobile-decoration-2"></div>

    <div class="hero-container">
        <div class="content-wrapper">
            {{-- Content Section --}}
            <div class="space-y-6 md:space-y-8">
                {{-- Eyebrow text --}}
                @if($subtitle)
                <div>
                    <span class="inline-block px-4 md:px-6 py-2 border-2 border-black text-black font-bold uppercase text-xs md:text-sm tracking-wider">
                        {{ $subtitle }}
                    </span>
                </div>
                @endif

                {{-- Main Headline --}}
                <h1 class="headline font-black text-black leading-none">
                    {{ $titleLine1 }}<br>
                    <span class="text-outline">{{ $titleLine2 }}</span>
                </h1>

                {{-- Subtitle --}}
                @if($description)
                <div class="subtitle space-y-1 md:space-y-2">
                    @foreach(explode("\n", $description) as $line)
                        @if(trim($line))
                            <p class="text-black font-semibold">{{ trim($line) }}</p>
                        @endif
                    @endforeach
                </div>
                @endif

                {{-- CTA Button --}}
                @if($buttonText)
                <div>
                    <x-ui.button :href="$buttonUrl" size="lg" class="btn-3d touch-optimized">
                        {{ $buttonText }}
                    </x-ui.button>
                </div>
                @endif
            </div>

            {{-- Visual Section - Desktop only --}}
            <div class="visual-wrapper">
                <div class="relative">
                    <div class="aspect-square border-4 md:border-6 border-black bg-white relative">
                        <div class="absolute top-4 md:top-6 left-4 md:left-6 w-6 md:w-8 h-6 md:h-8 bg-black"></div>
                        <div class="absolute bottom-4 md:bottom-6 right-4 md:right-6 w-8 md:w-12 h-8 md:h-12 border-2 md:border-3 border-black"></div>
                        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-12 md:w-16 h-12 md:h-16 border-2 md:border-3 border-black bg-gray-100"></div>

                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="text-center">
                                <div class="text-xl md:text-2xl lg:text-3xl font-black text-black mb-1">SHOP</div>
                                <div class="text-xs md:text-sm font-bold text-gray-600 tracking-widest">UKRAINE</div>
                            </div>
                        </div>
                    </div>

                    <div class="absolute -top-2 md:-top-3 -right-2 md:-right-3 w-3 md:w-4 h-3 md:h-4 bg-black rotate-45"></div>
                    <div class="absolute -bottom-2 md:-bottom-3 -left-2 md:-left-3 w-4 md:w-6 h-4 md:h-6 border-2 border-black rotate-12"></div>
                </div>
            </div>
        </div>

        {{-- Bottom stats --}}
        <div class="stats-grid">
            <div class="text-center p-2">
                <div class="text-lg md:text-2xl lg:text-3xl font-black text-black">10K+</div>
                <div class="text-xs md:text-sm font-bold text-gray-600 uppercase tracking-wide mt-1">{{ __('general.hero_stat_products') }}</div>
            </div>
            <div class="text-center p-2">
                <div class="text-lg md:text-2xl lg:text-3xl font-black text-black">2H</div>
                <div class="text-xs md:text-sm font-bold text-gray-600 uppercase tracking-wide mt-1">{{ __('general.hero_stat_delivery') }}</div>
            </div>
            <div class="text-center p-2">
                <div class="text-lg md:text-2xl lg:text-3xl font-black text-black">24/7</div>
                <div class="text-xs md:text-sm font-bold text-gray-600 uppercase tracking-wide mt-1">{{ __('general.hero_stat_support') }}</div>
            </div>
            <div class="text-center p-2">
                <div class="text-lg md:text-2xl lg:text-3xl font-black text-black">100%</div>
                <div class="text-xs md:text-sm font-bold text-gray-600 uppercase tracking-wide mt-1">{{ __('general.hero_stat_guarantee') }}</div>
            </div>
        </div>
    </div>
</section>
