@php
    $chips = [];
    if ($category ?? null) {
        $chips[] = ['label' => $category->title ?? $category->name, 'remove' => 'cat'];
    }
    if (request()->filled('q')) {
        $chips[] = ['label' => 'Пошук: ' . request('q'), 'remove' => 'q'];
    }
    foreach ((array) request('brand', []) as $b) {
        $chips[] = ['label' => $b, 'remove' => 'brand', 'value' => $b];
    }
    if (request()->filled('min')) {
        $chips[] = ['label' => 'від ' . request('min') . ' ₴', 'remove' => 'min'];
    }
    if (request()->filled('max')) {
        $chips[] = ['label' => 'до ' . request('max') . ' ₴', 'remove' => 'max'];
    }
    if (request('stock') === 'in') {
        $chips[] = ['label' => 'В наявності', 'remove' => 'stock'];
    }
@endphp

@if(!empty($chips))
    <div class="flex flex-wrap gap-2 py-3.5">
        @foreach($chips as $chip)
            @php
                $params = request()->all();
                if ($chip['remove'] === 'brand' && isset($chip['value'])) {
                    $params['brand'] = array_filter((array) ($params['brand'] ?? []), fn ($x) => $x !== $chip['value']);
                    if (empty($params['brand'])) unset($params['brand']);
                } else {
                    unset($params[$chip['remove']]);
                }
                unset($params['page']);
            @endphp
            <a href="{{ url()->current() . (count($params) ? '?' . http_build_query($params) : '') }}"
               class="inline-flex items-center gap-1.5 px-2.5 py-1.5 bg-white border border-[var(--gazu-line)] rounded-2xl text-xs text-[var(--gazu-ink)] no-underline hover:border-[var(--gazu-line-2)]">
                {{ $chip['label'] }} <x-gazu.icon name="close" size="12" stroke="var(--gazu-graphite)"/>
            </a>
        @endforeach
        <a href="{{ url()->current() }}" class="bg-transparent border-0 text-[var(--gazu-danger)] text-xs cursor-pointer px-2.5 py-1.5 no-underline">Очистити все</a>
    </div>
@endif
