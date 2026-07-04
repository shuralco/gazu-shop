<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| GAZU shop — fork of brutal-codebase, /gazu storefront only.
| Brutal /uk storefront removed in cleanup (2026-05-09).
|--------------------------------------------------------------------------
*/

// Emergency safe-mode endpoint — OUTSIDE any group щоб працювало навіть
// якщо група middleware/views зламані модулями. Token-protected.
//   URL: /safe-mode?token={sha1(APP_KEY).substr(0,16)}
Route::get('/safe-mode', [\App\Http\Controllers\SafeModeController::class, 'trigger'])
    ->withoutMiddleware(['web'])
    ->middleware('throttle:10,1');

// GAZU storefront — root-level URLs (no /gazu prefix, this fork is GAZU-only).
Route::name('gazu.')->middleware(['web'])->group(function () {
    $c = \App\Http\Controllers\Gazu\StoreController::class;

    Route::get('/', [$c, 'home'])->name('home');
    // Dev/staging variants — приховані за query string '?dev=1', або при APP_DEBUG=true.
    // Public users не побачать ці URLs.
    Route::get('/v2', function () use ($c) {
        if (! request('dev') && ! config('app.debug')) abort(404);
        return app($c)->home(request()->merge(['variant' => 'v2']));
    })->name('home.v2');
    Route::get('/v3', function () use ($c) {
        if (! request('dev') && ! config('app.debug')) abort(404);
        return app($c)->home(request()->merge(['variant' => 'v3']));
    })->name('home.v3');

    Route::get('/catalog', [$c, 'catalog'])->name('catalog');
    Route::get('/catalog/v2', function () use ($c) {
        if (! request('dev') && ! config('app.debug')) abort(404);
        return app($c)->catalog(request()->merge(['variant' => 'v2']));
    })->name('catalog.v2');
    Route::get('/catalog/v3', function () use ($c) {
        if (! request('dev') && ! config('app.debug')) abort(404);
        return app($c)->catalog(request()->merge(['variant' => 'v3']));
    })->name('catalog.v3');

    // Pretty URLs for car-selector filter: /zapchastyny/{make}/{model?}/{engine?}
    // Controller still consumes ?make/&model/&engine — route binding maps params into the query.
    // Констрейнти case-insensitive: код двигуна/слаг моделі бувають ВЕЛИКИМИ
    // (ID4EU, AWD, GW4G15B) — раніше [a-z0-9] відхиляв їх → 404.
    Route::get('/zapchastyny/{make}', [$c, 'catalogByCar'])->name('catalog.by-make')
        ->where('make', '[A-Za-z0-9][A-Za-z0-9-]*');
    Route::get('/zapchastyny/{make}/{model}', [$c, 'catalogByCar'])->name('catalog.by-model')
        ->where(['make' => '[A-Za-z0-9][A-Za-z0-9-]*', 'model' => '[A-Za-z0-9][A-Za-z0-9-]*']);
    Route::get('/zapchastyny/{make}/{model}/{engine}', [$c, 'catalogByCar'])->name('catalog.by-engine')
        ->where(['make' => '[A-Za-z0-9][A-Za-z0-9-]*', 'model' => '[A-Za-z0-9][A-Za-z0-9-]*', 'engine' => '[A-Za-z0-9][A-Za-z0-9\-\.]*']);

    // Pretty URLs для меню: «Новинки» / «Хіти» / «Акції» (раніше ?new=1 etc).
    Route::get('/novynky', fn () => app(\App\Http\Controllers\Gazu\StoreController::class)->catalog(request()->merge(['new' => 1])))->name('catalog.new');
    Route::get('/khity',   fn () => app(\App\Http\Controllers\Gazu\StoreController::class)->catalog(request()->merge(['hits' => 1])))->name('catalog.hits');
    Route::get('/akcii',   fn () => app(\App\Http\Controllers\Gazu\StoreController::class)->catalog(request()->merge(['promo' => 1])))->name('catalog.promo');

    // Backward compat: 301 to clean URL.
    Route::get('/product/{slug}', fn (string $slug) => redirect('/'.$slug, 301))
        ->where('slug', '[a-z0-9][a-z0-9-]*');
    Route::get('/product/{slug}/v2', fn (string $slug) => redirect('/'.$slug.'?v=2', 301))
        ->where('slug', '[a-z0-9][a-z0-9-]*');
    Route::get('/product/{slug}/v3', fn (string $slug) => redirect('/'.$slug.'?v=3', 301))
        ->where('slug', '[a-z0-9][a-z0-9-]*');

    Route::get('/cart', [$c, 'cart'])->name('cart');
    Route::get('/cart/empty', [$c, 'emptyCart'])->name('cart.empty');

    $cart = \App\Http\Controllers\Gazu\CartController::class;
    Route::get('/cart/contents', [$cart, 'contents'])->name('cart.contents');
    Route::middleware('throttle:60,1')->group(function () use ($cart) {
        Route::post('/cart/add',    [$cart, 'add'])->name('cart.add');
        Route::post('/cart/update', [$cart, 'update'])->name('cart.update');
        Route::post('/cart/remove', [$cart, 'remove'])->name('cart.remove');
        Route::post('/cart/clear',  [$cart, 'clear'])->name('cart.clear');
        Route::post('/cart/coupon/apply', [$cart, 'applyCoupon'])->name('cart.coupon.apply');
        Route::post('/cart/coupon/remove', [$cart, 'removeCoupon'])->name('cart.coupon.remove');
    });

    $checkout = \App\Http\Controllers\Gazu\CheckoutController::class;
    Route::get('/checkout', [$checkout, 'index'])->name('checkout');
    Route::post('/checkout', [$checkout, 'store'])->name('checkout.store');
    Route::get('/checkout/success/{order}', [$checkout, 'success'])->name('checkout.success');
    Route::post('/checkout/one-click', [$checkout, 'oneClick'])->name('checkout.one-click');

    // Свіжий CSRF-токен для клієнта (keep-alive). Лежить у web-групі (StartSession
    // активна → токен прив'язаний до поточної сесії і її TTL оновлюється при пінгу).
    // Виключений з ResponseCache у GazuCacheProfile, інакше токен «замерзне» в кеші.
    Route::get('/csrf-token', fn () => response()->json(['token' => csrf_token()])
        ->header('Cache-Control', 'no-store, private'))->name('csrf-token');

    $auth = \App\Http\Controllers\Gazu\AuthController::class;
    Route::get('/login', [$auth, 'show'])->name('auth');
    Route::get('/auth', fn () => redirect('/login', 301)); // legacy 301
    Route::middleware('throttle:10,1')->group(function () use ($auth) {
        Route::post('/login', [$auth, 'login'])->name('auth.login');
        Route::post('/register', [$auth, 'register'])->name('auth.register');
        Route::post('/auth/login', fn () => redirect('/login', 301)); // legacy
        Route::post('/auth/register', fn () => redirect('/register', 301)); // legacy
    });
    Route::post('/logout', [$auth, 'logout'])->name('auth.logout');
    Route::post('/auth/logout', fn () => redirect('/logout', 301)); // legacy

    // Legacy 301 — ЗА межами auth middleware щоб 301 спрацював одразу,
    // а не 302 на login. SEO правильно: /account → 301 → /kabinet → 302 → /login.
    Route::get('/account', fn () => redirect('/kabinet', 301));
    Route::get('/account/orders/{order}', fn ($o) => redirect("/kabinet/zamovlennya/{$o}", 301));
    Route::get('/orders/{order}/payment', fn ($o) => redirect("/zamovlennya/{$o}/oplata", 301));

    // /garazh routes moved to modules/gazu_garage/routes/web.php
    // Auto-loaded via ModuleDiscovery when module is enabled.

    Route::middleware('auth')->group(function () use ($c) {
        // Canonical UA URLs.
        Route::get('/kabinet', [$c, 'account'])->name('account');
        Route::get('/kabinet/zamovlennya/{order}', [$c, 'orderDetails'])->name('account.order');
        Route::get('/zamovlennya/{order}/oplata', [$c, 'orderPayment'])->name('order.payment');
    });

    // Brands: /brand (index), /brand/{slug} (specific). /brendy* — 301 legacy redirect.
    Route::get('/brand/{slug?}', [$c, 'brand'])->name('brand');
    Route::get('/brendy', fn () => redirect('/brand', 301));
    Route::get('/brendy/{slug}', fn (string $slug) => redirect("/brand/{$slug}", 301));

    $wish = \App\Http\Controllers\Gazu\WishlistController::class;
    Route::get('/wishlist', [$wish, 'index'])->name('wishlist');
    Route::post('/wishlist/toggle', [$wish, 'toggle'])->name('wishlist.toggle');
    Route::post('/wishlist/merge', [$wish, 'merge'])->name('wishlist.merge');
    Route::get('/api/wishlist/ids', [$wish, 'ids'])->name('wishlist.ids');

    // Callback request (footer popup, throttled).
    Route::post('/api/callback', [\App\Http\Controllers\Gazu\CallbackController::class, 'store'])->name('callback.store');

    // Stock notify: «повідомити коли з'явиться».
    Route::post('/api/stock-notify', [\App\Http\Controllers\Gazu\StockNotificationController::class, 'store'])->name('stock.notify');

    // СТО page removed (Etap 91). Legacy 301 → home для backward compat у Google index.
    Route::get('/sto', fn () => redirect('/', 301))->name('sto');
    Route::get('/blog', [$c, 'blog'])->name('blog');
    // Рубрика блогу — має йти ПЕРЕД /blog/{slug}, щоб не перехоплювалось як стаття.
    Route::get('/blog/rubryka/{categorySlug}', fn (string $categorySlug) => app($c)->blog(null, $categorySlug))->name('blog.category');
    Route::get('/blog/{slug}', [$c, 'blog'])->name('blog.show');

    // SEO landing pages built from filter combinations (admin-configurable).
    // Example URLs: /lp/filtri-oliv-bosch-dlya-bydy, /lp/akumulyatory-12v-60ah
    Route::get('/lp/{slug}', [\App\Http\Controllers\Gazu\FilterLandingController::class, 'show'])
        ->name('landing.show');
    Route::get('/contacts', [$c, 'contacts'])->name('contacts');
    Route::get('/search', [$c, 'search'])->name('search');
    Route::get('/api/search/suggest', [$c, 'searchSuggest'])->name('search.suggest');
    Route::get('/api/np/cities', [$c, 'npCities'])->name('api.np.cities');
    Route::get('/api/np/warehouses', [$c, 'npWarehouses'])->name('api.np.warehouses');
    Route::get('/api/np/streets', [$c, 'npStreets'])->name('api.np.streets');
    Route::get('/api/np/calculate', [$c, 'npCalculate'])->name('api.np.calculate');
    Route::get('/api/up/cities', [$c, 'upCities'])->name('api.up.cities');
    Route::get('/api/up/post-offices', [$c, 'upPostOffices'])->name('api.up.post-offices');

    // Car-selector cascade (марка → модель → двигун). Public, cached 1h.
    Route::get('/api/cars/makes', [$c, 'apiCarMakes'])->name('api.cars.makes');
    Route::get('/api/cars/models', [$c, 'apiCarModels'])->name('api.cars.models');
    Route::get('/api/cars/engines', [$c, 'apiCarEngines'])->name('api.cars.engines');
    // 4D: compat-check — «чи підходить ця запчастина моєму авто?»
    Route::get('/api/compat/check', [$c, 'apiCompatCheck'])->name('api.compat.check');
    // TEMP debug (прибрати): сирий JSON сумісності + звіт синку по товару.
    Route::get('/api/_dbg/compat/{id}', function ($id) {
        $p = \App\Models\Product::find((int) $id);
        if (! $p) return response()->json(['error' => 'not found']);
        // ?action=add-unyx — додати рядок Unyx 06 «усі варіації» (демо критерію приймання).
        if (request('action') === 'add-unyx') {
            $rows = is_array($p->compatibility) ? $p->compatibility : [];
            $has = collect($rows)->contains(fn ($r) => is_array($r) && mb_stripos(($r['model'] ?? ''), 'unyx') !== false);
            if (! $has) {
                $rows[] = ['make' => 'Volkswagen', 'model' => 'ID Unyx 06', 'years' => '2024-', 'engine' => '', 'all_engines' => true];
                $p->compatibility = $rows;
                $p->save(); // Product::saved → CompatibilitySync
            }
        }
        $rep = \App\Services\Gazu\CompatibilitySync::syncProductReport($p);
        return response()->json([
            'id' => $p->id,
            'compatibility_raw' => $p->compatibility,
            'report' => $rep,
            'linked_engine_ids' => $p->compatibleEngines()->pluck('car_engines.id'),
            'unyx_models_by_name' => \App\Models\CarModel::query()
                ->whereRaw("LOWER(TRIM(name)) LIKE '%unyx%'")
                ->with('make:id,name')
                ->get(['id', 'name', 'make_id', 'is_active'])
                ->map(fn ($m) => ['id' => $m->id, 'name' => $m->name, 'make' => $m->make?->name, 'active' => $m->is_active, 'engines' => \App\Models\CarEngine::where('model_id', $m->id)->count()]),
        ]);
    });
    // Recently-viewed: повертає products by ID list (для recently-viewed block)
    Route::get('/api/products/by-ids', [$c, 'apiProductsByIds'])->name('api.products.by-ids');

    // Variant picker AJAX endpoints живуть у модулі `related_products`.
    // Файл: modules/related_products/routes/web.php (підключається
    // через ModuleDiscovery, якщо модуль увімкнено).

    Route::get('/404', [$c, 'notFound'])->name('404');
    // /m/{page} — test mobile page, доступний тільки в debug режимі
    Route::get('/m/{page}', function (\Illuminate\Http\Request $r, string $page) use ($c) {
        if (! config('app.debug')) abort(404);
        return app($c)->mobile($r, $page);
    })->name('mobile');

    // Static info pages — all served by InfoController which reads from the
    // `info_pages` table (editable in the Filament admin) and falls back to
    // hard-coded content when a row is missing.
    Route::get('/about',        [\App\Http\Controllers\Gazu\InfoController::class, 'show'])->defaults('slug', 'about')->name('about');
    Route::get('/delivery',     [\App\Http\Controllers\Gazu\InfoController::class, 'show'])->defaults('slug', 'delivery')->name('delivery');
    Route::get('/warranty',     [\App\Http\Controllers\Gazu\InfoController::class, 'show'])->defaults('slug', 'warranty')->name('warranty');
    Route::get('/privacy',      [\App\Http\Controllers\Gazu\InfoController::class, 'show'])->defaults('slug', 'privacy')->name('privacy');
    Route::get('/terms',        [\App\Http\Controllers\Gazu\InfoController::class, 'show'])->defaults('slug', 'terms')->name('terms');
    Route::get('/optom',        [\App\Http\Controllers\Gazu\InfoController::class, 'show'])->defaults('slug', 'wholesale')->name('wholesale');
    Route::get('/wholesale',    fn () => redirect('/optom', 301)); // legacy
    Route::get('/faq',          [\App\Http\Controllers\Gazu\InfoController::class, 'show'])->defaults('slug', 'faq')->name('faq');
    Route::get('/bonusy',       [\App\Http\Controllers\Gazu\InfoController::class, 'show'])->defaults('slug', 'loyalty')->name('loyalty');
    Route::get('/loyalty',      fn () => redirect('/bonusy', 301)); // legacy
    Route::get('/careers',      [\App\Http\Controllers\Gazu\InfoController::class, 'show'])->defaults('slug', 'careers')->name('careers');
    Route::get('/certificates', [\App\Http\Controllers\Gazu\InfoController::class, 'show'])->defaults('slug', 'certificates')->name('certificates');
    Route::get('/offer',        [\App\Http\Controllers\Gazu\InfoController::class, 'show'])->defaults('slug', 'offer')->name('offer');

    // CMS-сторінки (/admin/pages, модуль cms_pages) — довільні сторінки
    // з блоками зон layout.page.* (Конструктор зон, OpenCart-стиль).
    Route::get('/page/{slug}', [\App\Http\Controllers\Gazu\CmsPageController::class, 'show'])
        ->middleware('module:cms_pages')
        ->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*')
        ->name('cms.page');

    // Root-level catch-all: resolveSlug dispatches to product (slug ends in
    // -\d+, Rozetka-style) or category (no numeric suffix). Must be LAST in
    // the group so every specific path above wins. Same URL pattern serves
    // both — the named alias is just for URL generation.
    Route::get('/{slug}', [$c, 'resolveSlug'])
        ->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*')
        ->name('product.show');
});

// Product feeds (no locale prefix)
Route::withoutMiddleware(['web'])->group(function () {
    Route::get('/feed/google.xml', function () {
        $feed = app(\App\Services\FeedGenerator\YmlFeedGenerator::class)->generate('google');
        return response($feed, 200, ['Content-Type' => 'application/xml']);
    })->name('feed.google');

    Route::get('/feed/rozetka.xml', function () {
        $feed = app(\App\Services\FeedGenerator\YmlFeedGenerator::class)->generate('rozetka');
        return response($feed, 200, ['Content-Type' => 'application/xml']);
    })->name('feed.rozetka');

    Route::get('/feed/prom.xml', function () {
        $feed = app(\App\Services\FeedGenerator\YmlFeedGenerator::class)->generate('prom');
        return response($feed, 200, ['Content-Type' => 'application/xml']);
    })->name('feed.prom');

    Route::get('/feed/olx.xml', function () {
        $feed = app(\App\Services\FeedGenerator\YmlFeedGenerator::class)->generate('olx');
        return response($feed, 200, ['Content-Type' => 'application/xml']);
    })->name('feed.olx');
});

// SEO and Sitemap
Route::withoutMiddleware(['web'])->group(function () {
    Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap.index');
    Route::get('/sitemap-main.xml', [\App\Http\Controllers\SitemapController::class, 'main'])->name('sitemap.main');
    Route::get('/sitemap-categories.xml', [\App\Http\Controllers\SitemapController::class, 'categories'])->name('sitemap.categories');
    Route::get('/sitemap-products.xml', [\App\Http\Controllers\SitemapController::class, 'products'])->name('sitemap.products');
Route::get('/sitemap-brands.xml', [\App\Http\Controllers\SitemapController::class, 'brands'])->name('sitemap.brands');
    Route::get('/robots.txt', [\App\Http\Controllers\SitemapController::class, 'robotsTxt'])->name('robots.txt');
});
Route::post('/sitemap/clear-cache', [\App\Http\Controllers\SitemapController::class, 'clearCache'])->name('sitemap.clear-cache');

// Payment + delivery webhooks (CSRF excluded via bootstrap/app.php validateCsrfTokens(except: ['webhooks/*']))
Route::post('/webhooks/liqpay', [\App\Http\Controllers\WebhookController::class, 'liqpay'])
    ->name('webhooks.liqpay');

Route::post('/webhooks/wayforpay', [\App\Http\Controllers\WebhookController::class, 'wayforpay'])
    ->name('webhooks.wayforpay');

Route::post('/webhooks/monobank', [\App\Http\Controllers\WebhookController::class, 'monobank'])
    ->name('webhooks.monobank');

// /api/np-webhook route moved to modules/novaposhta/routes/web.php
// (auto-loaded when novaposhta module is enabled)

// Filament admin
require __DIR__.'/admin.php';
