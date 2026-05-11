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
    @endif
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
