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

// ТИМЧАСОВО: повний reset каталогу до пари демо-штук. Видалити після.
Route::get('/__reset-demo', function (\Illuminate\Http\Request $request) {
    $u = auth()->user();
    abort_unless($u && ($u->is_admin === true || $u->access_preset_id !== null), 403);

    $DB = \Illuminate\Support\Facades\DB::connection();

    // --- Обчислення «що лишаємо» ---
    // Товари: топ-3 найзаповненіші (серед усіх, incl. trashed).
    $score = function ($p): int {
        $s = 0;
        foreach (['image', 'excerpt', 'content', 'description'] as $f) {
            if (! empty($p->$f)) $s++;
        }
        foreach (['specifications', 'compatibility', 'analogs', 'gallery'] as $f) {
            $v = json_decode((string) ($p->$f ?? ''), true);
            if (is_array($v) && $v) $s += 2;
        }
        if ((float) ($p->old_price ?? 0) > 0) $s++;
        if (! empty($p->sku)) $s++;
        return $s;
    };
    $keepProducts = collect($DB->table('products')->get())
        ->sortByDesc($score)->take(3)->pluck('id')->all();

    // Категорії: гілки лишених товарів (предки) — валідне міні-дерево.
    $parents = $DB->table('categories')->pluck('parent_id', 'id')->all();
    $keepCats = [];
    foreach ($DB->table('products')->whereIn('id', $keepProducts)->pluck('category_id') as $cid) {
        $d = 0;
        while ($cid && $d++ < 8) { $keepCats[$cid] = true; $cid = $parents[$cid] ?? null; }
    }
    // добити до 3 категорій кореневими, якщо мало
    if (count($keepCats) < 3) {
        foreach ($DB->table('categories')->whereNull('parent_id')->limit(3)->pluck('id') as $rid) {
            $keepCats[$rid] = true;
            if (count($keepCats) >= 3) break;
        }
    }
    $keepCats = array_map('intval', array_keys($keepCats));

    // Бренди: бренди лишених товарів + добити до 3.
    $keepBrands = $DB->table('products')->whereIn('id', $keepProducts)->whereNotNull('brand_id')->pluck('brand_id')->unique()->values()->all();
    if (count($keepBrands) < 3) {
        foreach ($DB->table('brands')->limit(3)->pluck('id') as $bid) {
            if (! in_array($bid, $keepBrands)) $keepBrands[] = $bid;
            if (count($keepBrands) >= 3) break;
        }
    }
    $keepBrands = array_map('intval', $keepBrands);

    // Характеристики: 3 (прив'язані до лишених товарів, інакше перші) + їх групи.
    $linkedFilters = $DB->table('filter_products')->whereIn('product_id', $keepProducts)->pluck('filter_id')->unique()->take(3)->values()->all();
    if (count($linkedFilters) < 3) {
        foreach ($DB->table('filters')->limit(3)->pluck('id') as $fid) {
            if (! in_array($fid, $linkedFilters)) $linkedFilters[] = $fid;
            if (count($linkedFilters) >= 3) break;
        }
    }
    $keepFilters = array_map('intval', array_slice($linkedFilters, 0, 3));
    $keepGroups = $DB->table('filters')->whereIn('id', $keepFilters)->whereNotNull('filter_group_id')->pluck('filter_group_id')->unique()->map(fn ($v) => (int) $v)->all();

    $plan = [
        'keep_products' => $keepProducts,
        'keep_categories' => $keepCats,
        'keep_brands' => $keepBrands,
        'keep_filters' => $keepFilters,
        'keep_filter_groups' => $keepGroups,
        'counts_before' => [
            'products' => $DB->table('products')->count(),
            'categories' => $DB->table('categories')->count(),
            'brands' => $DB->table('brands')->count(),
            'filters' => $DB->table('filters')->count(),
            'filter_groups' => $DB->table('filter_groups')->count(),
            'orders' => $DB->table('orders')->count(),
            'filter_products' => $DB->table('filter_products')->count(),
        ],
    ];

    if ($request->query('action') === 'urlcheck') {
        $gen = \Illuminate\Support\Facades\URL::temporarySignedRoute('livewire.upload-file', now()->addMinutes(5));
        return response()->json([
            'app_url' => config('app.url'),
            'request_scheme_host' => $request->getSchemeAndHttpHost(),
            'request_url' => $request->url(),
            'is_secure' => $request->isSecure(),
            'host' => $request->getHost(),
            'port' => $request->getPort(),
            'x_forwarded_proto' => $request->headers->get('x-forwarded-proto'),
            'x_forwarded_port' => $request->headers->get('x-forwarded-port'),
            'x_forwarded_host' => $request->headers->get('x-forwarded-host'),
            'generated_signed_upload_url' => $gen,
            'self_validates' => \Illuminate\Support\Facades\Request::create($gen)->hasValidSignature(),
        ]);
    }

    if ($request->query('action') === 'clean-orphans') {
        // Сміття від видалених товарів + логи/кеш. НП/Укрпошта-довідники НЕ чіпаємо.
        $out = [];
        $DB->statement('SET FOREIGN_KEY_CHECKS=0');
        try {
            $out['seo_meta'] = $DB->table('seo_meta')
                ->where('seoable_type', 'like', '%Product%')
                ->whereNotIn('seoable_id', $keepProducts ?: [0])->delete();
            if (\Schema::hasTable('inventory')) {
                $out['inventory'] = $DB->table('inventory')->whereNotIn('product_id', $keepProducts ?: [0])->delete();
            }
            foreach (['shipping_api_logs', 'cache', 'cache_locks', 'sessions', 'telescope_entries', 'failed_jobs', 'jobs'] as $tbl) {
                if (\Schema::hasTable($tbl)) {
                    try { $out[$tbl] = $DB->table($tbl)->delete(); } catch (\Throwable $e) { $out[$tbl] = 'ERR '.mb_substr($e->getMessage(), 0, 80); }
                }
            }
        } finally {
            $DB->statement('SET FOREIGN_KEY_CHECKS=1');
        }
        try { app(\Spatie\ResponseCache\ResponseCache::class)->clear(); } catch (\Throwable) {}
        return response()->json(['cleaned' => $out, 'kept_products' => $keepProducts]);
    }

    if ($request->query('action') === 'sizes') {
        $db = $DB->getDatabaseName();
        $rows = $DB->select(
            'SELECT table_name AS t, table_rows AS nrows, ROUND((data_length+index_length)/1024) AS kb '
            .'FROM information_schema.tables WHERE table_schema = ? ORDER BY (data_length+index_length) DESC LIMIT 25',
            [$db]
        );
        return response()->json(['top_tables' => $rows]);
    }

    if ($request->query('action') !== 'exec') {
        return response()->json(['mode' => 'plan (dry-run)', 'plan' => $plan]);
    }

    // --- Виконання (hard-delete, FK off). Покроково з логом — щоб бачити, де падає.
    $steps = [];
    $del = function (string $label, \Closure $fn) use (&$steps) {
        try {
            $steps[$label] = $fn();
        } catch (\Throwable $e) {
            $steps[$label] = 'ERROR: '.mb_substr($e->getMessage(), 0, 200);
        }
    };

    $DB->statement('SET FOREIGN_KEY_CHECKS=0');
    try {
        $del('order_products', fn () => $DB->table('order_products')->delete());
        $del('orders', fn () => $DB->table('orders')->delete());
        $del('filter_products', fn () => $DB->table('filter_products')->where(function ($q) use ($keepProducts, $keepFilters) {
            $q->whereNotIn('product_id', $keepProducts ?: [0])->orWhereNotIn('filter_id', $keepFilters ?: [0]);
        })->delete());
        if (\Schema::hasTable('product_compatibility')) {
            $del('product_compatibility', fn () => $DB->table('product_compatibility')->whereNotIn('product_id', $keepProducts ?: [0])->delete());
        }
        if (\Schema::hasTable('category_filters')) {
            $del('category_filters', fn () => $DB->table('category_filters')->where(function ($q) use ($keepCats, $keepFilters) {
                $q->whereNotIn('category_id', $keepCats ?: [0])->orWhereNotIn('filter_id', $keepFilters ?: [0]);
            })->delete());
        }
        $del('products', fn () => $DB->table('products')->whereNotIn('id', $keepProducts ?: [0])->delete());
        $del('categories', fn () => $DB->table('categories')->whereNotIn('id', $keepCats ?: [0])->delete());
        $del('brands', fn () => $DB->table('brands')->whereNotIn('id', $keepBrands ?: [0])->delete());
        $del('filters', fn () => $DB->table('filters')->whereNotIn('id', $keepFilters ?: [0])->delete());
        $del('filter_groups', fn () => $DB->table('filter_groups')->whereNotIn('id', $keepGroups ?: [0])->delete());
    } finally {
        $DB->statement('SET FOREIGN_KEY_CHECKS=1');
    }

    foreach (['gazu-menu', 'storefront'] as $tag) {
        try { \Illuminate\Support\Facades\Cache::tags([$tag])->flush(); } catch (\Throwable) {}
    }
    try { app(\Spatie\ResponseCache\ResponseCache::class)->clear(); } catch (\Throwable) {}
    try { \Illuminate\Support\Facades\Artisan::call('scout:flush', ['model' => \App\Models\Product::class]); } catch (\Throwable) {}
    try { \Illuminate\Support\Facades\Artisan::call('scout:import', ['model' => \App\Models\Product::class]); } catch (\Throwable) {}

    return response()->json([
        'ok' => true,
        'steps' => $steps,
        'plan' => $plan,
        'counts_after' => [
            'products' => $DB->table('products')->count(),
            'categories' => $DB->table('categories')->count(),
            'brands' => $DB->table('brands')->count(),
            'filters' => $DB->table('filters')->count(),
            'filter_groups' => $DB->table('filter_groups')->count(),
            'orders' => $DB->table('orders')->count(),
            'filter_products' => $DB->table('filter_products')->count(),
        ],
        'note' => 'Hard-delete виконано. Авто-сидер off. Бекап: storage/app/backups/db-*.sql.gz + catalog-*.json',
    ]);
})->middleware(['web', 'auth']);

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
    Route::get('/zapchastyny/{make}', [$c, 'catalogByCar'])->name('catalog.by-make')
        ->where('make', '[a-z0-9][a-z0-9-]*');
    Route::get('/zapchastyny/{make}/{model}', [$c, 'catalogByCar'])->name('catalog.by-model')
        ->where(['make' => '[a-z0-9][a-z0-9-]*', 'model' => '[a-z0-9][a-z0-9-]*']);
    Route::get('/zapchastyny/{make}/{model}/{engine}', [$c, 'catalogByCar'])->name('catalog.by-engine')
        ->where(['make' => '[a-z0-9][a-z0-9-]*', 'model' => '[a-z0-9][a-z0-9-]*', 'engine' => '[a-z0-9][a-z0-9-\.]*']);

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
