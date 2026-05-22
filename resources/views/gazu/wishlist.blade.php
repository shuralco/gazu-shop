@extends('gazu.layout')
@section('title', 'Обране — GAZU')

@section('content')
<div class="gazu-container">
    <x-gazu.breadcrumbs :items="[['Головна', route('gazu.home')], 'Обране']"/>
    <h1 class="gazu-display text-2xl sm:text-3xl md:text-4xl font-semibold m-0 mb-2">Обране</h1>
    @auth<p class="text-sm text-[var(--gazu-graphite)] mb-7">{{ plural_uk_count($items->count(), 'товар', 'товари', 'товарів') }}</p>@endauth

    @if(session('flash_message'))
        <div class="bg-[var(--gazu-success-bg)] text-[var(--gazu-success)] px-4 py-2 rounded-md mb-4 text-sm">
            {{ session('flash_message') }}
        </div>
    @endif

    @guest
        {{-- Гість: показуємо скільки збережено локально + CTA на вхід.
             count підставляється з localStorage через Alpine. --}}
        <div x-data="{ count: 0 }"
             x-init="$nextTick(() => { try { count = (JSON.parse(localStorage.getItem('gazu_wishlist')||'[]')||[]).length; } catch(e){} })">
            <div class="bg-white border border-[var(--gazu-line)] rounded-lg p-10 text-center">
                <div class="inline-flex w-16 h-16 bg-[var(--gazu-mist)] rounded-full items-center justify-center mb-4 text-[var(--gazu-blue)]">
                    <x-gazu.icon name="heart" size="28"/>
                </div>
                <template x-if="count > 0">
                    <div>
                        <div class="gazu-display text-xl font-semibold mb-2">
                            У вас <span x-text="count"></span> <span x-text="count === 1 ? 'збережений товар' : (count < 5 ? 'збережені товари' : 'збережених товарів')"></span>
                        </div>
                        <p class="text-sm text-[var(--gazu-graphite)] mb-5 max-w-md mx-auto">Увійдіть або створіть акаунт за 30 секунд — і ваше обране буде доступне на будь-якому пристрої.</p>
                        <a wire:navigate href="{{ route('gazu.auth') }}" class="gazu-btn-primary no-underline">Увійти, щоб переглянути →</a>
                    </div>
                </template>
                <template x-if="count === 0">
                    <div>
                        <div class="gazu-display text-xl font-semibold mb-2">Поки що нічого в обраному</div>
                        <p class="text-sm text-[var(--gazu-graphite)] mb-5">Натискайте ♥ на картках товарів — можна без реєстрації.</p>
                        <a wire:navigate href="{{ route('gazu.catalog') }}" class="gazu-btn-primary no-underline">До каталогу</a>
                    </div>
                </template>
            </div>
        </div>
    @endguest

    @auth
    @if($items->isEmpty())
        <div class="bg-white border border-[var(--gazu-line)] rounded-lg p-10 text-center">
            <div class="inline-flex w-16 h-16 bg-[var(--gazu-mist)] rounded-full items-center justify-center mb-4 text-[var(--gazu-blue)]">
                <x-gazu.icon name="heart" size="28"/>
            </div>
            <div class="gazu-display text-xl font-semibold mb-2">Поки що нічого в обраному</div>
            <p class="text-sm text-[var(--gazu-graphite)] mb-4">Натискайте ♥ на картках товарів, щоб зберегти їх сюди.</p>
            <a wire:navigate href="{{ route('gazu.catalog') }}" class="gazu-btn-primary no-underline">До каталогу</a>
        </div>
    @else
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3.5">
            @foreach($items as $p)
                <x-gazu.product-card :p="$p" :compact="true"/>
            @endforeach
        </div>
    @endif
    @endauth
</div>
@endsection
