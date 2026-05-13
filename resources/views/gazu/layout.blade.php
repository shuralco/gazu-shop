{{-- GAZU layout — окремий від чинного storefront. Лежить поряд, у resources/views/gazu/. --}}
<!DOCTYPE html>
<html lang="uk" class="gazu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $metaProductsLabel = $shopStats['products_label'] ?? 'широкий каталог';
        $pageTitle = trim(($__env->yieldContent('title') ?: 'GAZU — каталог автозапчастин'));
        $pageDescription = trim(($__env->yieldContent('description') ?: 'Інтернет-магазин автозапчастин · '.$metaProductsLabel.' · доставка по Україні'));
        $canonical = url()->current();
    @endphp
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    <link rel="canonical" href="{{ $canonical }}">

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
        /* Mobile safety: ніколи не дозволяти горизонтальний overflow */
        html, body.gazu { max-width: 100vw; overflow-x: hidden; }
        @media (max-width: 640px) {
            .gazu-container { padding-left: 16px; padding-right: 16px; }
        }
    </style>
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDescription }}">

    @yield('jsonld')

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

@include('gazu.partials.header', ['activeNav' => $activeNav ?? null, 'cartCount' => $cartCount ?? 0])

<main class="flex-1">
    @yield('content')
</main>

@include('gazu.partials.footer')

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
