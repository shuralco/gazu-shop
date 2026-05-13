@extends('gazu.layout')

@section('title', ($p->name ?? 'Товар') . ' · sticky-buy — GAZU')

@php
    $name = is_object($p) ? ($p->name ?? '') : ($p['name'] ?? '');
    $oem = is_object($p) ? ($p->oem ?? '') : ($p['oem'] ?? '');
    $brand = is_object($p) ? ($p->brand ?? '') : ($p['brand'] ?? '');
    $kind = is_object($p) ? ($p->image_kind ?? 'filter') : ($p['image_kind'] ?? 'filter');
    $price = is_object($p) ? (float)($p->price ?? 0) : (float)($p['price'] ?? 0);
    $oldPrice = is_object($p) ? ($p->old_price ?? null) : ($p['old_price'] ?? null);
    $discount = is_object($p) ? ($p->discount ?? null) : ($p['discount'] ?? null);
    $qty = is_object($p) ? (int)($p->qty ?? 0) : (int)($p['qty'] ?? 0);

    $rawSpecs = is_object($p) ? ($p->specifications ?? null) : ($p['specifications'] ?? null);
    if (is_array($rawSpecs) && ! empty($rawSpecs)) {
        $specs = [];
        foreach ($rawSpecs as $k => $v) {
            $isMono = preg_match('/^\d|[\.,×]|^[A-Z]\d/', (string) $v);
            $specs[] = [(string) $k, (string) $v, (bool) $isMono];
        }
    } else {
        $specs = [
            ['Виробник', $brand ?: '—', false],
            ['Артикул', $oem ?: '—', true],
            ['Стан', 'Новий', false],
            ['Гарантія', '12 місяців', false],
        ];
    }
    $half = (int) ceil(count($specs) / 2);
@endphp

@section('content')
    <div style="max-width: 1100px; margin-inline: auto; padding-inline: 24px; padding-bottom: 80px;">
        @include('gazu.partials.product-breadcrumbs', ['p' => $p, 'brand' => $brand, 'oem' => $oem, 'name' => $name, 'skipHome' => true])

        <div class="grid lg:grid-cols-2 gap-10">
            <div class="grid grid-cols-[60px_1fr] gap-3">
                <div class="flex flex-col gap-2">
                    @for($i = 0; $i < 4; $i++)
                        <div class="aspect-square bg-[var(--gazu-paper)] rounded-md flex items-center justify-center cursor-pointer" style="border: 1.5px solid {{ $i === 0 ? 'var(--gazu-ink)' : 'var(--gazu-line)' }};">
                            <x-gazu.part-image kind="{{ $kind }}" size="42"/>
                        </div>
                    @endfor
                </div>
                <div class="aspect-square bg-white border border-[var(--gazu-line)] rounded-[10px] relative overflow-hidden">
                    <div class="absolute inset-0 flex items-center justify-center">
                        <x-gazu.part-image kind="{{ $kind }}" size="400"/>
                    </div>
                </div>
            </div>

            <div>
                <div class="flex items-center gap-2.5 mb-2">
                    <x-gazu.condition-badge value="Новий"/>
                    <span class="gazu-display font-semibold text-[var(--gazu-ink)]">{{ $brand }}</span>
                </div>
                <h1 class="gazu-display text-[28px] font-semibold text-[var(--gazu-ink)] m-0 mb-1.5 leading-tight">{{ $name }}</h1>
                @php $soldV3 = is_object($p) ? (int) ($p->sold_count ?? 0) : 0; @endphp
                @if($oem || $soldV3 > 0)
                    <div class="text-[13px] text-[var(--gazu-graphite)] gazu-mono mb-3.5">
                        @if($oem)OEM {{ $oem }}@endif
                        @if($oem && $soldV3 > 0) · @endif
                        @if($soldV3 > 0){{ $soldV3 }} продано@endif
                    </div>
                @endif
                <div class="flex items-baseline gap-3 mb-3.5">
                    <span class="gazu-display font-bold text-[var(--gazu-ink)]" style="font-size: 36px; letter-spacing: -0.03em;">{{ number_format($price, 0, '.', ' ') }} ₴</span>
                    @if($oldPrice)
                        <span class="text-sm text-[var(--gazu-muted)] line-through">{{ number_format((float)$oldPrice, 0, '.', ' ') }} ₴</span>
                        @if($discount)
                            <span class="text-[11px] gazu-mono px-1.5 py-0.5 bg-[var(--gazu-danger-bg)] text-[var(--gazu-danger)] rounded">−{{ $discount }}%</span>
                        @endif
                    @endif
                </div>
                <x-gazu.stock qty="{{ $qty }}"/>
                @if(module('gazu_garage')->enabled())
                    @php $primaryCar = auth()->check() ? auth()->user()->primaryCar : null; @endphp
                    @if($primaryCar)
                        <div class="mt-4.5 p-3.5 bg-[var(--gazu-success-bg)] rounded-lg flex gap-2.5">
                            <x-gazu.icon name="check" size="18" stroke="var(--gazu-success)"/>
                            <div class="text-[13px] text-[var(--gazu-ink)]">
                                Підходить для <span class="font-semibold">{{ $primaryCar->display_name }}@if($primaryCar->engine), {{ $primaryCar->engine }}@endif</span>
                            </div>
                        </div>
                    @endif
                @endif
                <div class="mt-4.5 gazu-display text-base font-semibold mb-2.5">Опис</div>
                <p class="text-sm text-[var(--gazu-graphite)] leading-relaxed m-0">
                    Накручуваний масляний фільтр з фільтруючим елементом з целюлозного волокна.
                    Перепускний клапан запобігає відсутності змащування при холодному пуску. Зворотний клапан утримує масло у фільтрі після зупинки двигуна.
                </p>
                <div class="mt-5 grid grid-cols-2 gap-2 text-xs">
                    @foreach([
                        ['truck', 'Доставка завтра'],
                        ['shield', 'Гарантія 12 міс'],
                        ['return', 'Повернення 14 днів'],
                        ['box', 'Оригінальна упаковка'],
                    ] as [$ic, $t])
                        <div class="flex gap-2 items-center px-2.5 py-2 bg-white border border-[var(--gazu-line)] rounded-md text-[var(--gazu-graphite)]">
                            <span class="text-[var(--gazu-blue)]"><x-gazu.icon name="{{ $ic }}" size="14"/></span>{{ $t }}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        @include('gazu.partials.product-tabs', ['active' => 'spec'])
        <div class="mt-5 grid lg:grid-cols-2 gap-6">
            <div class="bg-white border border-[var(--gazu-line)] rounded-lg px-4">
                @foreach(array_slice($specs, 0, $half) as [$k, $v, $mono])
                    <div class="grid grid-cols-2 py-2.5 border-b border-[var(--gazu-line)] last:border-b-0 text-[13px]">
                        <span class="text-[var(--gazu-graphite)]">{{ $k }}</span>
                        <span class="text-[var(--gazu-ink)] {{ $mono ? 'gazu-mono font-medium' : '' }}">{{ $v }}</span>
                    </div>
                @endforeach
            </div>
            <div class="bg-white border border-[var(--gazu-line)] rounded-lg px-4">
                @foreach(array_slice($specs, $half) as [$k, $v, $mono])
                    <div class="grid grid-cols-2 py-2.5 border-b border-[var(--gazu-line)] last:border-b-0 text-[13px]">
                        <span class="text-[var(--gazu-graphite)]">{{ $k }}</span>
                        <span class="text-[var(--gazu-ink)] {{ $mono ? 'gazu-mono font-medium' : '' }}">{{ $v }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Sticky buy bar --}}
    <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-[var(--gazu-line)] px-6 py-3.5 flex items-center gap-4 z-10" style="box-shadow: 0 -4px 16px rgba(14,27,44,0.06);">
        <div class="w-11 h-11 bg-[var(--gazu-paper)] rounded-md flex items-center justify-center shrink-0">
            <x-gazu.part-image kind="{{ $kind }}" size="36"/>
        </div>
        <div class="min-w-0 flex-1 hidden md:block">
            <div class="text-[13px] font-medium text-[var(--gazu-ink)] truncate">{{ $name }}</div>
            <div class="text-[11px] text-[var(--gazu-graphite)]"><x-gazu.stock qty="{{ $qty }}"/></div>
        </div>
        <div class="gazu-display font-bold text-[var(--gazu-ink)]" style="font-size: 24px; letter-spacing: -0.02em;">{{ number_format($price, 0, '.', ' ') }} ₴</div>
        <div class="flex items-center border border-[var(--gazu-line)] rounded-md">
            <button type="button" class="w-9 h-10 border-0 bg-transparent text-[var(--gazu-ink)] cursor-pointer inline-flex items-center justify-center"><x-gazu.icon name="minus" size="14"/></button>
            <input value="1" class="w-11 text-center border-0 py-2.5 text-sm gazu-mono font-medium outline-none">
            <button type="button" class="w-9 h-10 border-0 bg-transparent text-[var(--gazu-ink)] cursor-pointer inline-flex items-center justify-center"><x-gazu.icon name="plus" size="14"/></button>
        </div>
        <button type="button" class="px-5 py-3 bg-[var(--gazu-ink)] text-white border-0 rounded-md font-medium text-sm cursor-pointer inline-flex items-center gap-2">
            <x-gazu.icon name="cart" size="16"/> <span class="hidden sm:inline">У кошик · {{ number_format($price, 0, '.', ' ') }} ₴</span>
        </button>
    </div>
@endsection
