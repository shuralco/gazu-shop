@php
    $active = $active ?? 'orders';
    $user = $user ?? auth()->user();
    $name = $user?->name ?: 'Гість';
    $phone = $user?->phone ?: $user?->email ?: '—';
@endphp
<aside class="bg-white border border-[var(--gazu-line)] rounded-lg p-5">
    <div class="flex items-center gap-3 mb-4 pb-4 border-b border-[var(--gazu-line)]">
        <div class="w-12 h-12 bg-[var(--gazu-mist)] rounded-full flex items-center justify-center text-[var(--gazu-blue)] uppercase font-bold gazu-display">
            {{ mb_substr($name, 0, 1) }}
        </div>
        <div class="min-w-0">
            <div class="font-semibold text-[var(--gazu-ink)] truncate">{{ $name }}</div>
            <div class="text-xs text-[var(--gazu-graphite)] gazu-mono truncate">{{ $phone }}</div>
        </div>
    </div>
    @php
        $navItems = [
            ['orders', 'Мої замовлення', 'box', route('gazu.account')],
        ];
        if (module('gazu_garage')->enabled()) {
            $navItems[] = ['garage', 'Гараж · мої авто', 'car', route('gazu.garage')];
        }
        $navItems = array_merge($navItems, [
            ['favs', 'Обране', 'heart', route('gazu.wishlist')],
        ]);
        // Loyalty tab only when the module is on (otherwise it's a dead link).
        if (function_exists('module') && module('loyalty')->enabled()) {
            $navItems[] = ['loyalty', 'Бонусна програма', 'shield', route('gazu.account')];
        }
    @endphp
    <nav class="flex flex-col gap-0.5">
        @foreach($navItems as [$k, $l, $ic, $url])
            <a wire:navigate href="{{ $url }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded text-sm no-underline {{ $active === $k ? 'bg-[var(--gazu-paper)] text-[var(--gazu-ink)] font-medium' : 'text-[var(--gazu-graphite)]' }}"
               style="border-left: 3px solid {{ $active === $k ? 'var(--gazu-blue)' : 'transparent' }};">
                <x-gazu.icon name="{{ $ic }}" size="18"/>
                {{ $l }}
            </a>
        @endforeach

        @auth
            <form action="{{ route('gazu.auth.logout') }}" method="POST" class="mt-2 border-t border-[var(--gazu-line)] pt-4">
                @csrf
                <button type="submit" class="flex items-center gap-3 px-3 py-2.5 rounded text-sm w-full text-left bg-transparent border-0 cursor-pointer text-[var(--gazu-graphite)] hover:text-[var(--gazu-danger)]">
                    <x-gazu.icon name="arrow-l" size="18"/> Вийти
                </button>
            </form>
        @endauth
    </nav>
</aside>
