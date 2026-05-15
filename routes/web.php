<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| GAZU shop — fork of brutal-codebase, /gazu storefront only.
| Brutal /uk storefront removed in cleanup (2026-05-09).
|--------------------------------------------------------------------------
*/

// GAZU storefront — root-level URLs (no /gazu prefix, this fork is GAZU-only).
Route::name('gazu.')->middleware(['web'])->group(function () {
    $c = \App\Http\Controllers\Gazu\StoreController::class;

    Route::get('/', [$c, 'home'])->name('home');
    Route::get('/v2', [$c, 'home'])->defaults('variant', 'v2')->name('home.v2');
    Route::get('/v3', [$c, 'home'])->defaults('variant', 'v3')->name('home.v3');

    Route::get('/catalog', [$c, 'catalog'])->name('catalog');
    Route::get('/catalog/v2', [$c, 'catalog'])->defaults('variant', 'v2')->name('catalog.v2');
    Route::get('/catalog/v3', [$c, 'catalog'])->defaults('variant', 'v3')->name('catalog.v3');

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

    $auth = \App\Http\Controllers\Gazu\AuthController::class;
    Route::get('/auth', [$auth, 'show'])->name('auth');
    Route::middleware('throttle:10,1')->group(function () use ($auth) {
        Route::post('/auth/login', [$auth, 'login'])->name('auth.login');
        Route::post('/auth/register', [$auth, 'register'])->name('auth.register');
    });
    Route::post('/auth/logout', [$auth, 'logout'])->name('auth.logout');

    Route::middleware('auth')->group(function () use ($c) {
        Route::get('/account', [$c, 'account'])->name('account');
        Route::get('/account/orders/{order}', [$c, 'orderDetails'])->name('account.order');

        $garage = \App\Http\Controllers\Gazu\GarageController::class;
        Route::get('/garage', [$garage, 'index'])->name('garage');
        Route::post('/garage', [$garage, 'store'])->name('garage.store');
        Route::post('/garage/{car}', [$garage, 'update'])->name('garage.update');
        Route::post('/garage/{car}/primary', [$garage, 'makePrimary'])->name('garage.primary');
        Route::delete('/garage/{car}', [$garage, 'destroy'])->name('garage.destroy');
    });

    Route::get('/brand/{slug?}', [$c, 'brand'])->name('brand');

    $wish = \App\Http\Controllers\Gazu\WishlistController::class;
    Route::get('/wishlist', [$wish, 'index'])->name('wishlist');
    Route::post('/wishlist/toggle', [$wish, 'toggle'])->name('wishlist.toggle');

    Route::get('/sto', [$c, 'sto'])->name('sto');
    Route::get('/blog', [$c, 'blog'])->name('blog');
    Route::get('/blog/{slug}', [$c, 'blog'])->name('blog.show');
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

    Route::get('/404', [$c, 'notFound'])->name('404');
    Route::get('/m/{page}', [$c, 'mobile'])->name('mobile');

    // Static info pages — all served by InfoController which reads from the
    // `info_pages` table (editable in the Filament admin) and falls back to
    // hard-coded content when a row is missing.
    Route::get('/about',        [\App\Http\Controllers\Gazu\InfoController::class, 'show'])->defaults('slug', 'about')->name('about');
    Route::get('/delivery',     [\App\Http\Controllers\Gazu\InfoController::class, 'show'])->defaults('slug', 'delivery')->name('delivery');
    Route::get('/warranty',     [\App\Http\Controllers\Gazu\InfoController::class, 'show'])->defaults('slug', 'warranty')->name('warranty');
    Route::get('/privacy',      [\App\Http\Controllers\Gazu\InfoController::class, 'show'])->defaults('slug', 'privacy')->name('privacy');
    Route::get('/terms',        [\App\Http\Controllers\Gazu\InfoController::class, 'show'])->defaults('slug', 'terms')->name('terms');
    Route::get('/wholesale',    [\App\Http\Controllers\Gazu\InfoController::class, 'show'])->defaults('slug', 'wholesale')->name('wholesale');
    Route::get('/faq',          [\App\Http\Controllers\Gazu\InfoController::class, 'show'])->defaults('slug', 'faq')->name('faq');
    Route::get('/loyalty',      [\App\Http\Controllers\Gazu\InfoController::class, 'show'])->defaults('slug', 'loyalty')->name('loyalty');
    Route::get('/careers',      [\App\Http\Controllers\Gazu\InfoController::class, 'show'])->defaults('slug', 'careers')->name('careers');
    Route::get('/certificates', [\App\Http\Controllers\Gazu\InfoController::class, 'show'])->defaults('slug', 'certificates')->name('certificates');
    Route::get('/offer',        [\App\Http\Controllers\Gazu\InfoController::class, 'show'])->defaults('slug', 'offer')->name('offer');

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
    Route::get('/robots.txt', [\App\Http\Controllers\SitemapController::class, 'robotsTxt'])->name('robots.txt');
});
Route::post('/sitemap/clear-cache', [\App\Http\Controllers\SitemapController::class, 'clearCache'])->name('sitemap.clear-cache');

// Payment + delivery webhooks (no CSRF)
Route::post('/webhooks/liqpay', [\App\Http\Controllers\WebhookController::class, 'liqpay'])
    ->name('webhooks.liqpay')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

Route::post('/webhooks/wayforpay', [\App\Http\Controllers\WebhookController::class, 'wayforpay'])
    ->name('webhooks.wayforpay')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

Route::post('/webhooks/monobank', [\App\Http\Controllers\WebhookController::class, 'monobank'])
    ->name('webhooks.monobank')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

Route::post('/api/np-webhook', \App\Http\Controllers\NpWebhookController::class)
    ->name('webhooks.np')
    ->withoutMiddleware([
        \App\Http\Middleware\VerifyCsrfToken::class,
        \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
    ]);

// Filament admin
require __DIR__.'/admin.php';
