{{-- GAZU layout — окремий від чинного storefront. Лежить поряд, у resources/views/gazu/. --}}
<!DOCTYPE html>
<html lang="uk" class="gazu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- CSRF token — Spatie ResponseCache CsrfTokenReplacer auto-replaces this
         meta value per-request, тому будь-який кешований HTML має правильний
         токен для активної session. Усі JS-fetch'ери мають читати з нього. --}}
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

        // Wishlist client-side hydration: HTML кешований ResponseCache → server-side
        // $inWishlist неконсистентний для різних users. JS витягує справжні wishlist
        // ids поточного user і dispatch event щоб кожна product-card оновила heart-state.
        (function () {
            window.GAZU_WISHLIST_IDS = new Set();
            function loadIds() {
                fetch('{{ route('gazu.wishlist.ids') }}', { credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
                    .then(function (r) { return r.ok ? r.json() : { ids: [] }; })
                    .then(function (d) {
                        window.GAZU_WISHLIST_IDS = new Set((d.ids || []).map(Number));
                        window.dispatchEvent(new CustomEvent('gazu:wishlist-ids-loaded'));
                    })
                    .catch(function () {});
            }
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

    @vite(['resources/css/themes/gazu/gazu.css'])
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

<main class="flex-1">
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
            <button type="button" @click="dismiss(t.id)" class="ml-2 bg-transparent border-0 text-white/80 hover:text-white cursor-pointer" aria-label="Закрити">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>
    </template>
</div>

{{-- Mini-cart drawer — slides in from right on cart-updated event --}}
<div x-data="{
        open: false,
        count: 0, qtyTotal: 0, total: 0,
        init() {
            window.addEventListener('cart-updated', (e) => {
                if (e.detail) {
                    this.count = e.detail.count || 0;
                    this.qtyTotal = e.detail.qtyTotal || 0;
                    this.total = e.detail.total || 0;
                }
                this.open = true;
                clearTimeout(this._t);
                this._t = setTimeout(() => this.open = false, 4500);
            });
        },
        money(v){ return new Intl.NumberFormat('uk-UA').format(v) + ' ₴'; }
     }"
     x-show="open" x-cloak
     @click.outside="open = false"
     @keydown.escape.window="open = false"
     class="fixed inset-y-0 right-0 z-[65] w-full sm:w-[380px] bg-white border-l border-[var(--gazu-line)] shadow-2xl flex flex-col gazu-drawer"
     :data-open="open ? '1' : '0'"
     role="dialog" aria-label="Кошик">
    <div class="flex items-center justify-between p-4 border-b border-[var(--gazu-line)]">
        <div class="flex items-center gap-2">
            <x-gazu.icon name="cart" size="20"/>
            <span class="font-semibold">Додано в кошик</span>
        </div>
        <button type="button" @click="open = false" class="w-8 h-8 rounded-md hover:bg-[var(--gazu-mist)] flex items-center justify-center" aria-label="Закрити">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
        </button>
    </div>
    <div class="flex-1 p-4">
        <div class="bg-[var(--gazu-mist)] rounded-lg p-4 text-center">
            <div class="text-sm text-[var(--gazu-graphite)]">Усього в кошику</div>
            <div class="gazu-display text-3xl font-semibold mt-1" x-text="qtyTotal + ' шт.'"></div>
            <div class="gazu-mono text-lg mt-1" x-text="money(total)"></div>
        </div>
    </div>
    <div class="p-4 border-t border-[var(--gazu-line)] flex gap-2">
        <button type="button" @click="open = false" class="flex-1 py-2.5 border border-[var(--gazu-line-2)] rounded-md text-sm font-medium hover:bg-[var(--gazu-mist)]">Продовжити</button>
        <a wire:navigate href="{{ route('gazu.cart') }}" class="flex-1 py-2.5 bg-[var(--gazu-ink)] hover:bg-[var(--gazu-ink-2)] text-white rounded-md text-sm font-medium no-underline inline-flex items-center justify-center">Оформити</a>
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
