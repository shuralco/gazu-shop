@props(['brands' => null])
@php
    // 1) Якщо явно передано — беремо. 2) Інакше з композера (gazu views). 3) Інакше з БД напряму.
    if (! $brands) {
        $brands = $brands ?? null;
        try {
            $live = \App\Models\Brand::query()
                ->when(\Schema::hasColumn('brands', 'is_active'), fn ($q) => $q->where('is_active', true))
                ->orderBy('sort_order')
                ->orderBy('name')
                ->limit(12)
                ->pluck('name')
                ->all();
            if (! empty($live)) $brands = $live;
        } catch (\Throwable) {}
    }
    if (empty($brands)) {
        $brands = ['Bosch', 'Mahle', 'TRW', 'KYB', 'NGK', 'FAG', 'Osram', 'Mobil', 'Mann', 'Sachs', 'Lemförder', 'ATE'];
    }
    // Normalize: composer тепер passes [name, slug] dicts. Handle обидва формати.
    $brandList = collect($brands)->map(function ($b) {
        if (is_array($b) && isset($b['slug'])) {
            $bn = $b['name'] ?? '';
            if (is_array($bn)) $bn = $bn['uk'] ?? array_values($bn)[0] ?? '';
            return ['name' => (string) $bn, 'slug' => (string) $b['slug']];
        }
        $name = is_array($b) ? ($b['uk'] ?? array_values($b)[0] ?? '') : (string) $b;
        return ['name' => (string) $name, 'slug' => \Illuminate\Support\Str::slug((string) $name)];
    })->filter(fn ($b) => $b['name'] && $b['slug'])->values()->all();
@endphp
<section class="gazu-container py-10">
    <div class="flex items-baseline justify-between mb-5">
        <h2 class="gazu-display text-[28px] font-semibold text-[var(--gazu-ink)] m-0">{{ $gazuSettings['gazu_section_brands'] ?? 'Топ-бренди' }}</h2>
        @php
            $brandsLabel = $shopStats['brands_label'] ?? 'усі бренди';
        @endphp
        <a wire:navigate href="{{ route('gazu.brand') }}" class="text-[13px] text-[var(--gazu-blue)] no-underline">Усі {{ $brandsLabel }} →</a>
    </div>
    <div class="grid grid-cols-3 md:grid-cols-6 gap-2.5">
        @foreach($brandList as $b)
            <a wire:navigate href="{{ route('gazu.brand', ['slug' => $b['slug']]) }}"
               class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg flex items-center justify-center gazu-display text-lg font-semibold text-[var(--gazu-ink)] no-underline hover:border-[var(--gazu-line-2)]"
               style="aspect-ratio: 5/2;">{{ $b['name'] }}</a>
        @endforeach
    </div>
</section>
