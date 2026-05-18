<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Services\Integrations\IntegrationManager::class, function () {
            $manager = new \App\Services\Integrations\IntegrationManager();

            $manager->register('liqpay', new \App\Services\Integrations\Concrete\LiqPayIntegration());
            $manager->register('wayforpay', new \App\Services\Integrations\Concrete\WayForPayIntegration());
            $manager->register('monobank', new \App\Services\Integrations\Concrete\MonobankIntegration());
            $manager->register('novaposhta', new \App\Services\Integrations\Concrete\NovaPoshtaIntegration());
            $manager->register('ukrposhta', new \App\Services\Integrations\Concrete\UkrPoshtaIntegration());
            $manager->register('meest', new \App\Services\Integrations\Concrete\MeestIntegration());
            $manager->register('checkbox', new \App\Services\Integrations\Concrete\CheckboxIntegration());
            $manager->register('google_analytics', new \App\Services\Integrations\Concrete\GoogleAnalyticsIntegration());
            $manager->register('facebook_pixel', new \App\Services\Integrations\Concrete\FacebookPixelIntegration());
            $manager->register('telegram', new \App\Services\Integrations\Concrete\TelegramBotIntegration());
            $manager->register('google_shopping', new \App\Services\Integrations\Concrete\GoogleShoppingIntegration());
            $manager->register('meilisearch', new \App\Services\Integrations\Concrete\MeilisearchIntegration());
            $manager->register('tinypng', new \App\Services\Integrations\Concrete\TinyPngIntegration());

            return $manager;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(!app()->isProduction());
        Model::handleLazyLoadingViolationUsing(function (Model $model, string $relation) {
            \Log::warning("Lazy loading [{$relation}] on " . get_class($model));
        });

        // Force HTTPS asset/route URLs when behind a TLS-terminating proxy
        // (Traefik / Caddy / nginx). Without this, Vite-rendered <link href>
        // and asset() helpers emit http:// — browsers then block mixed
        // content or follow the 302 to https (CSS sometimes fails to apply).
        if (str_starts_with((string) config('app.url'), 'https://')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Custom Livewire synthesizer for models with HasTranslations
        \Livewire\Livewire::propertySynthesizer(\App\Livewire\Synthesizers\TranslatableModelSynth::class);

        \App\Models\Order::observe(\App\Observers\OrderObserver::class);
        \App\Models\Order::observe(\App\Observers\OrderNotificationObserver::class);
        \App\Models\Category::observe(\App\Observers\CategoryObserver::class);
        \App\Models\Brand::observe(\App\Observers\BrandObserver::class);
        \App\Models\Product::observe(\App\Observers\ProductObserver::class);
        // Shipped notifications — fire 'order.shipped' email коли admin створює TTN.
        if (class_exists(\App\Models\NpShipment::class)) {
            \App\Models\NpShipment::observe(\App\Observers\ShipmentNotificationObserver::class);
        }
        if (class_exists(\App\Models\UpShipment::class)) {
            \App\Models\UpShipment::observe(\App\Observers\ShipmentNotificationObserver::class);
        }

        // Auto-invalidate Spatie ResponseCache on any storefront-visible model change.
        // Wire all models whose data appears on public pages — saved/deleted/restored
        // triggers ResponseCache::clear() (Redis-tagged: 'gazu-response').
        $responseCacheModels = [
            \App\Models\Product::class,
            \App\Models\Category::class,
            \App\Models\Brand::class,
            \App\Models\InfoPage::class,
            \App\Models\Page::class,
            \App\Models\DisplaySetting::class,
            \App\Models\MerchantWarehouse::class,
            \App\Models\Inventory::class,
            \App\Models\CarMake::class,
            \App\Models\CarModel::class,
            \App\Models\CarEngine::class,
        ];
        foreach ($responseCacheModels as $model) {
            if (class_exists($model)) {
                $model::observe(\App\Observers\ResponseCacheObserver::class);
            }
        }

        \Illuminate\Support\Facades\Event::listen(
            \App\Events\NpShipmentStatusChanged::class,
            \App\Listeners\NotifyCustomerOnStatusChange::class,
        );

        $this->configureRateLimiting();

        $this->registerViewComposers();
    }

    /**
     * Register view composers for the application.
     */
    protected function registerViewComposers(): void
    {
        // GAZU storefront — share menu, cart count, computed shop stats and
        // visual settings into every gazu view + every gazu blade component.
        view()->composer(
            ['gazu.*', 'components.gazu.*'],
            \App\View\Composers\GazuMenuComposer::class,
        );
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        // Login attempts limiter - 5 attempts per minute
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip().'|'.$request->input('email'));
        });

        // Registration limiter - 3 attempts per minute
        RateLimiter::for('register', function (Request $request) {
            return Limit::perMinute(3)->by($request->ip());
        });

        // Password reset limiter - 2 attempts per minute
        RateLimiter::for('password-reset', function (Request $request) {
            return Limit::perMinute(2)->by($request->ip().'|'.$request->input('email'));
        });

        // General API limiter - 60 requests per minute
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Global limiter for all routes - 1000 requests per minute
        RateLimiter::for('global', function (Request $request) {
            return Limit::perMinute(1000)->by($request->ip());
        });

        // Checkout limiter - 5 orders per minute
        RateLimiter::for('checkout', function (Request $request) {
            return Limit::perMinute(5)->by($request->user()?->id ?: $request->ip());
        });
    }
}
