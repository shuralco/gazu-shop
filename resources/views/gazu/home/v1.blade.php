@extends('gazu.layout')

@section('title', 'GAZU — пошук автозапчастин за артикулом')

@section('content')
    {{-- Hero — Артикул-first --}}
    <section class="py-12" style="background: linear-gradient(180deg, var(--gazu-mist) 0%, var(--gazu-paper) 100%);">
        <div class="gazu-container gazu-grid-hero-vin">
            <div>
                @php
                    $s = $gazuSettings ?? [];
                    $heroSubtitle = $s['gazu_hero_subtitle'] ?? 'Магазин автозапчастин'.(isset($shopStats['products_label']) ? ' · '.$shopStats['products_label'] : '');
                    $heroTitle1 = $s['gazu_hero_title_1'] ?? 'Знайди потрібну деталь';
                    $heroTitle2Html = $s['gazu_hero_title_2_html'] ?? 'за <span style="color:var(--gazu-blue)">артикулом</span> або назвою.';
                    $heroDescription = $s['gazu_hero_description'] ?? sprintf(
                        'Точний підбір з оригінальних каталогів. %s в Україні, доставка 1–3 дні, гарантія на кожну позицію.',
                        $shopStats['warehouses_label'] ?? 'власні склади'
                    );
                @endphp
                <div class="gazu-mono text-[11px] text-[var(--gazu-blue)] tracking-widest uppercase mb-3.5">{{ $heroSubtitle }}</div>
                <h1 class="gazu-display font-semibold text-[var(--gazu-ink)] m-0" style="font-size: clamp(26px, 6.4vw, 56px); line-height: 1.08; letter-spacing: -0.03em; overflow-wrap: anywhere; max-width: 100%;">
                    {{ $heroTitle1 }}<br>{!! $heroTitle2Html !!}
                </h1>
                <p class="text-base text-[var(--gazu-graphite)] leading-relaxed mt-4 max-w-lg">{{ $heroDescription }}</p>

                {{-- Search --}}
                <div class="mt-7 bg-white rounded-[10px] border border-[var(--gazu-line)] overflow-hidden" style="box-shadow: var(--gazu-shadow-2);">
                    <div class="p-4">
                        <form action="{{ route('gazu.search') }}" method="GET" class="flex gap-2">
                            <input name="q" placeholder="Введіть артикул або назву деталі"
                                   class="flex-1 px-4 py-3.5 gazu-mono text-[15px] border border-[var(--gazu-line)] rounded-md outline-none">
                            <button type="submit" class="px-6 bg-[var(--gazu-ink)] text-white border-0 rounded-md font-medium text-sm cursor-pointer inline-flex items-center gap-2">
                                <x-gazu.icon name="search" size="16"/> Пошук
                            </button>
                        </form>
                        <div class="mt-2.5 text-xs text-[var(--gazu-graphite)]">
                            Приклади: <span class="gazu-mono text-[var(--gazu-ink)]">06A 115 561 B</span> · <span class="gazu-mono text-[var(--gazu-ink)]">1K0 407 151 BC</span>
                        </div>
                    </div>
                </div>

                {{-- 4C: hero car-selector — підбір по марці/моделі/двигуну.
                     Альтернатива до search-by-article, працює як «не знаю артикул — обери авто». --}}
                <div class="mt-5">
                    <x-gazu.car-selector variant="hero"/>
                </div>

                <div class="flex flex-wrap gap-6 mt-5 text-xs text-[var(--gazu-graphite)]">
                    <span class="inline-flex gap-1.5 items-center"><x-gazu.icon name="check" size="14" stroke="var(--gazu-success)"/> Без передоплати</span>
                    <span class="inline-flex gap-1.5 items-center"><x-gazu.icon name="check" size="14" stroke="var(--gazu-success)"/> Гарантія 12+ міс.</span>
                    <span class="inline-flex gap-1.5 items-center"><x-gazu.icon name="check" size="14" stroke="var(--gazu-success)"/> Повернення 14 днів</span>
                </div>
            </div>

            {{-- Visual: top product from $featured (or admin override). --}}
            @php
                $topProd = isset($featured) ? collect($featured)->first() : null;
                $vKind = $gazuSettings['gazu_hero_visual_image_kind']
                    ?? (is_object($topProd) ? ($topProd->image_kind ?? 'bearing') : 'bearing');
                $vOem = $gazuSettings['gazu_hero_visual_oem_code']
                    ?? (is_object($topProd) && $topProd->oem ? 'OEM ' . $topProd->oem : null);
                $vTitle = $gazuSettings['gazu_hero_visual_title']
                    ?? (is_object($topProd) ? ($topProd->name ?? null) : null);
                $vSubtitle = $gazuSettings['gazu_hero_visual_subtitle']
                    ?? (is_object($topProd) ? ($topProd->oem ?? '') : '');
                $vPrice = $gazuSettings['gazu_hero_visual_price']
                    ?? (is_object($topProd) && $topProd->price ? number_format($topProd->price, 0, '.', ' ') . ' ₴' : null);
                $vUrl = is_object($topProd) ? ($topProd->url ?? null) : null;
            @endphp
            @php $heroTag = $vUrl ? 'a' : 'div'; @endphp
            <{{ $heroTag }} @if($vUrl) wire:navigate href="{{ $vUrl }}" @endif
               class="bg-white rounded-xl border border-[var(--gazu-line)] relative overflow-hidden no-underline {{ $vUrl ? 'cursor-pointer transition-all hover:border-[var(--gazu-ink)] hover:shadow-[0_8px_24px_-12px_rgba(14,27,44,0.25)]' : '' }} block"
               style="aspect-ratio: 4/3;">
                <div class="absolute inset-0 gazu-grid-pattern"></div>
                <div class="absolute inset-0 flex items-center justify-center">
                    <x-gazu.part-image kind="{{ $vKind }}" size="280"/>
                </div>
                @if($vOem)
                    <div class="absolute top-4 left-4 px-2.5 py-1.5 bg-[var(--gazu-ink)] text-white gazu-mono text-[11px] tracking-wider rounded">{{ $vOem }}</div>
                @endif
                @if($vTitle || $vSubtitle || $vPrice)
                    <div class="absolute bottom-4 left-4 right-4 p-3 bg-white/95 rounded-lg border border-[var(--gazu-line)] text-xs">
                        @if($vTitle)<div class="text-[var(--gazu-graphite)] line-clamp-1">{{ $vTitle }}</div>@endif
                        @if($vSubtitle || $vPrice)
                            <div class="flex justify-between items-baseline mt-1">
                                <span class="gazu-mono text-[var(--gazu-muted)]">{{ $vSubtitle }}</span>
                                <span class="gazu-display font-bold text-[var(--gazu-ink)]">{{ $vPrice }}</span>
                            </div>
                        @endif
                    </div>
                @endif
            </{{ $heroTag }}>
        </div>
    </section>

    <x-gazu.trust-strip/>
    <x-gazu.category-tiles/>
    @php
        $promoItems = (isset($featured) ? collect($featured) : collect())->filter(fn ($p) => is_object($p) && ! empty($p->old_price) && $p->old_price > ($p->price ?? 0))->values();
    @endphp
    @if($promoItems->isNotEmpty())
        <x-gazu.featured-row title="Акції тижня" :items="$promoItems" :viewAll="route('gazu.catalog', ['promo' => 1])"/>
    @endif
    <x-gazu.featured-row title="Хіти продажів" :items="$popular" :viewAll="route('gazu.catalog', ['hits' => 1])"/>
    <x-gazu.brand-strip/>
@endsection
