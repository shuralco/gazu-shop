@php
    use App\Models\DisplaySetting;
    use App\Models\Category;

    $structure = DisplaySetting::get('main_mega_menu_structure', []);
    if (is_string($structure)) {
        $structure = json_decode($structure, true) ?: [];
    }

    // Build lookup for translated category names
    $allCategories = \Illuminate\Support\Facades\Cache::remember('mega_menu_all_cats_' . app()->getLocale(), 1800, function () {
        $cats = Category::where('is_active', true)->get();
        $byId = [];
        $bySlug = [];
        foreach ($cats as $cat) {
            $byId[$cat->id] = $cat->title; // returns current locale via HasTranslations
            // Map all locale slugs to translated title
            foreach (config('app.available_locales', ['uk','en']) as $loc) {
                $s = $cat->getTranslation('slug', $loc, false);
                if ($s) $bySlug[$s] = $cat->title;
            }
        }
        return ['byId' => $byId, 'bySlug' => $bySlug];
    });
    $catById = $allCategories['byId'];
    $catBySlug = $allCategories['bySlug'];
    $showPromo = DisplaySetting::get('main_show_promo', true);
    $promoImage = DisplaySetting::get('main_mega_menu_promo_image', '');
    $promoTitle = DisplaySetting::get('main_mega_menu_promo_title', 'АКЦІЇ ТИЖНЯ');
    $promoSubtitle = DisplaySetting::get('main_mega_menu_promo_subtitle', 'Знижки до 50% на вибрані категорії товарів');
    $promoButton = DisplaySetting::get('main_mega_menu_promo_button', 'ПЕРЕГЛЯНУТИ ВСІ');
    $promoUrl = DisplaySetting::get('main_mega_menu_promo_url', '/specials');

    // Translate promo text for non-default locale
    $promoTranslations = [
        'АКЦІЇ ТИЖНЯ' => __('general.promo_weekly'),
        'Знижки до 50% на вибрані категорії товарів' => __('general.promo_subtitle_default'),
        'ПЕРЕГЛЯНУТИ ВСІ' => __('general.view_all'),
    ];
    $promoTitle = $promoTranslations[$promoTitle] ?? $promoTitle;
    $promoSubtitle = $promoTranslations[$promoSubtitle] ?? $promoSubtitle;
    $promoButton = $promoTranslations[$promoButton] ?? $promoButton;
@endphp

@if(!empty($structure['columns']))
<div class="mega-menu" id="megaMenu">
    <div class="max-w-screen-2xl mx-auto p-6 md:p-8">
        <!-- Categories Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-1 md:gap-2">
            @foreach($structure['columns'] as $columnIndex => $columnItems)
                @if(is_array($columnItems) && !empty($columnItems))
                <div>
                    @foreach($columnItems as $item)
                        @if($item['type'] === 'category')
                        <div class="mb-6">
                            <h4 class="font-black text-black mb-4 text-base md:text-lg border-b-2 border-black pb-2 uppercase">
                                {{ $catById[$item['category_id'] ?? 0] ?? $item['title'] }}
                            </h4>
                            
                            <!-- Підкатегорії -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2">
                                @foreach($item['children'] as $child)
                                <a wire:navigate href="{{ locale_url($child['slug']) }}" class="block text-black text-sm md:text-base font-medium hover:font-semibold hover:text-blue-600 py-1.5 transition-all">
                                    {{ $catBySlug[$child['slug'] ?? ''] ?? $child['title'] }}
                                </a>
                                @endforeach
                            </div>
                            
                            @if($item['show_all_link'])
                            <div class="mt-3">
                                <a wire:navigate href="{{ locale_url($item['slug']) }}" class="text-blue-600 hover:text-blue-800 font-semibold text-sm">
                                    {{ __('general.view_all') }} →
                                </a>
                            </div>
                            @endif
                        </div>
                        @elseif($item['type'] === 'custom_link')
                        @php
                            $linkTitle = $item['title'];
                            $linkTranslations = [
                                'АКЦІЇ' => __('general.specials'),
                                'ХІТИ ПРОДАЖІВ' => __('general.hits'),
                                'НОВИНКИ' => __('general.new_products'),
                            ];
                            $linkTitle = $linkTranslations[$linkTitle] ?? $linkTitle;
                        @endphp
                        <div class="mb-6">
                            <a wire:navigate href="{{ locale_url(ltrim($item['url'], '/')) }}" class="block text-black text-sm md:text-base font-medium hover:font-semibold hover:text-blue-600 py-1.5 transition-all">
                                {{ $linkTitle }}
                            </a>
                        </div>
                        @endif
                    @endforeach
                </div>
                @endif
            @endforeach
        </div>
        
        <!-- Thin Promo Strip at Bottom -->
        @if($showPromo)
        <div class="mt-4">
            <div class="bg-black text-white p-4 md:p-6 shadow-lg">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4 md:gap-6">
                    <!-- Promo Image -->
                    <div class="flex-shrink-0 hidden md:block">
                        @if($promoImage)
                            <img src="{{ asset('storage/' . $promoImage) }}" alt="{{ $promoTitle }}" class="w-16 md:w-20 h-16 md:h-20 object-cover rounded-lg">
                        @else
                            <div class="bg-gray-800 rounded-lg p-2 md:p-3 w-16 md:w-20 h-16 md:h-20 flex items-center justify-center">
                                <div class="w-8 md:w-10 h-8 md:h-10 bg-white rounded opacity-20"></div>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Promo Content -->
                    <div class="flex-1">
                        <h3 class="font-black text-white mb-2 text-xl md:text-2xl uppercase tracking-wide">
                            {{ $promoTitle }}
                        </h3>
                        <p class="text-gray-300 text-sm md:text-base leading-relaxed">
                            {{ $promoSubtitle }}
                        </p>
                    </div>
                    
                    <!-- Action Button -->
                    <div class="flex-shrink-0">
                        <a href="{{ $promoUrl }}" class="bg-white text-black border-2 border-white font-black px-12 md:px-16 py-5 md:py-6 hover:bg-gray-100 hover:text-black hover:border-gray-100 transition-all text-lg md:text-xl uppercase tracking-wide shadow-lg">
                            {{ $promoButton }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endif