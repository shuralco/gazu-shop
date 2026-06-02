{{-- GAZU layout — окремий від чинного storefront. Лежить поряд, у resources/views/gazu/. --}}
<!DOCTYPE html>
<html lang="uk" class="gazu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- CSRF token — Spatie ResponseCache CsrfTokenReplacer auto-replaces this
         meta value per-request, тому будь-який кешований HTML має правильний
         токен для активної session. Усі JS-fetch'ери мають читати з нього. --}}
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon.png') }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <meta name="theme-color" content="#0E1B2C">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        // Глобальний токен для всіх fetch() — single source of truth, читається
        // з мета-тега який Spatie ResponseCache оновлює per-request.
        window.GAZU_CSRF = document.querySelector('meta[name=csrf-token]')?.content || '';

        // CSRF Form Auto-Refresh: будь-який <form> з @csrf input (_token) при submit
        // отримує СВІЖИЙ токен з window.GAZU_CSRF. Це fix для кешованого HTML де
        // @csrf видає stale token (Spatie CsrfTokenReplacer оновлює лише meta, не inputs).
        // Capture-phase listener спрацьовує ДО надсилання форми браузером.
        document.addEventListener('submit', function (e) {
            if (!window.GAZU_CSRF) return;
            var form = e.target;
            if (!form || form.tagName !== 'FORM') return;
            var tokenInput = form.querySelector('input[name="_token"]');
            if (tokenInput) tokenInput.value = window.GAZU_CSRF;
        }, true);

        // Wishlist: гість зберігає в localStorage (без авторизації), залогінений —
        // на сервері. Перегляд /wishlist потребує логіну. window.gazuWishlistToggle()
        // — єдина точка для всіх сердечок.
        (function () {
            window.GAZU_AUTH = {{ auth()->check() ? 'true' : 'false' }};
            window.GAZU_WISHLIST_IDS = new Set();
            var LS_KEY = 'gazu_wishlist';

            function lsGet() {
                try { return new Set((JSON.parse(localStorage.getItem(LS_KEY) || '[]') || []).map(Number)); }
                catch (e) { return new Set(); }
            }
            function lsSet(set) {
                try { localStorage.setItem(LS_KEY, JSON.stringify([...set])); } catch (e) {}
            }
            function emit() {
                window.dispatchEvent(new CustomEvent('gazu:wishlist-ids-loaded'));
                window.dispatchEvent(new CustomEvent('gazu:wishlist-changed', { detail: { count: window.GAZU_WISHLIST_IDS.size } }));
            }

            function loadIds() {
                if (window.GAZU_AUTH) {
                    fetch('{{ route('gazu.wishlist.ids') }}', { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
                        .then(function (r) { return r.ok ? r.json() : { ids: [] }; })
                        .then(function (d) {
                            window.GAZU_WISHLIST_IDS = new Set((d.ids || []).map(Number));
                            // Merge будь-яких гостьових айтемів у акаунт (одноразово після логіну).
                            var guest = lsGet();
                            if (guest.size) {
                                fetch('{{ route('gazu.wishlist.merge') }}', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': window.GAZU_CSRF },
                                    body: JSON.stringify({ ids: [...guest] })
                                }).then(function (r) { return r.json(); }).then(function (d) {
                                    if (! d || ! d.ok) { emit(); return; }   // не чистимо localStorage якщо merge не вдався
                                    guest.forEach(function (id) { window.GAZU_WISHLIST_IDS.add(id); });
                                    try { localStorage.removeItem(LS_KEY); } catch (e) {}
                                    // Сторінка /wishlist рендериться сервером ДО merge → reload щоб
                                    // показати щойно перенесені товари.
                                    if (location.pathname.indexOf('wishlist') !== -1 || location.pathname.indexOf('obrane') !== -1) {
                                        location.reload(); return;
                                    }
                                    emit();
                                }).catch(function () { emit(); });
                            } else {
                                emit();
                            }
                        })
                        .catch(function () {});
                } else {
                    window.GAZU_WISHLIST_IDS = lsGet();
                    emit();
                }
            }

            // Єдина точка toggling для всіх сердечок. Повертає Promise<bool inWishlist>.
            window.gazuWishlistToggle = function (pid) {
                pid = Number(pid);
                if (window.GAZU_AUTH) {
                    return fetch('{{ route('gazu.wishlist.toggle') }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': window.GAZU_CSRF, 'Accept': 'application/json' },
                        body: new URLSearchParams({ product_id: String(pid) })
                    }).then(function (r) { return r.json(); }).then(function (d) {
                        if (d.ok) {
                            if (d.in_wishlist) window.GAZU_WISHLIST_IDS.add(pid); else window.GAZU_WISHLIST_IDS.delete(pid);
                            window.dispatchEvent(new CustomEvent('gazu:wishlist-changed', { detail: { count: window.GAZU_WISHLIST_IDS.size } }));
                            window.gazuToast && window.gazuToast(d.in_wishlist ? 'Додано в обране ❤' : 'Видалено з обраного', d.in_wishlist ? 'success' : 'info');
                            return d.in_wishlist;
                        }
                        return window.GAZU_WISHLIST_IDS.has(pid);
                    });
                }
                // Гість — localStorage
                var set = lsGet();
                var added;
                if (set.has(pid)) { set.delete(pid); added = false; } else { set.add(pid); added = true; }
                lsSet(set);
                window.GAZU_WISHLIST_IDS = set;
                window.dispatchEvent(new CustomEvent('gazu:wishlist-changed', { detail: { count: set.size } }));
                if (added) {
                    window.gazuToast && window.gazuToast('Збережено в обране · увійдіть, щоб переглянути', 'info', { action: { label: 'Увійти', href: '{{ route('gazu.auth') }}' } });
                } else {
                    window.gazuToast && window.gazuToast('Видалено з обраного', 'info');
                }
                return Promise.resolve(added);
            };

            if (document.readyState !== 'loading') loadIds();
            else document.addEventListener('DOMContentLoaded', loadIds);
            document.addEventListener('livewire:navigated', loadIds);
        })();
    </script>

    {{-- Performance hints — preconnect до origin + dns-prefetch до zовнішніх сервісів --}}
    <link rel="dns-prefetch" href="//cdn.jsdelivr.net">
    <link rel="preconnect" href="https://gazu.uno" crossorigin>
    @php
        $metaProductsLabel = $shopStats['products_label'] ?? 'широкий каталог';
        $pageTitle = trim(($__env->yieldContent('title') ?: 'GAZU — каталог автозапчастин'));
        $pageDescription = trim(($__env->yieldContent('description') ?: 'Інтернет-магазин автозапчастин · '.$metaProductsLabel.' · доставка по Україні'));
        $canonical = url()->current();
    @endphp
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    @if((bool) (\App\Models\DisplaySetting::get('seo_noindex_all', true)))
        {{-- Site-wide no-index for staging/презентаційний домен --}}
        <meta name="robots" content="noindex,nofollow">
    @else
        <link rel="canonical" href="{{ $canonical }}">
    @endif

    {{-- OpenGraph / Twitter — for share previews --}}
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:site_name" content="GAZU">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:locale" content="uk_UA">
    @hasSection('og_image')
        <meta property="og:image" content="@yield('og_image')">
        <meta name="twitter:image" content="@yield('og_image')">
    @else
        <meta property="og:image" content="{{ url('/og-default.svg') }}">
        <meta name="twitter:image" content="{{ url('/og-default.svg') }}">
    @endif

    <style>
        /* Mobile: cap width to viewport. We deliberately do NOT set
           `overflow-x: hidden/clip` here — it breaks `position: sticky` for
           descendants in WebKit/Chromium. The earlier layout fixes (grid
           `min-width: 0` children, font-size capping etc.) keep horizontal
           overflow under control without needing this safety net. */
        html, body.gazu { max-width: 100vw; }
        @media (max-width: 640px) {
            .gazu-container { padding-left: 16px; padding-right: 16px; }
        }
    </style>
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDescription }}">

    @yield('jsonld')

    {{-- Global JSON-LD: Organization + WebSite з SearchAction (sitelinks search box у Google) --}}
    @php
        $orgLd = [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'Organization',
                    '@id' => url('/').'#organization',
                    'name' => 'GAZU',
                    'url' => url('/'),
                    'logo' => url('/og-default.svg'),
                    'sameAs' => array_values(array_filter([
                        $gazuSettings['gazu_social_facebook'] ?? null,
                        $gazuSettings['gazu_social_instagram'] ?? null,
                        $gazuSettings['gazu_social_telegram'] ?? null,
                    ])),
                ],
                [
                    '@type' => 'WebSite',
                    '@id' => url('/').'#website',
                    'url' => url('/'),
                    'name' => 'GAZU',
                    'inLanguage' => 'uk-UA',
                    'publisher' => ['@id' => url('/').'#organization'],
                    'potentialAction' => [
                        '@type' => 'SearchAction',
                        'target' => [
                            '@type' => 'EntryPoint',
                            'urlTemplate' => url('/search').'?q={search_term_string}',
                        ],
                        'query-input' => 'required name=search_term_string',
                    ],
                ],
            ],
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($orgLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}</script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=Space+Grotesk:wght@400;500;600;700&family=Inter+Tight:wght@400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

    @vite([\App\Support\ThemeManager::cssEntry() ?: 'themes/gazu/resources/css/gazu.css'])
    {{-- Runtime theme tokens: re-skins the storefront to the active theme's
         palette live (no npm build). Must come AFTER @vite so it wins the
         cascade. Empty for a theme without token overrides. --}}
    @php $gazuThemeVars = \App\Support\ThemeManager::cssVarOverrides(); @endphp
    @if($gazuThemeVars)<style id="gazu-theme-vars">{!! $gazuThemeVars !!}</style>@endif
    @livewireStyles
    {{-- Alpine.js is bundled with Livewire 3 (@livewireScripts at end of body).
         Loading alpine-3.14.1.min.js separately created a SECOND instance,
         causing 'Detected multiple instances of Alpine running' + every
         x-on/@click handler firing TWICE = cart fetch + page navigation
         race = ERR_NETWORK_CHANGED + 'page jumping'. --}}
    <script defer src="{{ asset('assets/js/gazu-np-map.js') }}"></script>
    <script defer src="{{ asset('assets/js/gazu-fx.js') }}"></script>
</head>
<body class="gazu gazu-theme min-h-screen flex flex-col">
{{-- Accessibility: skip-to-main link для keyboard navigation (Tab key) --}}
<a href="#main-content"
   class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-[100] focus:px-4 focus:py-2 focus:bg-[var(--gazu-ink)] focus:text-white focus:rounded focus:no-underline focus:outline-2 focus:outline-[var(--gazu-blue)]">
    Перейти до основного контенту
</a>

{{-- INSTANT NAVIGATION (mobile-app feel, 5 шарів реакції)
     1. Viewport-visible links — prefetch одразу через IntersectionObserver
     2. Hover/touchstart — prefetch (~200ms head start)
     3. Mousedown — guaranteed prefetch (~80ms head start before mouseup→click)
     4. Livewire.navigate API (якщо доступний) — реєструє в Livewire SPA cache
     5. Fallback <link rel=prefetch as=document> — HTTP cache
--}}
<script>
(function () {
    if (typeof window === 'undefined') return;
    var prefetched = new Set();
    var origin = location.origin;
    var inflight = 0;
    var MAX_CONCURRENT = 4;

    function shouldSkip(url) {
        if (!url || prefetched.has(url) || url.startsWith('javascript:') || url.startsWith('mailto:') || url.startsWith('tel:')) return true;
        try {
            var u = new URL(url, origin);
            if (u.origin !== origin) return true;
            if (u.pathname === location.pathname && !u.search) return true;
            // Skip auth pages — не warm session cookie без потреби
            if (/\/(login|logout|register|admin|api)/.test(u.pathname)) return true;
        } catch (e) { return true; }
        return false;
    }

    function prefetch(url) {
        if (shouldSkip(url) || inflight >= MAX_CONCURRENT) return;
        prefetched.add(url);
        inflight++;
        // Livewire 3: Livewire.navigate(url, { prefetch: true }) кладе в Livewire cache.
        if (window.Livewire && typeof window.Livewire.navigate === 'function') {
            try {
                // No-op stub: Livewire's prefetch не публічний; fallback на rel=prefetch.
            } catch (e) {}
        }
        var link = document.createElement('link');
        link.rel = 'prefetch';
        link.href = url;
        link.as = 'document';
        link.onload = link.onerror = function () { inflight = Math.max(0, inflight - 1); };
        document.head.appendChild(link);
    }

    function urlOf(a) {
        return (a && a.href) ? a.href : null;
    }

    function tryPrefetch(target) {
        if (!target || !target.closest) return;
        var a = target.closest('a[wire\\:navigate], a[wire\\:navigate\\.hover]');
        if (!a) return;
        prefetch(urlOf(a));
    }

    var ric = window.requestIdleCallback || function (cb) { return setTimeout(cb, 1); };

    // (1) Viewport prefetch — на view (above + below fold up to ~600px рамою)
    if ('IntersectionObserver' in window) {
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    ric(function () { prefetch(urlOf(entry.target)); });
                    io.unobserve(entry.target);
                }
            });
        }, { rootMargin: '600px 0px' });
        // Observe всі поточні navigate-links + спостерігаємо за новими через MutationObserver
        function observeAll() {
            document.querySelectorAll('a[wire\\:navigate], a[wire\\:navigate\\.hover]').forEach(function (a) {
                if (!a.dataset.prefetchObserved) {
                    a.dataset.prefetchObserved = '1';
                    io.observe(a);
                }
            });
        }
        observeAll();
        document.addEventListener('livewire:navigated', observeAll);
    }

    // (2) Hover/touchstart prefetch
    document.addEventListener('mouseover', function (e) { ric(function(){ tryPrefetch(e.target); }); }, { passive: true, capture: true });
    document.addEventListener('touchstart', function (e) { tryPrefetch(e.target); }, { passive: true, capture: true });

    // (3) Mousedown — guaranteed prefetch (~80ms head start before mouseup→click)
    document.addEventListener('mousedown', function (e) { tryPrefetch(e.target); }, { passive: true, capture: true });

    // Instant scroll-to-top при навігації — без smooth animation.
    document.addEventListener('livewire:navigating', function () {
        document.documentElement.scrollTop = 0;
        document.body.scrollTop = 0;
    });
})();
</script>

@include('gazu.partials.header', ['activeNav' => $activeNav ?? null, 'cartCount' => $cartCount ?? 0])

<main id="main-content" class="flex-1" tabindex="-1">

    @yield('content')
</main>

@include('gazu.partials.footer')

{{-- Global brutal-style 1-click order modal (listens for `gazu:one-click`). --}}
<x-gazu.one-click-modal/>

{{-- Critical CSS for drawer + toast positioning. Inline because the prod
     Vite bundle doesn't include the arbitrary `w-[380px]` / `inset-y-0`
     Tailwind utilities, so the drawer would otherwise stretch full-width. --}}
<style>
.gazu-drawer {
    position: fixed; top: 0; bottom: 0; right: 0; z-index: 65;
    width: 100%; max-width: 380px;
    background: #fff; border-left: 1px solid var(--gazu-line);
    box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
    display: flex; flex-direction: column;
}
@media (min-width: 640px) {
    body:has(.gazu-drawer[data-open="1"]) .gazu-toast-container { right: 396px; }
}
.gazu-toast-container { transition: right 0.25s cubic-bezier(0.16, 1, 0.3, 1); }

/* Grid/flex children must be allowed to shrink below their content width,
   otherwise a long word in the hero h1 forces horizontal overflow on phones. */
.gazu-grid-hero-vin > *,
.gazu-grid-buy > *,
.gazu-grid-buy-left > *,
.gazu-grid-sidebar > * { min-width: 0; }
.gazu-grid-hero-vin h1 { overflow-wrap: anywhere; }

/* Footer: 2 cols on phones, 5 on desktop (was a fixed 5-col grid that
   overflowed narrow viewports). */
.gazu-footer-grid { grid-template-columns: 1fr 1fr; }
@media (min-width: 768px) {
    .gazu-footer-grid { grid-template-columns: 1.4fr 1fr 1fr 1fr 1.2fr; }
}
</style>

{{-- Глобальний Toast UI (window.gazuToast або CustomEvent('gazu:toast')) --}}
<div class="gazu-toast-container" x-data="{
        queue: [],
        nextId: 0,
        init() {
            window.addEventListener('gazu:toast', (e) => this.add(e.detail.message, e.detail.type, e.detail.duration));
            window.gazuToast = (m, t, d) => this.add(m, t, d);
        },
        add(message, type = 'success', duration = 3500) {
            const id = ++this.nextId;
            this.queue.push({ id, message, type });
            setTimeout(() => this.dismiss(id), duration);
        },
        dismiss(id) { this.queue = this.queue.filter(t => t.id !== id); }
     }" x-cloak>
    <template x-for="t in queue" :key="t.id">
        <div class="gazu-toast" :class="t.type" role="status">
            <svg x-show="t.type === 'success'" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg>
            <svg x-show="t.type === 'error'" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M15 9l-6 6M9 9l6 6"/></svg>
            <svg x-show="t.type === 'info'" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
            <span class="text-sm font-medium" x-text="t.message"></span>
            <template x-if="t.action">
                <a :href="t.action.href" class="ml-1 text-sm font-semibold underline underline-offset-2 text-white whitespace-nowrap" x-text="t.action.label"></a>
            </template>
            <button type="button" @click="dismiss(t.id)" class="ml-2 bg-transparent border-0 text-white/80 hover:text-white cursor-pointer" aria-label="Закрити">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>
    </template>
</div>

{{-- Mini-cart drawer — slides in from right on cart-updated event.
     Lists every cart line with image/title/price, qty-stepper (+/−) and remove.
     All mutations hit the cart endpoints + re-fetch /cart/contents → live totals. --}}
<div x-data="{
        open: false,
        loading: false,
        busy: {},
        items: [],
        count: 0, qtyTotal: 0, total: 0,
        _idleMs: 6000,   // авто-приховування, якщо немає взаємодії
        _idle: null,
        _csrf() { return document.querySelector('meta[name=csrf-token]')?.content || ''; },
        init() {
            window.addEventListener('cart-updated', () => { this.openAndLoad(); });
        },
        openAndLoad() {
            this.open = true;
            this.fetchContents();
            this.armIdle();
        },
        // Закрити drawer через _idleMs бездіяльності. Скидається на будь-яку
        // взаємодію (hover/scroll/click). Скасовується поки курсор всередині.
        armIdle() {
            clearTimeout(this._idle);
            this._idle = setTimeout(() => { this.open = false; }, this._idleMs);
        },
        cancelIdle() { clearTimeout(this._idle); },
        async fetchContents() {
            this.loading = true;
            try {
                const r = await fetch('{{ route('gazu.cart.contents') }}', { headers: { 'Accept': 'application/json' } });
                const d = await r.json();
                this.items = d.items || [];
                this.count = d.count || 0;
                this.qtyTotal = d.qtyTotal || 0;
                this.total = d.total || 0;
            } catch (e) { /* keep stale */ }
            this.loading = false;
        },
        async setQty(item, qty) {
            if (qty < 1 || this.busy[item.id]) return;
            this.busy[item.id] = true;
            try {
                await fetch('{{ route('gazu.cart.update') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': this._csrf() },
                    body: JSON.stringify({ product_id: item.id, quantity: qty }),
                });
                await this.fetchContents();
                window.dispatchEvent(new CustomEvent('cart-updated', { detail: { count: this.count, qtyTotal: this.qtyTotal, total: this.total, _internal: true } }));
            } catch (e) {}
            this.busy[item.id] = false;
        },
        async removeItem(item) {
            if (this.busy[item.id]) return;
            this.busy[item.id] = true;
            try {
                await fetch('{{ route('gazu.cart.remove') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': this._csrf() },
                    body: JSON.stringify({ product_id: item.id }),
                });
                await this.fetchContents();
                window.dispatchEvent(new CustomEvent('cart-updated', { detail: { count: this.count, qtyTotal: this.qtyTotal, total: this.total, _internal: true } }));
            } catch (e) {}
            this.busy[item.id] = false;
        },
        money(v){ return new Intl.NumberFormat('uk-UA').format(Math.round(v)) + ' ₴'; }
     }"
     x-show="open" x-cloak
     @click.outside="open = false"
     @keydown.escape.window="open = false"
     @mouseenter="cancelIdle()"
     @mouseleave="armIdle()"
     @mousemove="cancelIdle()"
     @scroll.passive="cancelIdle(); armIdle()"
     @touchstart.passive="cancelIdle()"
     class="fixed inset-y-0 right-0 z-[65] w-full sm:w-[400px] bg-white border-l border-[var(--gazu-line)] shadow-2xl flex flex-col gazu-drawer"
     :data-open="open ? '1' : '0'"
     role="dialog" aria-label="Кошик">

    {{-- Header --}}
    <div class="flex items-center justify-between p-4 border-b border-[var(--gazu-line)] shrink-0">
        <div class="flex items-center gap-2">
            <x-gazu.icon name="cart" size="20"/>
            <span class="font-semibold">Кошик</span>
            <span class="text-xs text-[var(--gazu-graphite)]" x-show="count" x-text="'· ' + qtyTotal + ' шт.'"></span>
        </div>
        <button type="button" @click="open = false" class="w-8 h-8 rounded-md hover:bg-[var(--gazu-mist)] flex items-center justify-center" aria-label="Закрити">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
        </button>
    </div>

    {{-- Items list --}}
    <div class="flex-1 overflow-y-auto p-3">
        {{-- Empty --}}
        <div x-show="!loading && items.length === 0" class="h-full flex flex-col items-center justify-center text-center px-6 py-10">
            <div class="w-14 h-14 rounded-full bg-[var(--gazu-mist)] flex items-center justify-center mb-3 text-[var(--gazu-blue)]">
                <x-gazu.icon name="cart" size="24"/>
            </div>
            <div class="font-medium text-[var(--gazu-ink)] mb-1">Кошик порожній</div>
            <div class="text-xs text-[var(--gazu-graphite)]">Додайте товари з каталогу</div>
        </div>

        {{-- Skeleton while first load --}}
        <div x-show="loading && items.length === 0" class="space-y-3">
            <template x-for="i in 3" :key="i">
                <div class="flex gap-3 animate-pulse">
                    <div class="w-16 h-16 rounded-md bg-[var(--gazu-mist)] shrink-0"></div>
                    <div class="flex-1 space-y-2 py-1"><div class="h-3 bg-[var(--gazu-mist)] rounded w-3/4"></div><div class="h-3 bg-[var(--gazu-mist)] rounded w-1/3"></div></div>
                </div>
            </template>
        </div>

        {{-- Lines --}}
        <div class="space-y-3" x-show="items.length">
            <template x-for="item in items" :key="item.key">
                <div class="flex gap-3 pb-3 border-b border-[var(--gazu-line)] last:border-0 transition-opacity" :class="busy[item.id] ? 'opacity-50' : ''">
                    {{-- Image --}}
                    <a :href="item.url || '#'" class="w-16 h-16 rounded-md bg-[var(--gazu-paper)] border border-[var(--gazu-line)] shrink-0 overflow-hidden flex items-center justify-center no-underline">
                        <template x-if="item.image">
                            <img :src="item.image" :alt="item.title" class="w-full h-full object-cover" loading="lazy">
                        </template>
                        <template x-if="!item.image">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--gazu-line-2)" stroke-width="1.5"><path d="m3 7 9-4 9 4-9 4-9-4Z"/><path d="M3 7v10l9 4 9-4V7"/></svg>
                        </template>
                    </a>
                    {{-- Body --}}
                    <div class="flex-1 min-w-0">
                        <a :href="item.url || '#'" class="block text-[13px] font-medium text-[var(--gazu-ink)] leading-snug no-underline hover:text-[var(--gazu-blue)] line-clamp-2" x-text="item.title"></a>
                        <div class="flex items-center justify-between mt-2">
                            {{-- Qty stepper --}}
                            <div class="inline-flex items-center border border-[var(--gazu-line)] rounded-md">
                                <button type="button" @click="setQty(item, item.qty - 1)" :disabled="item.qty <= 1 || busy[item.id]"
                                        class="w-8 h-8 flex items-center justify-center text-[var(--gazu-ink)] disabled:opacity-40 disabled:cursor-not-allowed hover:bg-[var(--gazu-mist)]" aria-label="Менше">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M5 12h14"/></svg>
                                </button>
                                <span class="w-8 text-center text-[13px] font-medium gazu-mono" x-text="item.qty"></span>
                                <button type="button" @click="setQty(item, item.qty + 1)" :disabled="busy[item.id]"
                                        class="w-8 h-8 flex items-center justify-center text-[var(--gazu-ink)] disabled:opacity-40 hover:bg-[var(--gazu-mist)]" aria-label="Більше">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                                </button>
                            </div>
                            {{-- Line total --}}
                            <div class="gazu-display font-bold text-[var(--gazu-ink)] text-[14px] whitespace-nowrap" x-text="money(item.lineTotal)"></div>
                        </div>
                    </div>
                    {{-- Remove --}}
                    <button type="button" @click="removeItem(item)" :disabled="busy[item.id]"
                            class="w-7 h-7 shrink-0 self-start rounded-md text-[var(--gazu-graphite)] hover:text-[var(--gazu-danger)] hover:bg-[var(--gazu-danger-bg)] flex items-center justify-center transition-colors" aria-label="Видалити">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/></svg>
                    </button>
                </div>
            </template>
        </div>
    </div>

    {{-- Footer --}}
    <div class="border-t border-[var(--gazu-line)] p-4 shrink-0" x-show="items.length">
        <div class="flex items-baseline justify-between mb-3">
            <span class="text-sm text-[var(--gazu-graphite)]">Разом</span>
            <span class="gazu-display text-2xl font-bold text-[var(--gazu-ink)]" x-text="money(total)"></span>
        </div>
        <div class="flex gap-2">
            <button type="button" @click="open = false" class="flex-1 py-2.5 border border-[var(--gazu-line-2)] rounded-md text-sm font-medium hover:bg-[var(--gazu-mist)]">Продовжити</button>
            <a wire:navigate href="{{ route('gazu.cart') }}" class="flex-1 py-2.5 bg-[var(--gazu-ink)] hover:bg-[var(--gazu-ink-2)] text-white rounded-md text-sm font-medium no-underline inline-flex items-center justify-center">Оформити</a>
        </div>
    </div>
</div>

{{-- Flash messages → toast after DOM ready --}}
@if(session('cart_message') || session('flash_message') || session('order_message'))
    @php $msg = session('cart_message') ?? session('flash_message') ?? session('order_message'); @endphp
    <script>setTimeout(() => window.gazuToast && window.gazuToast(@json($msg), 'success'), 100);</script>
@endif
@if($errors->any())
    <script>setTimeout(() => window.gazuToast && window.gazuToast(@json($errors->first()), 'error'), 100);</script>
@endif

@livewireScripts
</body>
</html>
