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
    // Iconic glyphs (1 SVG per category by slug/name match).
    $iconBySlug = [
        'engine' => 'M 14 4 L 14 8 L 18 8 L 18 12 L 22 12 L 22 16 L 18 16 L 18 20 L 14 20 L 14 16 L 6 16 L 6 12 L 2 12 L 2 8 L 6 8 L 6 4 L 10 4 L 10 12 L 14 12 Z',
        'brakes' => 'M 12 2 A 10 10 0 1 0 12 22 A 10 10 0 1 0 12 2 M 12 6 A 6 6 0 1 1 12 18 A 6 6 0 1 1 12 6',
        'suspension' => 'M 12 2 L 12 22 M 8 6 L 16 6 M 6 10 L 18 10 M 8 14 L 16 14 M 10 18 L 14 18',
        'electrics' => 'M 13 2 L 4 14 L 11 14 L 10 22 L 20 10 L 13 10 Z',
        'transmission' => 'M 7 7 L 17 7 L 17 17 L 7 17 Z M 9 5 L 9 9 M 15 5 L 15 9 M 9 15 L 9 19 M 15 15 L 15 19',
        'fluids' => 'M 12 2.5 C 7 9 5 13 5 16 a 7 7 0 0 0 14 0 C 19 13 17 9 12 2.5 Z',
        'body' => 'M 5 11 L 7 5 L 17 5 L 19 11 M 3 11 L 21 11 L 21 18 L 18 18 L 18 16 L 6 16 L 6 18 L 3 18 Z M 7 13.5 A 1.5 1.5 0 0 1 7 16.5 M 17 13.5 A 1.5 1.5 0 0 1 17 16.5',
        'accessories' => 'M 12 2 L 14.5 8.5 L 21 9 L 16 13.5 L 17.5 20 L 12 16.5 L 6.5 20 L 8 13.5 L 3 9 L 9.5 8.5 Z',
    ];
    $defaultIcon = 'M 12 2 L 2 7 L 12 12 L 22 7 Z M 2 17 L 12 22 L 22 17 M 2 12 L 12 17 L 22 12';

    $tree = $categories ?? app(\App\Services\Gazu\MegaMenuBuilder::class)->build();
    $cats = [];
    if (! empty($tree) && is_array($tree)) {
        foreach (array_slice($tree, 0, 8) as $i => $node) {
            $slug = $node['slug'] ?? null;
            $children = $node['children'] ?? [];
            $cats[] = [
                'name'   => $node['label'] ?? '—',
                'count'  => $node['count'] ?? 0,
                'icon'   => $iconBySlug[$slug] ?? $defaultIcon,
                'accent' => $accents[$i % count($accents)],
                'url'    => $slug ? url('/'.$slug) : route('gazu.catalog'),
                'subs'   => array_slice(array_map(fn ($c) => [
                    'label' => $c['label'] ?? '',
                    'url'   => isset($c['slug']) ? url('/'.$c['slug']) : '#',
                ], is_array($children) ? $children : []), 0, 4),
            ];
        }
    }
    if (empty($cats)) {
        $cats = [
            ['name' => 'Двигун', 'count' => 8420, 'icon' => $iconBySlug['engine'], 'accent' => $accents[0], 'url' => route('gazu.catalog'), 'subs' => []],
            ['name' => 'Гальма', 'count' => 3180, 'icon' => $iconBySlug['brakes'], 'accent' => $accents[1], 'url' => route('gazu.catalog'), 'subs' => []],
            ['name' => 'Підвіска', 'count' => 4920, 'icon' => $iconBySlug['suspension'], 'accent' => $accents[2], 'url' => route('gazu.catalog'), 'subs' => []],
            ['name' => 'Електрика', 'count' => 6210, 'icon' => $iconBySlug['electrics'], 'accent' => $accents[3], 'url' => route('gazu.catalog'), 'subs' => []],
            ['name' => 'Трансмісія', 'count' => 2840, 'icon' => $iconBySlug['transmission'], 'accent' => $accents[4], 'url' => route('gazu.catalog'), 'subs' => []],
            ['name' => 'Мастила', 'count' => 1560, 'icon' => $iconBySlug['fluids'], 'accent' => $accents[5], 'url' => route('gazu.catalog'), 'subs' => []],
            ['name' => 'Кузов', 'count' => 980, 'icon' => $iconBySlug['body'], 'accent' => $accents[6], 'url' => route('gazu.catalog'), 'subs' => []],
            ['name' => 'Аксесуари', 'count' => 420, 'icon' => $iconBySlug['accessories'], 'accent' => $accents[7], 'url' => route('gazu.catalog'), 'subs' => []],
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

                {{-- Subtle dot pattern overlay (carbon-fiber feel) --}}
                <div class="absolute inset-0 opacity-[0.08]"
                     style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 12px 12px;"></div>

                {{-- Big glyph in corner — auto-parts iconic look --}}
                <svg class="absolute -bottom-4 -right-4 w-32 h-32 sm:w-40 sm:h-40 text-white/15 group-hover:text-white/25 transition-colors"
                     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
                    <path d="{{ $c['icon'] }}"/>
                </svg>

                {{-- Top-left: count chip --}}
                <div class="absolute top-3 left-3 px-2 py-1 bg-white/15 backdrop-blur rounded-md text-[10px] gazu-mono tracking-wider uppercase">
                    {{ number_format($c['count'], 0, '.', ' ') }} {{ \plural_uk_count($c['count'], 'товар', 'товари', 'товарів') }}
                </div>

                {{-- Bottom: title + subcategories slide-up on hover --}}
                <div class="absolute inset-x-0 bottom-0 p-4">
                    <div class="gazu-display text-[18px] sm:text-[22px] font-semibold leading-tight mb-1">{{ $c['name'] }}</div>

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
