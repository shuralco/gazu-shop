@extends('gazu.layout')

@section('title', 'GAZU — для майстрів і водіїв')

@php
    $s = $gazuSettings ?? [];
    $left = [
        'kicker' => $s['gazu_hero_v3_left_kicker'] ?? 'Для майстрів СТО',
        'title' => $s['gazu_hero_v3_left_title'] ?? "Швидкий пошук\nза OEM-кодом",
        'desc'  => $s['gazu_hero_v3_left_description'] ?? sprintf(
            'Прямий доступ до %s. Аналоги і замінники в один клік.',
            $shopStats['products_label'] ?? 'каталогу'
        ),
        'perks' => $s['gazu_hero_v3_left_perks'] ?? ['VIN-декодер', 'Пакетний пошук', 'Гуртові ціни'],
    ];
    $right = [
        'kicker' => $s['gazu_hero_v3_right_kicker'] ?? 'Для водіїв',
        'title' => $s['gazu_hero_v3_right_title'] ?? "Підбір за вашим\nавто",
        'desc'  => $s['gazu_hero_v3_right_description'] ?? 'Марка, модель, рік — і ви побачите тільки сумісні запчастини.',
    ];
@endphp

@section('content')
    <section class="gazu-container py-10">
        <div class="grid lg:grid-cols-2 gap-4">
            <div class="bg-[var(--gazu-ink)] text-white rounded-xl p-9 relative overflow-hidden min-h-[380px]">
                <div class="gazu-mono text-[11px] text-[var(--gazu-azure)] tracking-widest uppercase mb-3.5">{{ $left['kicker'] }}</div>
                <h2 class="gazu-display text-4xl font-semibold leading-tight m-0">{!! nl2br(e($left['title'])) !!}</h2>
                <p class="text-sm text-[#9DA5B2] leading-relaxed mt-3.5">{{ $left['desc'] }}</p>
                <form action="{{ route('gazu.search') }}" method="GET" class="mt-7 flex gap-2">
                    <input name="q" placeholder="06A 115 561 B" class="flex-1 px-4 py-3.5 gazu-mono text-sm border-0 rounded-md outline-none bg-white text-[var(--gazu-ink)]">
                    <button type="submit" class="px-5 bg-[var(--gazu-blue)] text-white border-0 rounded-md font-medium text-sm cursor-pointer">Знайти</button>
                </form>
                <div class="mt-4 flex gap-4 text-xs text-[#9DA5B2] flex-wrap">
                    @foreach((array) $left['perks'] as $perk)
                        <span>· {{ $perk }}</span>
                    @endforeach
                </div>
                <div class="absolute -right-7 -bottom-7 opacity-10">
                    <x-gazu.part-image kind="bearing" size="240"/>
                </div>
            </div>

            <div class="bg-[var(--gazu-mist)] rounded-xl p-9 relative overflow-hidden min-h-[380px]">
                <div class="gazu-mono text-[11px] text-[var(--gazu-blue)] tracking-widest uppercase mb-3.5">{{ $right['kicker'] }}</div>
                <h2 class="gazu-display text-4xl font-semibold leading-tight m-0 text-[var(--gazu-ink)]">{!! nl2br(e($right['title'])) !!}</h2>
                <p class="text-sm text-[var(--gazu-graphite)] leading-relaxed mt-3.5">{{ $right['desc'] }}</p>
                <div class="mt-7 grid grid-cols-3 gap-2">
                    @foreach(['Марка', 'Модель', 'Рік'] as $p)
                        <div class="px-3 py-3.5 bg-white rounded-md border border-[var(--gazu-line)]">
                            <div class="text-[11px] text-[var(--gazu-graphite)] mb-0.5">{{ $p }}</div>
                            <div class="text-[13px] text-[var(--gazu-muted)] flex items-center justify-between">Обрати <x-gazu.icon name="chevron" size="14"/></div>
                        </div>
                    @endforeach
                </div>
                <a wire:navigate href="{{ route('gazu.catalog') }}" class="mt-3 w-full py-3.5 bg-[var(--gazu-ink)] text-white border-0 rounded-md font-medium text-sm cursor-pointer text-center no-underline block">
                    Підібрати запчастини
                </a>
                <div class="absolute -right-5 -bottom-7 opacity-15">
                    <x-gazu.part-image kind="pad" size="220"/>
                </div>
            </div>
        </div>
    </section>

    <x-gazu.trust-strip/>
    <x-gazu.category-tiles/>
    <x-gazu.featured-row :title="$gazuSettings['gazu_section_specials'] ?? 'Новинки каталогу'" :items="$featured"/>
    <x-gazu.vin-block/>
    <x-gazu.brand-strip/>
@endsection
