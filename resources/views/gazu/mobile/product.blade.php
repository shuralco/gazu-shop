@extends('gazu.layout')
@section('title', 'Товар · mobile')

@section('content')
@php
    $p = $product ?? ($products->first() ?? null);
    $name = is_object($p) ? ($p->name ?? '') : ($p['name'] ?? 'Фільтр масляний');
    $description = is_object($p) ? (is_array($p->excerpt ?? null) ? ($p->excerpt['uk'] ?? '') : ($p->excerpt ?? '')) : '';
    $primaryCar = auth()->check() ? auth()->user()->primaryCar : null;
@endphp
<div class="max-w-[420px] mx-auto pb-32">
    <div class="aspect-square bg-[var(--gazu-surface)] relative">
        <div class="absolute inset-0 flex items-center justify-center">
            <x-gazu.part-image kind="{{ $p->image_kind ?? 'filter' }}" size="280"/>
        </div>
        <div class="absolute top-3 left-3 px-2 py-1 bg-[var(--gazu-surface)] border border-[var(--gazu-line)] gazu-mono text-[10px] tracking-wider rounded">1 / 8</div>
        <button class="absolute top-3 right-3 w-9 h-9 bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-md flex items-center justify-center"><x-gazu.icon name="heart" size="16"/></button>
    </div>

    <div class="p-4">
        <div class="flex items-center gap-2 mb-2"><x-gazu.condition-badge value="Новий"/> <span class="gazu-display font-semibold text-sm">{{ $p->brand ?? '' }}</span></div>
        <h1 class="gazu-display text-lg font-semibold m-0 mb-1">{{ $name }}</h1>
        @if($p->oem ?? null)
            <div class="text-xs text-[var(--gazu-graphite)] gazu-mono mb-3">OEM {{ $p->oem }}</div>
        @endif
        @if($description)
            <p class="text-sm text-[var(--gazu-graphite)] mt-2 mb-3 leading-relaxed">{{ $description }}</p>
        @endif
        <div class="flex items-baseline gap-2 mb-2">
            <span class="gazu-display text-2xl font-bold">{{ number_format((float)($p->price ?? 0), 0, '.', ' ') }} ₴</span>
            @if(!empty($p->old_price))<span class="text-sm text-[var(--gazu-muted)] line-through">{{ number_format((float)$p->old_price, 0, '.', ' ') }} ₴</span>@endif
        </div>
        <x-gazu.stock qty="{{ (int)($p->qty ?? 12) }}"/>

        @if(module('gazu_garage')->enabled() && $primaryCar)
            <div class="mt-3 p-3 bg-[var(--gazu-success-bg)] rounded text-xs flex gap-2">
                <x-gazu.icon name="check" size="14" stroke="var(--gazu-success)"/>
                <span>Підходить для <b>{{ $primaryCar->display_name }}</b>@if($primaryCar->engine), {{ $primaryCar->engine }}@endif</span>
            </div>
        @endif

        <div class="mt-4 grid grid-cols-2 gap-2 text-xs">
            @foreach([['truck','Доставка завтра'],['shield','Гарантія 12 міс'],['return','Повернення 14 днів'],['box','Оригінал']] as [$ic, $t])
                <div class="flex gap-2 items-center px-2.5 py-2 bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded text-[var(--gazu-graphite)]">
                    <span class="text-[var(--gazu-blue)]"><x-gazu.icon name="{{ $ic }}" size="12"/></span>{{ $t }}
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="fixed bottom-12 left-0 right-0 max-w-[420px] mx-auto bg-[var(--gazu-surface)] border-t border-[var(--gazu-line)] p-3 flex gap-2 z-20">
    <button type="button" class="gazu-btn-outline px-3"><x-gazu.icon name="cart" size="18"/></button>
    <button type="button" class="gazu-btn-primary flex-1 py-3">Купити · {{ number_format((float)($p->price ?? 0), 0, '.', ' ') }} ₴</button>
</div>

@include('gazu.partials.mobile-nav', ['active' => 'catalog'])
@endsection
