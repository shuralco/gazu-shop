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
        @foreach($brands as $b)
            <a wire:navigate href="{{ route('gazu.brand', ['slug' => \Str::slug($b)]) }}"
               class="bg-white border border-[var(--gazu-line)] rounded-lg flex items-center justify-center gazu-display text-lg font-semibold text-[var(--gazu-ink)] no-underline hover:border-[var(--gazu-line-2)]"
               style="aspect-ratio: 5/2;">{{ $b }}</a>
        @endforeach
    </div>
</section>
