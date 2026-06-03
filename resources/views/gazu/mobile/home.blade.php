@extends('gazu.layout')
@section('title', 'GAZU mobile · home')

@php
    $s = $gazuSettings ?? [];
    $kicker = $s['gazu_mobile_hero_kicker'] ?? ($shopStats['products_label'] ?? 'Каталог автозапчастин');
    $titleHtml = $s['gazu_mobile_hero_title_html'] ?? 'Знайди деталь за <span style="color:var(--gazu-blue)">Артикул</span>';
    $catsTitle = $s['gazu_mobile_categories_title'] ?? 'Категорії';
    $hitsTitle = $s['gazu_mobile_hits_title'] ?? 'Хіти';

    // Реальні корінні категорії (перші 4) з тих самих даних, що мега-меню
    $tree = app(\App\Services\Gazu\MegaMenuBuilder::class)->build();
    $kinds = ['oil','pad','shock','spark','bulb','filter','bearing','wiper'];
    $mobCats = [];
    foreach (array_slice((array) $tree, 0, 4) as $i => $node) {
        $mobCats[] = [
            'name' => $node['label'] ?? '—',
            'kind' => $kinds[$i % count($kinds)],
            'url'  => ! empty($node['slug']) ? url('/'.$node['slug']) : route('gazu.catalog'),
        ];
    }
@endphp

@section('content')
<div class="max-w-[420px] mx-auto py-4 px-4 pb-20">
    <div class="bg-[var(--gazu-mist)] rounded-xl p-5 mb-4">
        <div class="gazu-mono text-[10px] text-[var(--gazu-blue)] tracking-widest uppercase mb-2">{{ $kicker }}</div>
        <h1 class="gazu-display text-2xl font-bold leading-tight m-0">{!! $titleHtml !!}</h1>
        <form action="{{ route('gazu.search') }}" class="mt-4 flex bg-[var(--gazu-surface)] border border-[var(--gazu-ink)] rounded-md overflow-hidden">
            <input name="q" placeholder="Введіть код" class="flex-1 px-3 py-2.5 border-0 outline-none gazu-mono text-sm">
            <button type="submit" class="px-4 bg-[var(--gazu-ink)] text-[var(--gazu-on-brand)] border-0 cursor-pointer">
                <x-gazu.icon name="search" size="16"/>
            </button>
        </form>
    </div>

    @if(! empty($mobCats))
        <h2 class="gazu-display text-lg font-semibold mb-2">{{ $catsTitle }}</h2>
        <div class="grid grid-cols-2 gap-2 mb-5">
            @foreach($mobCats as $c)
                <a wire:navigate href="{{ $c['url'] }}" class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg p-3 no-underline text-[var(--gazu-ink)] flex items-center gap-2">
                    <div class="w-10 h-10 bg-[var(--gazu-paper)] rounded-md flex items-center justify-center">
                        <x-gazu.part-image kind="{{ $c['kind'] }}" size="32"/>
                    </div>
                    <span class="font-medium text-sm">{{ $c['name'] }}</span>
                </a>
            @endforeach
        </div>
    @endif

    <h2 class="gazu-display text-lg font-semibold mb-2">{{ $hitsTitle }}</h2>
    <div class="grid grid-cols-2 gap-2.5">
        @foreach($products->take(4) as $p)
            <x-gazu.product-card :p="$p" :compact="true"/>
        @endforeach
    </div>
</div>

@include('gazu.partials.mobile-nav', ['active' => 'home'])
@endsection
