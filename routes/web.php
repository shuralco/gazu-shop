<?php

use Illuminate\Support\Facades\Route;

// GAZU shop — root delegates to gazu home directly (fork from brutal codebase).
Route::get('/', function () {
    return redirect()->route('gazu.home');
});

// All public routes with locale prefix
Route::prefix('{locale}')
    ->where(['locale' => implode('|', config('app.available_locales', ['uk', 'en']))])
    ->middleware(['web', 'throttle:global', 'set-locale'])
    ->group(function () {
        Route::get('/', \App\Livewire\HomeComponent::class)->name('home');
        Route::get('/specials', \App\Livewire\Product\SpecialsComponent::class)->name('specials');
        Route::get('/hits', \App\Livewire\Product\HitsComponent::class)->name('hits');
        Route::get('/new', \App\Livewire\Product\NewProductsComponent::class)->name('new');
        Route::get('/cart', function () {
            return redirect()->route('checkout', ['locale' => app()->getLocale()]);
        });
        Route::get('/search', \App\Livewire\Search\SearchComponent::class)->name('search');
        Route::get('/brands', \App\Livewire\Product\BrandsComponent::class)->name('brands');
        Route::get('/comparison', \App\Livewire\Product\ComparisonComponent::class)->name('comparison');

        // Public TTN tracking page (no auth required)
        Route::get('/track/{ttn?}', \App\Livewire\Tracking\TrackingComponent::class)->name('tracking');

        // CMS Pages
        Route::get('/page/{slug}', \App\Livewire\PageComponent::class)->name('page');

        // Mobile test route
        Route::match(['GET', 'POST'], '/mobile-test', function () {
            return view('mobile-test');
        })->name('mobile-test');
    });

// Checkout route with specific rate limiting (allow guests)
Route::prefix('{locale}')
    ->where(['locale' => implode('|', config('app.available_locales', ['uk', 'en']))])
    ->middleware(['web', 'throttle:checkout', 'set-locale'])
    ->group(function () {
        Route::get('/checkout', \App\Livewire\Cart\CheckoutComponent::class)->name('checkout');
    });

// Product routes (longer/complex slugs with numbers) - higher priority
Route::prefix('{locale}')
    ->where(['locale' => implode('|', config('app.available_locales', ['uk', 'en']))])
    ->middleware(['web', 'throttle:global', 'set-locale'])
    ->group(function () {
        Route::get('/{product_slug}', \App\Livewire\Product\ProductComponent::class)
            ->name('product')
            ->where('product_slug', '[a-z0-9\-]*[0-9]+[a-z0-9\-]*');
    });

// Brand routes - specific brand pages
Route::prefix('{locale}')
    ->where(['locale' => implode('|', config('app.available_locales', ['uk', 'en']))])
    ->middleware(['web', 'throttle:global', 'set-locale'])
    ->group(function () {
        Route::get('/brands/{brand:slug}', \App\Livewire\Product\BrandComponent::class)->name('brand');
    });

// Offline page (PWA)
Route::prefix('{locale}')
    ->where(['locale' => implode('|', config('app.available_locales', ['uk', 'en']))])
    ->middleware(['web', 'set-locale'])
    ->group(function () {
        Route::get('/offline', fn() => view('offline'))->name('offline');
    });

// Legal pages (public, no auth required)
Route::prefix('{locale}')
    ->where(['locale' => implode('|', config('app.available_locales', ['uk', 'en']))])
    ->middleware(['web', 'set-locale'])
    ->group(function () {
        Route::get('/privacy', \App\Livewire\Pages\PrivacyPolicyComponent::class)->name('privacy');
        Route::get('/terms', \App\Livewire\Pages\TermsComponent::class)->name('terms');
        Route::get('/returns', \App\Livewire\Pages\ReturnPolicyComponent::class)->name('returns');
        Route::get('/offer', \App\Livewire\Pages\PublicOfferComponent::class)->name('offer');
    });

// Guest routes with specific rate limiting for authentication
Route::prefix('{locale}')
    ->where(['locale' => implode('|', config('app.available_locales', ['uk', 'en']))])
    ->middleware(['web', 'guest', 'set-locale'])
    ->group(function () {
        Route::get('/register', \App\Livewire\User\RegisterComponent::class)
            ->name('register')
            ->middleware('throttle:register');
        Route::get('/login', \App\Livewire\User\LoginComponent::class)
            ->name('login')
            ->middleware('throttle:login');
    });

// Authenticated routes (MUST be before category catch-all)
Route::prefix('{locale}')
    ->where(['locale' => implode('|', config('app.available_locales', ['uk', 'en']))])
    ->middleware(['web', 'auth', 'set-locale'])
    ->group(function () {
        Route::get('/logout', function () {
            auth()->logout();
            return redirect()->route('login', ['locale' => app()->getLocale()]);
        })->name('logout');
        Route::get('/account', \App\Livewire\User\AccountComponent::class)->name('account');
        Route::get('/change-account', \App\Livewire\User\ChangeAccountComponent::class)->name('change-account');
        Route::get('/orders', \App\Livewire\User\OrderComponent::class)->name('orders');
        Route::get('/order-show/{id}', \App\Livewire\User\OrderShowComponent::class)->name('orders-show');
        Route::get('/wishlist', \App\Livewire\User\WishlistComponent::class)->name('wishlist');
        Route::get('/addresses', \App\Livewire\User\AddressBookComponent::class)->name('addresses');
        Route::get('/loyalty', \App\Livewire\User\LoyaltyComponent::class)->name('loyalty');
        Route::get('/settings', \App\Livewire\User\ProfileSettingsComponent::class)->name('settings');
    });

// Category routes (without numbers) - lower priority, MUST be after explicit routes
Route::prefix('{locale}')
    ->where(['locale' => implode('|', config('app.available_locales', ['uk', 'en']))])
    ->middleware(['web', 'throttle:global', 'set-locale'])
    ->group(function () {
        Route::get('/{category_slug}', \App\Livewire\Product\CategoryComponent::class)
            ->name('category')
            ->where('category_slug', '[a-z\-]+');
    });

// Payment and webhook routes (with locale prefix for user-facing pages)
Route::prefix('{locale}')
    ->where(['locale' => implode('|', config('app.available_locales', ['uk', 'en']))])
    ->middleware(['web', 'set-locale'])
    ->group(function () {
        Route::match(['GET', 'POST'], '/orders/{order}/success', \App\Livewire\Order\OrderSuccessComponent::class)
            ->name('orders.success');

        Route::get('/orders/{order}/payment', \App\Livewire\Payment\PaymentMethodSelector::class)
            ->name('orders.payment')
            ->middleware('auth');
    });

// GAZU theme — паралельний storefront-прев'ю на префіксі /gazu (BEFORE catch-all redirect).
// Не зачіпає чинного storefront. Контролер: App\Http\Controllers\Gazu\StoreController.
Route::prefix('gazu')->name('gazu.')->middleware(['web'])->group(function () {
    $c = \App\Http\Controllers\Gazu\StoreController::class;

    Route::get('/', [$c, 'home'])->name('home');
    Route::get('/v2', [$c, 'home'])->defaults('variant', 'v2')->name('home.v2');
    Route::get('/v3', [$c, 'home'])->defaults('variant', 'v3')->name('home.v3');

    Route::get('/catalog', [$c, 'catalog'])->name('catalog');
    Route::get('/catalog/v2', [$c, 'catalog'])->defaults('variant', 'v2')->name('catalog.v2');
    Route::get('/catalog/v3', [$c, 'catalog'])->defaults('variant', 'v3')->name('catalog.v3');

    Route::get('/product/{slug}', [$c, 'product'])->name('product.show');
    Route::get('/product/{slug}/v2', [$c, 'product'])->defaults('variant', 'v2')->name('product.v2');
    Route::get('/product/{slug}/v3', [$c, 'product'])->defaults('variant', 'v3')->name('product.v3');

    Route::get('/cart', [$c, 'cart'])->name('cart');
    Route::get('/cart/empty', [$c, 'emptyCart'])->name('cart.empty');

    $cart = \App\Http\Controllers\Gazu\CartController::class;
    Route::post('/cart/add',    [$cart, 'add'])->name('cart.add');
    Route::post('/cart/update', [$cart, 'update'])->name('cart.update');
    Route::post('/cart/remove', [$cart, 'remove'])->name('cart.remove');
    Route::post('/cart/clear',  [$cart, 'clear'])->name('cart.clear');

    $checkout = \App\Http\Controllers\Gazu\CheckoutController::class;
    Route::get('/checkout', [$checkout, 'index'])->name('checkout');
    Route::post('/checkout', [$checkout, 'store'])->name('checkout.store');
    Route::get('/checkout/success/{order}', [$checkout, 'success'])->name('checkout.success');
    Route::post('/checkout/one-click', [$checkout, 'oneClick'])->name('checkout.one-click');

    $auth = \App\Http\Controllers\Gazu\AuthController::class;
    Route::get('/auth', [$auth, 'show'])->name('auth');
    Route::post('/auth/login', [$auth, 'login'])->name('auth.login');
    Route::post('/auth/register', [$auth, 'register'])->name('auth.register');
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
    Route::get('/vin', [$c, 'vin'])->name('vin');
    Route::get('/search', [$c, 'search'])->name('search');
    Route::get('/api/search/suggest', [$c, 'searchSuggest'])->name('search.suggest');
    Route::get('/api/np/cities', [$c, 'npCities'])->name('api.np.cities');
    Route::get('/api/np/warehouses', [$c, 'npWarehouses'])->name('api.np.warehouses');
    Route::get('/api/np/streets', [$c, 'npStreets'])->name('api.np.streets');
    Route::get('/api/np/calculate', [$c, 'npCalculate'])->name('api.np.calculate');
    Route::get('/api/up/cities', [$c, 'upCities'])->name('api.up.cities');
    Route::get('/api/up/post-offices', [$c, 'upPostOffices'])->name('api.up.post-offices');
    Route::get('/404', [$c, 'notFound'])->name('404');

    Route::get('/m/{page}', [$c, 'mobile'])->name('mobile');
});

// Backward compatibility: redirect old non-prefixed URLs to locale-prefixed versions
Route::middleware(['web'])->group(function () {
    $publicPaths = [
        'specials', 'hits', 'new', 'search', 'brands', 'comparison',
        'checkout', 'privacy', 'terms', 'returns', 'offer',
        'login', 'register', 'account', 'logout', 'orders', 'wishlist',
        'addresses', 'loyalty', 'settings', 'change-account', 'mobile-test',
    ];
    foreach ($publicPaths as $path) {
        Route::get('/' . $path, function () use ($path) {
            return redirect('/' . app()->getLocale() . '/' . $path, 301);
        });
    }

    // Catch-all for old product/category slugs without locale prefix
    // (skip 'gazu' — it has its own route group above)
    Route::get('/{slug}', function (string $slug) {
        $available = config('app.available_locales', ['uk', 'en']);
        if (in_array($slug, $available)) {
            return redirect('/' . $slug);
        }
        if ($slug === 'gazu') {
            abort(404);
        }
        return redirect('/' . app()->getLocale() . '/' . $slug, 301);
    })->where('slug', '[a-z0-9\-]+');
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

// SEO and Sitemap routes (no locale prefix)
Route::withoutMiddleware(['web'])->group(function () {
    Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap.index');
    Route::get('/sitemap-main.xml', [\App\Http\Controllers\SitemapController::class, 'main'])->name('sitemap.main');
    Route::get('/sitemap-categories.xml', [\App\Http\Controllers\SitemapController::class, 'categories'])->name('sitemap.categories');
    Route::get('/sitemap-products.xml', [\App\Http\Controllers\SitemapController::class, 'products'])->name('sitemap.products');
    Route::get('/robots.txt', [\App\Http\Controllers\SitemapController::class, 'robotsTxt'])->name('robots.txt');
});
Route::post('/sitemap/clear-cache', [\App\Http\Controllers\SitemapController::class, 'clearCache'])->name('sitemap.clear-cache');

// Webhook routes (no CSRF, no locale prefix)
Route::post('/webhooks/liqpay', [\App\Http\Controllers\WebhookController::class, 'liqpay'])
    ->name('webhooks.liqpay')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

Route::post('/webhooks/wayforpay', [\App\Http\Controllers\WebhookController::class, 'wayforpay'])
    ->name('webhooks.wayforpay')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

Route::post('/webhooks/monobank', [\App\Http\Controllers\WebhookController::class, 'monobank'])
    ->name('webhooks.monobank')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

// Nova Poshta status push webhook
Route::post('/api/np-webhook', \App\Http\Controllers\NpWebhookController::class)
    ->name('webhooks.np')
    ->withoutMiddleware([
        \App\Http\Middleware\VerifyCsrfToken::class,
        \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
    ]);

// Admin routes
require __DIR__.'/admin.php';
