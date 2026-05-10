@php $active = $active ?? 'home'; @endphp
<nav class="gazu-mobile-nav max-w-[420px] mx-auto">
    @foreach([
        ['home', 'Головна', 'home', route('gazu.home')],
        ['catalog', 'Каталог', 'grid', route('gazu.catalog')],
        ['vin', 'VIN', 'shield', route('gazu.vin')],
        ['cart', 'Кошик', 'cart', route('gazu.cart')],
        ['account', 'Профіль', 'user', route('gazu.account')],
    ] as [$k, $l, $ic, $url])
        <a wire:navigate href="{{ $url }}" class="flex flex-col items-center justify-center py-2.5 text-[10px] no-underline {{ $active === $k ? 'text-[var(--gazu-ink)]' : 'text-[var(--gazu-graphite)]' }}">
            <x-gazu.icon name="{{ $ic }}" size="20"/>
            <span class="mt-0.5">{{ $l }}</span>
        </a>
    @endforeach
</nav>
