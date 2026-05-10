@extends('gazu.layout')
@section('title', 'Обране — GAZU')

@section('content')
<div class="gazu-container">
    <x-gazu.breadcrumbs :items="[['Головна', route('gazu.home')], 'Обране']"/>
    <h1 class="gazu-display text-4xl font-semibold m-0 mb-2">Обране</h1>
    <p class="text-sm text-[var(--gazu-graphite)] mb-7">{{ plural_uk_count($items->count(), 'товар', 'товари', 'товарів') }}</p>

    @if(session('flash_message'))
        <div class="bg-[var(--gazu-success-bg)] text-[var(--gazu-success)] px-4 py-2 rounded-md mb-4 text-sm">
            {{ session('flash_message') }}
        </div>
    @endif

    @if($items->isEmpty())
        <div class="bg-white border border-[var(--gazu-line)] rounded-lg p-10 text-center">
            <div class="inline-flex w-16 h-16 bg-[var(--gazu-mist)] rounded-full items-center justify-center mb-4 text-[var(--gazu-blue)]">
                <x-gazu.icon name="heart" size="28"/>
            </div>
            <div class="gazu-display text-xl font-semibold mb-2">
                @auth Поки що нічого в обраному @else Увійдіть, щоб переглянути обране @endauth
            </div>
            <p class="text-sm text-[var(--gazu-graphite)] mb-4">Натискайте ♥ на картках товарів, щоб зберегти їх сюди.</p>
            <a wire:navigate href="{{ auth()->check() ? route('gazu.catalog') : route('gazu.auth') }}" class="gazu-btn-primary no-underline">
                @auth До каталогу @else Увійти @endauth
            </a>
        </div>
    @else
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3.5">
            @foreach($items as $p)
                <x-gazu.product-card :p="$p" :compact="true"/>
            @endforeach
        </div>
    @endif
</div>
@endsection
