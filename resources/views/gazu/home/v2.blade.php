@extends('gazu.layout')

@section('title', 'GAZU — підбір за маркою-моделлю-роком')

@section('content')
    <section class="py-15 relative overflow-hidden bg-[var(--gazu-ink)] text-white" style="padding-top:60px;padding-bottom:60px">
        <div class="absolute inset-0 gazu-grid-pattern-dark"></div>
        <div class="gazu-container relative">
            <div class="gazu-grid-hero-picker">
                @php
                    $s = $gazuSettings ?? [];
                    $kicker = $s['gazu_hero_v2_kicker'] ?? 'Підбір за вашим авто';
                    $title = $s['gazu_hero_v2_title'] ?? "Запчастини, які\nточно підійдуть.";
                    $desc = $s['gazu_hero_v2_description'] ?? 'Оберіть марку, модель та рік випуску — побачите тільки сумісні деталі. Без помилок і повернень.';
                    $brands = $s['gazu_hero_v2_brands'] ?? ['VW', 'Audi', 'BMW', 'Skoda', 'Toyota', 'Renault', 'Ford', 'Hyundai'];
                    $brandsTotal = $s['gazu_hero_v2_brands_total'] ?? 240;
                @endphp
                <div>
                    <div class="gazu-mono text-[11px] text-[var(--gazu-azure)] tracking-widest uppercase mb-3.5">{{ $kicker }}</div>
                    <h1 class="gazu-display font-semibold m-0" style="font-size: 60px; line-height: 1.0; letter-spacing: -0.04em;">
                        {!! nl2br(e($title)) !!}
                    </h1>
                    <p class="text-base text-[#9DA5B2] leading-relaxed mt-5 max-w-md">{{ $desc }}</p>
                </div>
                <div class="bg-white text-[var(--gazu-ink)] rounded-xl p-6">
                    <div class="flex items-center gap-1.5 mb-4.5 text-[11px] gazu-mono tracking-widest uppercase text-[var(--gazu-graphite)]">
                        <span class="text-[var(--gazu-blue)]">Крок 1 з 4</span>
                        <span class="flex-1 h-0.5 bg-[var(--gazu-line)] rounded relative">
                            <span class="absolute left-0 h-full bg-[var(--gazu-blue)] rounded" style="width: 25%"></span>
                        </span>
                    </div>

                    <div class="mb-5">
                        <label class="text-xs text-[var(--gazu-graphite)] mb-2 block">Оберіть марку</label>
                        <div class="grid grid-cols-4 gap-2">
                            @foreach((array) $brands as $i => $b)
                                <button type="button"
                                        class="py-3 px-2 gazu-display font-semibold text-[13px] border-[1.5px] rounded-md cursor-pointer {{ $i === 0 ? 'bg-[var(--gazu-ink)] text-white border-[var(--gazu-ink)]' : 'bg-white text-[var(--gazu-ink)] border-[var(--gazu-line)]' }}">{{ $b }}</button>
                            @endforeach
                        </div>
                        <a wire:navigate href="{{ route('gazu.brand') }}" class="inline-block bg-transparent border-0 text-[var(--gazu-blue)] text-xs pt-2.5 cursor-pointer no-underline">Усі {{ $brandsTotal }} марок →</a>
                    </div>

                    <div class="grid grid-cols-2 gap-2.5">
                        <div class="px-3.5 py-3 border border-[var(--gazu-line)] rounded-md bg-[var(--gazu-paper)]">
                            <div class="text-[11px] text-[var(--gazu-graphite)]">Модель</div>
                            <div class="text-sm text-[var(--gazu-muted)] mt-0.5">Спочатку марка</div>
                        </div>
                        <div class="px-3.5 py-3 border border-[var(--gazu-line)] rounded-md bg-[var(--gazu-paper)]">
                            <div class="text-[11px] text-[var(--gazu-graphite)]">Рік</div>
                            <div class="text-sm text-[var(--gazu-muted)] mt-0.5">—</div>
                        </div>
                    </div>

                    <div class="flex gap-3 mt-4 px-3.5 py-3 bg-[var(--gazu-mist)] rounded-lg text-xs text-[var(--gazu-graphite)]">
                        <x-gazu.icon name="shield" size="16" stroke="var(--gazu-blue)"/>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <x-gazu.trust-strip/>
    <x-gazu.category-tiles/>
    <x-gazu.featured-row title="Хіти для VW Passat B8" :items="$featured"/>
    <x-gazu.featured-row title="Сезонне: підготовка до зими" :items="$popular"/>
    <x-gazu.brand-strip/>
@endsection
