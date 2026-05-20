@props(['categories' => null])
@php
    // Accent palette — авто-стиль (oil-blue, brake-red, ink-steel, warn-amber, etc).
    $accentsRaw = ($gazuSettings ?? null)['gazu_category_accents'] ?? null;
    if (is_array($accentsRaw) && ! empty($accentsRaw)) {
        $accents = array_map(fn ($x) => is_array($x) ? ($x['color'] ?? null) : $x, $accentsRaw);
        $accents = array_values(array_filter($accents));
    }
    if (empty($accents)) {
        $accents = [
            '#0066FF',  // engine blue
            '#E63946',  // brake red
            '#37474F',  // suspension steel
            '#F4A300',  // electric amber
            '#1E88E5',  // transmission azure
            '#0D9488',  // fluids teal
            '#7C3AED',  // body purple
            '#475569',  // accessories slate
        ];
    }
    // Realistic auto-part glyphs. innerHTML of <svg viewBox="0 0 24 24"> — multi-element.
    // Each glyph reads as a recognizable part silhouette (gear, disc, plug, can, etc).
    $iconBySlug = [
        // Двигун — V-block з валом + 2 поршні + клапани зверху
        'engine' => '<rect x="3" y="9" width="18" height="9" rx="1"/><path d="M5 9V6h3v3M16 9V6h3v3"/><circle cx="6.5" cy="13.5" r="1.4"/><circle cx="12" cy="13.5" r="1.4"/><circle cx="17.5" cy="13.5" r="1.4"/><path d="M3 18v2h18v-2"/>',
        // Гальма — диск + caliper збоку + центральна гайка
        'brakes' => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5.5"/><circle cx="12" cy="12" r="1.5" fill="currentColor"/><path d="M3.5 8.5h4v7h-4z"/><path d="M5 12h2"/>',
        // Підвіска — пружина амортизатора (zigzag) + шток
        'suspension' => '<rect x="9" y="2" width="6" height="3" rx="0.5"/><path d="M12 5v2"/><path d="M8 7h8l-2 2h-4l2 2H8l2 2h4l-2 2H8l2 2h4"/><path d="M12 18v3"/><rect x="9" y="19" width="6" height="3" rx="0.5"/>',
        // Електрика — свічка запалювання (резьба + ізолятор + електрод)
        'electrics' => '<path d="M11 2h2v3h-2z"/><rect x="9.5" y="5" width="5" height="3" rx="0.5"/><path d="M10 8h4l-0.5 2.5h-3z"/><rect x="10.5" y="10.5" width="3" height="6"/><path d="M11 16.5v3M13 16.5v3M11.5 19.5h2"/><path d="M11.5 19.5l-1.5 2.5"/>',
        // Трансмісія — зубчасте колесо (gear)
        'transmission' => '<path d="M12 2v3M12 19v3M2 12h3M19 12h3M5 5l2 2M17 17l2 2M5 19l2-2M17 7l2-2"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2.5"/>',
        // Мастила — каністра з ручкою + носик
        'fluids' => '<path d="M7 7h10v13H7z"/><path d="M9 7V4h3.5v3"/><path d="M12.5 5h4v3l-2 1"/><path d="M9 10h6M9 13h6M9 16h6"/>',
        // Кузов — силует машини збоку (хетчбек)
        'body' => '<path d="M3 16h2c0-1.7 1.3-3 3-3s3 1.3 3 3M11 16h2c0-1.7 1.3-3 3-3s3 1.3 3 3"/><path d="M3 16v-3l3-5h11l3 4v4"/><path d="M6 8h4v3M11 8h5v3"/><circle cx="8" cy="16" r="2"/><circle cx="16" cy="16" r="2"/>',
        // Аксесуари — гайковий ключ (wrench)
        'accessories' => '<path d="M15.5 4.5a4 4 0 105 5l-2.5 2.5-3-3z"/><path d="M14.5 8.5L4 19l3 3 10.5-10.5"/>',
    ];
    $defaultIcon = '<rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 12h18M12 3v18"/>';

    $tree = $categories ?? app(\App\Services\Gazu\MegaMenuBuilder::class)->build();
    $cats = [];
    if (! empty($tree) && is_array($tree)) {
        foreach (array_slice($tree, 0, 8) as $i => $node) {
            $slug = $node['slug'] ?? null;
            $children = $node['children'] ?? [];
            $photoPath = $slug ? public_path("img/categories/{$slug}.webp") : null;
            // ?v=filemtime — cache-bust коли фото підмінюють (asset кеш max-age 7д).
            $photoUrl = ($photoPath && is_file($photoPath))
                ? asset("img/categories/{$slug}.webp").'?v='.@filemtime($photoPath)
                : null;
            $cats[] = [
                'name'   => $node['label'] ?? '—',
                'count'  => $node['count'] ?? 0,
                'icon'   => $iconBySlug[$slug] ?? $defaultIcon,
                'accent' => $accents[$i % count($accents)],
                'photo'  => $photoUrl,
                'url'    => $slug ? url('/'.$slug) : route('gazu.catalog'),
                'subs'   => array_slice(array_map(fn ($c) => [
                    'label' => $c['label'] ?? '',
                    'url'   => isset($c['slug']) ? url('/'.$c['slug']) : '#',
                ], is_array($children) ? $children : []), 0, 4),
            ];
        }
    }
    if (empty($cats)) {
        $photo = fn ($s) => is_file(public_path("img/categories/{$s}.webp"))
            ? asset("img/categories/{$s}.webp").'?v='.@filemtime(public_path("img/categories/{$s}.webp"))
            : null;
        $cats = [
            ['name' => 'Двигун', 'count' => 8420, 'icon' => $iconBySlug['engine'], 'accent' => $accents[0], 'photo' => $photo('engine'), 'url' => route('gazu.catalog'), 'subs' => []],
            ['name' => 'Гальма', 'count' => 3180, 'icon' => $iconBySlug['brakes'], 'accent' => $accents[1], 'photo' => $photo('brakes'), 'url' => route('gazu.catalog'), 'subs' => []],
            ['name' => 'Підвіска', 'count' => 4920, 'icon' => $iconBySlug['suspension'], 'accent' => $accents[2], 'photo' => $photo('suspension'), 'url' => route('gazu.catalog'), 'subs' => []],
            ['name' => 'Електрика', 'count' => 6210, 'icon' => $iconBySlug['electrics'], 'accent' => $accents[3], 'photo' => $photo('electrics'), 'url' => route('gazu.catalog'), 'subs' => []],
            ['name' => 'Трансмісія', 'count' => 2840, 'icon' => $iconBySlug['transmission'], 'accent' => $accents[4], 'photo' => $photo('transmission'), 'url' => route('gazu.catalog'), 'subs' => []],
            ['name' => 'Мастила', 'count' => 1560, 'icon' => $iconBySlug['fluids'], 'accent' => $accents[5], 'photo' => $photo('fluids'), 'url' => route('gazu.catalog'), 'subs' => []],
            ['name' => 'Кузов', 'count' => 980, 'icon' => $iconBySlug['body'], 'accent' => $accents[6], 'photo' => $photo('body'), 'url' => route('gazu.catalog'), 'subs' => []],
            ['name' => 'Аксесуари', 'count' => 420, 'icon' => $iconBySlug['accessories'], 'accent' => $accents[7], 'photo' => $photo('accessories'), 'url' => route('gazu.catalog'), 'subs' => []],
        ];
    }
@endphp
<section class="gazu-container pt-14 pb-6">
    <div class="flex items-end justify-between mb-7">
        <div>
            <div class="gazu-mono text-[11px] text-[var(--gazu-blue)] tracking-widest uppercase mb-2">Категорії</div>
            <h2 class="gazu-display text-[26px] sm:text-[34px] font-semibold text-[var(--gazu-ink)] m-0 leading-tight">
                {{ $gazuSettings['gazu_section_categories'] ?? 'Усе для вашого авто' }}
            </h2>
        </div>
        <a wire:navigate href="{{ route('gazu.catalog') }}" class="text-sm text-[var(--gazu-blue)] no-underline inline-flex items-center gap-1.5 hover:gap-2 transition-all">
            Усі категорії <x-gazu.icon name="arrow-r" size="14"/>
        </a>
    </div>

    {{-- Premium square-tile grid. Square aspect-ratio, gradient bg per category,
         hover lifts the card + slides subcategories from bottom. --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 sm:gap-4">
        @foreach($cats as $c)
            <a wire:navigate href="{{ $c['url'] }}"
               class="gazu-cat-tile group relative aspect-square rounded-2xl no-underline text-white overflow-hidden cursor-pointer transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_24px_50px_-20px_rgba(14,27,44,0.45)]"
               style="background: linear-gradient(135deg, {{ $c['accent'] }} 0%, color-mix(in srgb, {{ $c['accent'] }} 70%, #0E1B2C) 100%);">

                @if(! empty($c['photo']))
                    {{-- Photographic background (Pexels) — zoom-in on hover --}}
                    <img src="{{ $c['photo'] }}" alt="{{ $c['name'] }}" loading="lazy" decoding="async"
                         class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                    {{-- Dark gradient overlay: transparent top → accent-tinted dark bottom, для читабельності назви --}}
                    <div class="absolute inset-0"
                         style="background: linear-gradient(to top, color-mix(in srgb, {{ $c['accent'] }} 55%, #0A1422) 0%, rgba(10,20,34,0.55) 38%, rgba(10,20,34,0.15) 70%, rgba(10,20,34,0.30) 100%);"></div>
                @else
                    {{-- Fallback: dot pattern + big glyph коли фото немає --}}
                    <div class="absolute inset-0 opacity-[0.08]"
                         style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 12px 12px;"></div>
                    <svg class="absolute -bottom-3 -right-3 w-32 h-32 sm:w-40 sm:h-40 text-white/20 group-hover:text-white/30 transition-colors"
                         viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round">
                        {!! $c['icon'] !!}
                    </svg>
                @endif

                {{-- Bottom: title + subcategories slide-up on hover --}}
                <div class="absolute inset-x-0 bottom-0 p-4">
                    <div class="gazu-display text-[18px] sm:text-[22px] font-semibold leading-tight mb-1" style="text-shadow: 0 1px 8px rgba(10,20,34,0.55);">{{ $c['name'] }}</div>

                    @if(! empty($c['subs']))
                        <div class="gazu-cat-subs flex flex-wrap gap-1 mt-2 opacity-90 transition-all duration-300 max-h-0 group-hover:max-h-24 overflow-hidden">
                            @foreach($c['subs'] as $sub)
                                <span class="inline-block px-2 py-0.5 bg-white/15 rounded text-[11px] truncate max-w-[120px]">{{ $sub['label'] }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Hover arrow indicator --}}
                <div class="absolute top-3 right-3 w-8 h-8 rounded-full bg-white/20 backdrop-blur inline-flex items-center justify-center opacity-0 group-hover:opacity-100 translate-x-2 group-hover:translate-x-0 transition-all duration-300">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </div>
            </a>
        @endforeach
    </div>
</section>
