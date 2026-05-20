@php
    $activeNav = $activeNav ?? 'catalog';
    $cartCount = $cartCount ?? 0;
    $megaOpen = $megaOpen ?? false;
@endphp
<header class="bg-white border-b border-[var(--gazu-line)] relative font-text"
        x-data="{ megaOpen: false }"
        @keydown.escape.window="megaOpen = false">
    @include('gazu.partials.topbar')

    {{-- flex-wrap so the search box drops to its own full-width row below `lg`
         instead of being crushed to ~30px between the logo and action icons. --}}
    <div class="gazu-container py-3 sm:py-4 flex flex-wrap items-center gap-x-3 sm:gap-x-4 gap-y-3 lg:flex-nowrap lg:gap-5">
        <a wire:navigate href="{{ route('gazu.home') }}" class="no-underline shrink-0 inline-flex items-center">
            <x-gazu.logo size="26"/>
        </a>

        {{-- Catalog mega-button — square 40x40 icon-only on mobile, label from sm: up --}}
        <button type="button"
                @click="megaOpen = !megaOpen"
                :aria-label="megaOpen ? 'Закрити каталог' : 'Відкрити каталог'"
                :class="megaOpen ? 'bg-[var(--gazu-blue)]' : 'bg-[var(--gazu-ink)]'"
                class="inline-flex items-center justify-center gap-2 w-10 h-10 sm:w-auto sm:h-auto sm:px-4 sm:py-2.5 text-white border-0 rounded-lg text-sm font-medium shrink-0 cursor-pointer transition-colors hover:opacity-90">
            <x-gazu.icon name="menu" size="18"/>
            <span class="hidden sm:inline">Каталог</span>
        </button>

        {{-- Search bar — артикул / категорія / бренд з live autocomplete.
             Own full-width row below `lg`; inline flex-1 from `lg` up. --}}
        <div class="order-last w-full lg:order-none lg:w-auto lg:flex-1 min-w-0 relative"
             x-data="{
                q: @js(request('q', '')),
                items: [],
                total: 0,
                open: false,
                loading: false,
                timer: null,
                voiceSupported: false,
                listening: false,
                _rec: null,
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
                },
                initVoice() {
                    this.voiceSupported = 'SpeechRecognition' in window || 'webkitSpeechRecognition' in window;
                },
                voice() {
                    if (!this.voiceSupported) return;
                    if (this.listening) { this._rec?.stop(); return; }
                    const R = window.SpeechRecognition || window.webkitSpeechRecognition;
                    const r = new R();
                    r.lang = 'uk-UA';
                    r.interimResults = true;
                    r.continuous = false;
                    r.onstart  = () => { this.listening = true; };
                    r.onend    = () => { this.listening = false; this._rec = null; };
                    r.onerror  = () => { this.listening = false; };
                    r.onresult = (e) => {
                        const t = Array.from(e.results).map(x => x[0].transcript).join('').trim();
                        this.q = t;
                        if (e.results[e.results.length - 1].isFinal) {
                            this.onInput();
                        }
                    };
                    this._rec = r;
                    try { r.start(); } catch(e) { this.listening = false; }
                }
             }"
             x-init="initVoice()"
             @click.outside="open = false">
            <form action="{{ route('gazu.search') }}" method="GET" class="flex items-stretch border-[1.5px] border-[var(--gazu-ink)] rounded-lg overflow-hidden bg-white">
                <input name="q" placeholder="Назва категорії, бренд або деталь — напр. оливний фільтр, Bosch, амортизатор"
                       x-model="q" @input="onInput" @focus="if (items.length) open = true"
                       class="flex-1 min-w-0 border-0 outline-none px-3.5 py-2.5 text-sm text-[var(--gazu-ink)]"
                       autocomplete="off">
                {{-- Voice input — Web Speech API. Hidden when the browser doesn't support it. --}}
                <button type="button" @click="voice()" x-show="voiceSupported" x-cloak
                        :aria-pressed="listening"
                        :title="listening ? 'Зупинити запис' : 'Голосовий пошук'"
                        :class="listening ? 'text-[var(--gazu-danger)] bg-[var(--gazu-danger-bg)]' : 'text-[var(--gazu-graphite)] bg-white hover:bg-[var(--gazu-paper)]'"
                        class="border-0 border-l border-[var(--gazu-line)] px-3 cursor-pointer inline-flex items-center justify-center shrink-0 transition-colors">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                         :class="listening ? 'animate-pulse' : ''"><path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 1 0 6 0V5a3 3 0 0 0-3-3z"/><path d="M19 10v2a7 7 0 1 1-14 0v-2"/><line x1="12" y1="19" x2="12" y2="23"/><line x1="8" y1="23" x2="16" y2="23"/></svg>
                </button>
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
            <div class="hidden lg:flex flex-col items-start gap-1 shrink-0">
                <a href="tel:{{ preg_replace('/\s+/', '', $phone) }}" class="no-underline">
                    <div class="text-[15px] font-bold text-[var(--gazu-ink)] gazu-display whitespace-nowrap leading-none">{{ $phone }}</div>
                </a>
                <x-gazu.callback-popover variant="link" source="header" align="left"/>
            </div>
        @endif

        {{-- Actions — compact on mobile (9×9), full from sm: up (11×11).
             ml-auto pushes them to the right edge of row 1 on mobile. --}}
        <div class="flex items-center gap-1 shrink-0 ml-auto lg:ml-0">
            <a wire:navigate href="{{ route('gazu.wishlist') }}" title="Обране" aria-label="Список обраних товарів" class="w-9 h-9 sm:w-11 sm:h-11 inline-flex items-center justify-center bg-white text-[var(--gazu-ink)] border border-[var(--gazu-line)] rounded-lg cursor-pointer relative">
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
               class="w-9 h-9 sm:w-11 sm:h-11 inline-flex items-center justify-center bg-white text-[var(--gazu-ink)] border border-[var(--gazu-line)] rounded-lg cursor-pointer relative">
                <x-gazu.icon name="user" size="20"/>
                @auth
                    <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 bg-[var(--gazu-success)] rounded-full border-2 border-white"></span>
                @endauth
            </a>
            <a wire:navigate href="{{ route('gazu.cart') }}"
               data-gazu-cart-icon
               x-data="{ count: {{ (int) $cartCount }} }"
               x-on:cart-updated.window="count = $event.detail.count"
               class="w-9 h-9 sm:w-11 sm:h-11 inline-flex items-center justify-center bg-[var(--gazu-ink)] text-white border border-[var(--gazu-ink)] rounded-lg cursor-pointer relative">
                <x-gazu.icon name="cart" size="20"/>
                <span x-show="count > 0" x-cloak
                      class="absolute -top-1 -right-1 bg-[var(--gazu-blue)] text-white rounded-full min-w-[18px] h-[18px] text-[11px] font-semibold flex items-center justify-center px-1"
                      x-text="count">{{ $cartCount }}</span>
            </a>
        </div>
    </div>

    {{-- Secondary nav — hidden on mobile/tablet (the same links live inside
         the catalog mega-menu at the top of its mobile accordion, opened via
         the ☰ button). Shows from lg up. --}}
    <div class="hidden lg:block border-t border-[var(--gazu-line)] bg-[var(--gazu-paper)]">
        @php
            // Sub-nav: admin-editable (gazu_subnav) із fallback на дефолтні маршрути.
            $subnavSetting = $gazuSettings['gazu_subnav'] ?? null;
            if (is_array($subnavSetting) && ! empty($subnavSetting)) {
                $subnav = collect($subnavSetting)
                    ->map(fn ($i) => [
                        'k'     => $i['key'] ?? \Illuminate\Support\Str::slug($i['label'] ?? ''),
                        'label' => $i['label'] ?? '',
                        'url'   => $i['url'] ?? '#',
                    ])
                    ->filter(fn ($i) => $i['label'] !== '')
                    ->all();
            } else {
                $subnav = [
                    ['k' => 'promo',  'label' => 'Акції',   'url' => route('gazu.catalog.promo')],
                    ['k' => 'hits',   'label' => 'Хіти',    'url' => route('gazu.catalog.hits')],
                    ['k' => 'new',    'label' => 'Новинки', 'url' => route('gazu.catalog.new')],
                    ['k' => 'brands', 'label' => 'Бренди',  'url' => route('gazu.brand')],
                    ['k' => 'blog',   'label' => 'Блог',    'url' => route('gazu.blog')],
                ];
            }
        @endphp
        <div class="gazu-container px-6 flex items-center gap-0.5 text-[13px] whitespace-nowrap overflow-x-auto">
            @foreach($subnav as $item)
                <a wire:navigate href="{{ $item['url'] }}"
                   class="px-3.5 py-3.5 no-underline {{ $activeNav === $item['k'] ? 'text-[var(--gazu-ink)] font-medium' : 'text-[var(--gazu-graphite)]' }}"
                   style="border-bottom: 2px solid {{ $activeNav === $item['k'] ? 'var(--gazu-blue)' : 'transparent' }};">{{ $item['label'] }}</a>
            @endforeach
            <span class="flex-1"></span>
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
            {{-- Popover: near-fullscreen sheet on mobile, centred 1280px popover on desktop.
                 The mobile shell pins top/bottom so the mega-menu body scrolls inside it
                 instead of overflowing the viewport. --}}
            <div class="fixed z-[56] left-2 right-2 top-2 bottom-2
                        lg:left-1/2 lg:right-auto lg:top-[105px] lg:bottom-auto
                        lg:-translate-x-1/2 lg:w-[min(1280px,calc(100vw-48px))]"
                 @click.outside="megaOpen = false">
                @include('gazu.partials.mega-menu', ['activeMega' => 'engine'])
            </div>
        </div>
    </template>
</header>
