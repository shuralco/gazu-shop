<?php

namespace App\Providers;

use App\Services\Shipping\NovaPoshtaProvider;
use App\Services\Shipping\ShippingOrchestrator;
use Illuminate\Support\ServiceProvider;

/**
 * Service Provider для системи доставки
 */
class ShippingServiceProvider extends ServiceProvider
{
    /**
     * Реєстрація сервісів
     */
    public function register(): void
    {
        // Реєструємо основний оркестратор
        $this->app->singleton(ShippingOrchestrator::class, function ($app) {
            return new ShippingOrchestrator;
        });

        // Реєструємо провайдери доставки
        $this->app->bind('shipping.novaposhta', NovaPoshtaProvider::class);

        // Реєструємо провайдери в контейнері через тег
        $this->app->tag([
            NovaPoshtaProvider::class,
        ], 'shipping.providers');

        // Створюємо алиас для зручного доступу
        $this->app->alias(ShippingOrchestrator::class, 'shipping');
    }

    /**
     * Завантаження сервісів
     */
    public function boot(): void
    {
        // Публікуємо конфігурацію
        $this->publishes([
            __DIR__.'/../../config/novaposhta.php' => config_path('novaposhta.php'),
        ], 'shipping-config');

        // Публікуємо міграції
        $this->publishes([
            __DIR__.'/../../database/migrations' => database_path('migrations'),
        ], 'shipping-migrations');

        // Реєструємо команди Artisan якщо потрібно
        if ($this->app->runningInConsole()) {
            $this->commands([
                // Тут будуть команди для роботи з доставкою
            ]);
        }
    }

    /**
     * Отримати сервіси що надаються
     */
    public function provides(): array
    {
        return [
            ShippingOrchestrator::class,
            'shipping',
        ];
    }
}
