@php
    $activeNav = $activeNav ?? 'catalog';
    $cartCount = $cartCount ?? 0;
    $megaOpen = $megaOpen ?? false;
@endphp
<header class="bg-white border-b border-[var(--gazu-line)] relative font-text"
        x-data="{ megaOpen: false }"
        @keydown.escape.window="megaOpen = false">
    @include('gazu.partials.topbar')

    <div class="gazu-container py-4 flex items-center gap-5">
        <a wire:navigate href="{{ route('gazu.home') }}" class="no-underline">
            <x-gazu.logo size="26"/>
        </a>

        {{-- Catalog mega-button --}}
        <button type="button"
                @click="megaOpen = !megaOpen"
                :class="megaOpen ? 'bg-[var(--gazu-blue)]' : 'bg-[var(--gazu-ink)]'"
                class="inline-flex items-center gap-2 px-4 py-2.5 text-white border-0 rounded-lg text-sm font-medium shrink-0 cursor-pointer transition-colors">
            <x-gazu.icon name="menu" size="18"/> Каталог
        </button>

        {{-- Search bar — OEM/VIN з live autocomplete --}}
        <div class="flex-1 min-w-0 relative"
             x-data="{
                q: @js(request('q', '')),
                items: [],
                total: 0,
                open: false,
                loading: false,
                timer: null,
                async fetch() {
                    if (this.q.length < 2) { this.items = []; this.open = false; return; }
                    this.loading = true;
                    try {
                        const r = await window.fetch('{{ route('gazu.search.suggest') }}?q=' + encodeURIComponent(this.q));
                        const d = await r.json();
                        this.items = d.items || [];
                        this.total = d.total || 0;
                        this.open = true;
                    } catch(e) { this.items = []; this.open = false; }
                    finally { this.loading = false; }
                },
                onInput() {
                    clearTimeout(this.timer);
                    this.timer = setTimeout(() => this.fetch(), 250);
                }
             }"
             @click.outside="open = false">
            <form action="{{ route('gazu.search') }}" method="GET" class="flex border-[1.5px] border-[var(--gazu-ink)] rounded-lg overflow-hidden bg-white">
                <div class="flex items-center gap-1.5 px-3.5 border-r border-[var(--gazu-line)] text-[var(--gazu-graphite)] text-[13px] cursor-pointer shrink-0 select-none">
                    <span>OEM</span>
                    <x-gazu.icon name="chevron" size="14"/>
                </div>
                <input name="q" placeholder="Введіть OEM-код, наприклад 06A 115 561 B"
                       x-model="q" @input="onInput" @focus="if (items.length) open = true"
                       class="flex-1 min-w-0 border-0 outline-none px-3.5 py-2.5 text-sm text-[var(--gazu-ink)]"
                       style="font-family: var(--gazu-font-mono);"
                       autocomplete="off">
                <button type="submit" class="border-0 bg-[var(--gazu-ink)] text-white px-4 cursor-pointer inline-flex items-center gap-1.5 text-sm shrink-0">
                    <x-gazu.icon name="search" size="16"/> <span class="hidden sm:inline">Знайти</span>
                </button>
            </form>

            {{-- Suggest dropdown --}}
            <div x-show="open && (items.length || loading)" x-cloak x-transition.opacity
                 class="absolute top-full left-0 right-0 mt-2 bg-white border border-[var(--gazu-line)] rounded-lg shadow-2xl z-50 overflow-hidden max-h-[80vh] overflow-y-auto">
                <template x-if="loading && !items.length">
                    <div class="p-4 text-center text-sm text-[var(--gazu-graphite)]">Шукаю…</div>
                </template>
                <template x-for="item in items" :key="item.id">
                    <a wire:navigate :href="item.url" class="flex items-center gap-3 px-3 py-2.5 hover:bg-[var(--gazu-paper)] no-underline border-b border-[var(--gazu-line)] last:border-b-0">
                        <div class="w-10 h-10 bg-[var(--gazu-paper)] rounded shrink-0 flex items-center justify-center text-[10px] gazu-mono text-[var(--gazu-muted)]" x-text="item.image_kind"></div>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium text-[var(--gazu-ink)] truncate" x-text="item.title"></div>
                            <div class="text-[11px] text-[var(--gazu-graphite)] gazu-mono truncate">
                                <span x-text="item.manufacturer"></span><span x-show="item.manufacturer && item.sku"> · </span><span x-text="item.sku"></span>
                            </div>
                        </div>
                        <div class="gazu-display font-bold text-sm text-[var(--gazu-ink)] whitespace-nowrap">
                            <span x-text="item.price_formatted"></span> ₴
                        </div>
                    </a>
                </template>
                <template x-if="total > items.length">
                    <a wire:navigate :href="`{{ route('gazu.search') }}?q=${encodeURIComponent(q)}`"
                       class="block px-3 py-2.5 text-center bg-[var(--gazu-paper)] text-sm text-[var(--gazu-blue)] no-underline hover:bg-[var(--gazu-mist)]">
                        Усі <span x-text="total"></span> результатів →
                    </a>
                </template>
            </div>
        </div>

        {{-- Phone --}}
        @php
            $phone = $gazuSettings['gazu_phone'] ?? '0 800 75 10 24';
            $phoneSubtitle = $gazuSettings['gazu_phone_subtitle'] ?? 'безкоштовно по Україні';
        @endphp
        @if($phone)
            <a href="tel:{{ preg_replace('/\s+/', '', $phone) }}" class="hidden lg:flex flex-col items-start gap-px shrink-0 no-underline">
                <div class="text-[15px] font-bold text-[var(--gazu-ink)] gazu-display whitespace-nowrap">{{ $phone }}</div>
                <div class="text-[11px] text-[var(--gazu-graphite)] whitespace-nowrap">{{ $phoneSubtitle }}</div>
            </a>
        @endif

        {{-- Actions --}}
        <div class="flex items-center gap-1 shrink-0">
            <a wire:navigate href="{{ route('gazu.wishlist') }}" title="Обране" class="w-11 h-11 inline-flex items-center justify-center bg-white text-[var(--gazu-ink)] border border-[var(--gazu-line)] rounded-lg cursor-pointer relative">
                <x-gazu.icon name="heart" size="20"/>
                @auth
                    @php $wlc = \DB::table('wishlists')->where('user_id', auth()->id())->count(); @endphp
                    @if($wlc > 0)
                        <span class="absolute -top-1 -right-1 bg-[var(--gazu-danger)] text-white rounded-full min-w-[18px] h-[18px] text-[11px] font-semibold flex items-center justify-center px-1">{{ $wlc }}</span>
                    @endif
                @endauth
            </a>
            <a wire:navigate href="{{ auth()->check() ? route('gazu.account') : route('gazu.auth') }}"
               title="{{ auth()->check() ? auth()->user()->name : 'Вхід / Реєстрація' }}"
               class="w-11 h-11 inline-flex items-center justify-center bg-white text-[var(--gazu-ink)] border border-[var(--gazu-line)] rounded-lg cursor-pointer relative">
                <x-gazu.icon name="user" size="20"/>
                @auth
                    <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 bg-[var(--gazu-success)] rounded-full border-2 border-white"></span>
                @endauth
            </a>
            <a wire:navigate href="{{ route('gazu.cart') }}"
               data-gazu-cart-icon
               x-data="{ count: {{ (int) $cartCount }} }"
               x-on:cart-updated.window="count = $event.detail.count"
               class="w-11 h-11 inline-flex items-center justify-center bg-[var(--gazu-ink)] text-white border border-[var(--gazu-ink)] rounded-lg cursor-pointer relative">
                <x-gazu.icon name="cart" size="20"/>
                <span x-show="count > 0" x-cloak
                      class="absolute -top-1 -right-1 bg-[var(--gazu-blue)] text-white rounded-full min-w-[18px] h-[18px] text-[11px] font-semibold flex items-center justify-center px-1"
                      x-text="count">{{ $cartCount }}</span>
            </a>
        </div>
    </div>

    {{-- Secondary nav --}}
    <div class="border-t border-[var(--gazu-line)] bg-[var(--gazu-paper)]">
        <div class="gazu-container px-6 flex items-center gap-0.5 text-[13px] whitespace-nowrap overflow-x-auto">
            @foreach([
                ['vin', 'Пошук за VIN', route('gazu.vin')],
                ['compat', 'Підбір за авто', route('gazu.vin')],
                ['promo', 'Акції', route('gazu.catalog', ['promo' => 1])],
                ['hits', 'Хіти', route('gazu.catalog', ['hits' => 1])],
                ['new', 'Новинки', route('gazu.catalog', ['new' => 1])],
                ['brands', 'Бренди', route('gazu.brand')],
                ['sto', 'СТО та послуги', route('gazu.sto')],
                ['blog', 'Блог', route('gazu.blog')],
            ] as [$k, $label, $url])
                <a wire:navigate href="{{ $url }}"
                   class="px-3.5 py-3.5 no-underline {{ $activeNav === $k ? 'text-[var(--gazu-ink)] font-medium' : 'text-[var(--gazu-graphite)]' }}"
                   style="border-bottom: 2px solid {{ $activeNav === $k ? 'var(--gazu-blue)' : 'transparent' }};">{{ $label }}</a>
            @endforeach
            <span class="flex-1"></span>
            @php $totalSku = $gazuSettings['gazu_total_sku'] ?? 50000; @endphp
            <span class="gazu-mono text-[11px] text-[var(--gazu-muted)] tracking-widest uppercase">{{ number_format((int) $totalSku, 0, '.', ' ') }}+ SKU</span>
        </div>
    </div>

    {{-- Mega menu (hidden by default; toggled via Alpine x-show) --}}
    <template x-teleport="body">
        <div x-show="megaOpen"
             x-transition.opacity.duration.150ms
             style="display: none;">
            {{-- Dim overlay covers ENTIRE page (positioned fixed under body) --}}
            <div class="fixed inset-0 bg-black/45 z-[55] cursor-pointer"
                 @click="megaOpen = false"></div>
            {{-- Popover positioned just under the header --}}
            <div class="fixed top-[105px] left-1/2 -translate-x-1/2 z-[56] w-[min(1280px,calc(100vw-48px))]"
                 @click.outside="megaOpen = false">
                @include('gazu.partials.mega-menu', ['activeMega' => 'engine'])
            </div>
        </div>
    </template>
</header>
