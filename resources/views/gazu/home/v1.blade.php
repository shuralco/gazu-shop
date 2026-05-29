@extends('gazu.layout')

@section('title', 'GAZU — пошук автозапчастин за артикулом')

@section('content')
    @hookAction('layout.home.top')
    {{-- Hero — селектор як primary CTA справа. Зліва — тексти + бенефіти + CTA. --}}
    <section class="py-10 sm:py-14" style="background: linear-gradient(180deg, var(--gazu-mist) 0%, var(--gazu-paper) 100%);">
        <div class="gazu-container gazu-grid-hero-vin">
            <div>
                @php
                    $s = $gazuSettings ?? [];
                    $heroSubtitle = $s['gazu_hero_subtitle'] ?? 'Запчастини для китайських авто';
                    $heroTitle1 = $s['gazu_hero_title_1'] ?? 'Підбір по авто';
                    $heroTitle2Html = $s['gazu_hero_title_2_html'] ?? 'за <span style="color:var(--gazu-blue)">марку</span> і двигун.';
                @endphp
                <div class="gazu-mono text-[11px] text-[var(--gazu-blue)] tracking-widest uppercase mb-3.5">{{ $heroSubtitle }}</div>
                <h1 class="gazu-display font-semibold text-[var(--gazu-ink)] m-0" style="font-size: clamp(28px, 5.2vw, 52px); line-height: 1.05; letter-spacing: -0.03em; overflow-wrap: anywhere; max-width: 100%;">
                    {{ $heroTitle1 }}<br>{!! $heroTitle2Html !!}
                </h1>
                <p class="text-[15px] sm:text-[16px] text-[var(--gazu-graphite)] leading-relaxed mt-5 max-w-md">
                    {{ $s['gazu_hero_description'] ?? 'BYD, Chery, Geely, Haval, Great Wall, JAC, MG, VW. У наявності 1278+ оригінальних запчастин і перевірених аналогів. Доставка 1-3 дні по Україні.' }}
                </p>

                {{-- Primary CTA buttons --}}
                <div class="flex flex-wrap gap-3 mt-6">
                    <a wire:navigate href="{{ route('gazu.catalog') }}" class="inline-flex items-center gap-2 px-5 py-3 bg-[var(--gazu-ink)] text-white rounded-md text-[14px] font-semibold no-underline hover:bg-[var(--gazu-ink-2)] transition-colors">
                        Дивитись каталог
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </a>
                    <a href="tel:0800751024" class="inline-flex items-center gap-2 px-5 py-3 bg-white text-[var(--gazu-ink)] rounded-md text-[14px] font-semibold no-underline shadow-[inset_0_0_0_1px_var(--gazu-line)] hover:shadow-[inset_0_0_0_1px_var(--gazu-ink)] transition-shadow">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        0 800 75 10 24
                    </a>
                </div>

                {{-- Trust badges --}}
                <div class="flex flex-wrap gap-x-5 gap-y-2 mt-6 text-[12px] sm:text-[13px] text-[var(--gazu-graphite)]">
                    <span class="inline-flex gap-1.5 items-center"><x-gazu.icon name="check" size="14" stroke="var(--gazu-success)"/> Без передоплати</span>
                    <span class="inline-flex gap-1.5 items-center"><x-gazu.icon name="check" size="14" stroke="var(--gazu-success)"/> Гарантія 12+ міс.</span>
                    <span class="inline-flex gap-1.5 items-center"><x-gazu.icon name="check" size="14" stroke="var(--gazu-success)"/> Повернення 14 днів</span>
                </div>
            </div>

            {{-- Right side: car-selector (primary feature) — server-rendered initial brands --}}
            <div>
                <x-gazu.car-selector variant="hero" :initial-makes="$heroMakes ?? []"/>
            </div>
        </div>
    </section>

    <x-gazu.trust-strip/>
    <x-gazu.category-tiles/>

    @if(isset($promoProducts) && $promoProducts->isNotEmpty())
        <x-gazu.featured-row title="Акції тижня" :items="$promoProducts" :viewAll="route('gazu.catalog.promo')"/>
    @endif

    @if(isset($newProducts) && $newProducts->isNotEmpty())
        <x-gazu.featured-row title="Новинки" :items="$newProducts" :viewAll="route('gazu.catalog.new')"/>
    @endif

    <x-gazu.featured-row title="Хіти продажів" :items="$popular" :viewAll="route('gazu.catalog.hits')"/>

    <x-gazu.recently-viewed/>

    <x-gazu.brand-strip/>

    <x-gazu.seo-text/>

    @hookAction('layout.home.bottom')
@endsection
