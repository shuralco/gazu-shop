@extends('gazu.layout')
@section('title', 'СТО та послуги — GAZU')

@section('content')
@php
    $s = $gazuSettings ?? [];
    $heroTitle = $s['gazu_sto_intro_title'] ?? 'СТО та послуги';
    $heroDesc = $s['gazu_sto_intro_desc'] ?? 'Ми не лише продаємо запчастини — у нас власна мережа партнерських СТО з гарантією на роботи та фіксованими цінами.';
    $services = $s['gazu_sto_services'] ?? [];
    $partners = $s['gazu_sto_partners'] ?? [];
@endphp
<div class="gazu-container">
    <x-gazu.breadcrumbs :items="[['Головна', route('gazu.home')], 'СТО та послуги']"/>
    <section class="bg-[var(--gazu-ink)] text-[var(--gazu-on-brand)] rounded-xl p-10 mb-7">
        <h1 class="gazu-display text-4xl font-semibold m-0 mb-2">{{ $heroTitle }}</h1>
        <p class="text-base text-[#9DA5B2] m-0 max-w-xl">{{ $heroDesc }}</p>
    </section>

    @if(! empty($services))
        <div class="grid md:grid-cols-3 gap-4 mb-7">
            @foreach($services as $svc)
                <div class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg p-5">
                    <div class="w-12 h-12 bg-[var(--gazu-mist)] rounded-md flex items-center justify-center text-[var(--gazu-blue)] mb-4">
                        <x-gazu.icon name="{{ $svc['icon'] ?? 'wrench' }}" size="24"/>
                    </div>
                    <h3 class="gazu-display text-lg font-semibold m-0 mb-1">{{ $svc['title'] ?? '' }}</h3>
                    <div class="gazu-mono text-sm text-[var(--gazu-blue)] mb-2">{{ $svc['price'] ?? '' }}</div>
                    <p class="text-sm text-[var(--gazu-graphite)] m-0">{{ $svc['desc'] ?? '' }}</p>
                </div>
            @endforeach
        </div>
    @endif

    @if(! empty($partners))
        <section class="bg-[var(--gazu-mist)] rounded-xl p-7">
            <h2 class="gazu-display text-2xl font-semibold m-0 mb-4">Наші партнери СТО</h2>
            <div class="grid md:grid-cols-2 gap-3">
                @foreach($partners as $p)
                    <div class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg p-4 flex items-start gap-3">
                        <div class="w-10 h-10 bg-[var(--gazu-paper)] rounded-md flex items-center justify-center text-[var(--gazu-blue)]">
                            <x-gazu.icon name="location" size="20"/>
                        </div>
                        <div class="flex-1">
                            <div class="font-medium text-[var(--gazu-ink)]">{{ $p['name'] ?? '' }}</div>
                            <div class="text-xs text-[var(--gazu-graphite)]">{{ $p['addr'] ?? '' }}</div>
                            @if(! empty($p['rating']))
                                <div class="text-xs text-[var(--gazu-warn)] mt-1">★ {{ $p['rating'] }}</div>
                            @endif
                        </div>
                        <button type="button" class="gazu-btn-outline text-xs py-2 px-3">Записатись</button>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection
