@props(['categories' => null])
@php
    // 1) Accent-кольори з admin-settings, інакше defaults
    $accentsRaw = ($gazuSettings ?? null)['gazu_category_accents'] ?? null;
    if (is_array($accentsRaw) && ! empty($accentsRaw)) {
        // Repeater::simple зберігає кожен елемент як ['color' => '...']; нормалізуємо
        $accents = array_map(fn ($x) => is_array($x) ? ($x['color'] ?? null) : $x, $accentsRaw);
        $accents = array_values(array_filter($accents));
    }
    if (empty($accents)) {
        $accents = ['var(--gazu-blue)', 'var(--gazu-danger)', 'var(--gazu-steel)', 'var(--gazu-warn)', 'var(--gazu-azure)', 'var(--gazu-success)'];
    }
    $kinds = ['oil','pad','shock','spark','bulb','filter','bearing','wiper'];
    $tree = $categories ?? app(\App\Services\Gazu\MegaMenuBuilder::class)->build();
    $cats = [];
    if (! empty($tree) && is_array($tree)) {
        foreach (array_slice($tree, 0, 8) as $i => $node) {
            $slug = $node['slug'] ?? null;
            $cats[] = [
                'name'   => $node['label'] ?? '—',
                'count'  => $node['count'] ?? 0,
                'kind'   => $kinds[$i % count($kinds)],
                'accent' => $accents[$i % count($accents)],
                'url'    => $slug ? route('gazu.catalog', ['cat' => $slug]) : route('gazu.catalog'),
            ];
        }
    }
    // 2) Fallback — статика
    if (empty($cats)) {
        $cats = [
            ['name' => 'Двигун', 'count' => 8420, 'kind' => 'oil', 'accent' => 'var(--gazu-blue)', 'url' => route('gazu.catalog')],
            ['name' => 'Гальмівна система', 'count' => 3180, 'kind' => 'pad', 'accent' => 'var(--gazu-danger)', 'url' => route('gazu.catalog')],
            ['name' => 'Підвіска та рульове', 'count' => 4920, 'kind' => 'shock', 'accent' => 'var(--gazu-steel)', 'url' => route('gazu.catalog')],
            ['name' => 'Електрика', 'count' => 6210, 'kind' => 'spark', 'accent' => 'var(--gazu-warn)', 'url' => route('gazu.catalog')],
            ['name' => 'Кузов та оптика', 'count' => 2840, 'kind' => 'bulb', 'accent' => 'var(--gazu-azure)', 'url' => route('gazu.catalog')],
            ['name' => 'Фільтри', 'count' => 1560, 'kind' => 'filter', 'accent' => 'var(--gazu-success)', 'url' => route('gazu.catalog')],
            ['name' => 'Підшипники', 'count' => 980, 'kind' => 'bearing', 'accent' => 'var(--gazu-steel)', 'url' => route('gazu.catalog')],
            ['name' => 'Склоочисники', 'count' => 420, 'kind' => 'wiper', 'accent' => 'var(--gazu-blue)', 'url' => route('gazu.catalog')],
        ];
    }
@endphp
<section class="gazu-container pt-16 pb-6">
    <div class="flex items-baseline justify-between mb-7">
        <h2 class="gazu-display text-4xl font-semibold text-[var(--gazu-ink)] m-0">{{ $gazuSettings['gazu_section_categories'] ?? 'Каталог за категоріями' }}</h2>
        <a href="{{ route('gazu.catalog') }}" class="text-sm text-[var(--gazu-blue)] no-underline inline-flex items-center gap-1.5">Усі категорії <x-gazu.icon name="arrow-r" size="14"/></a>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3.5">
        @foreach($cats as $c)
            <a href="{{ $c['url'] ?? route('gazu.catalog') }}" class="bg-white border border-[var(--gazu-line)] rounded-[10px] p-5 flex flex-col gap-2.5 no-underline text-[var(--gazu-ink)] cursor-pointer relative overflow-hidden hover:border-[var(--gazu-line-2)]">
                <div class="absolute -right-2 -top-2 opacity-10">
                    <x-gazu.part-image kind="{{ $c['kind'] }}" size="120"/>
                </div>
                <div class="w-1 h-7 rounded relative" style="background: {{ $c['accent'] }};"></div>
                <div class="gazu-display text-lg font-semibold">{{ $c['name'] }}</div>
                <div class="gazu-mono text-[11px] text-[var(--gazu-graphite)] tracking-wider">{{ number_format($c['count'], 0, '.', ' ') }} ТОВАРІВ</div>
            </a>
        @endforeach
    </div>
</section>
