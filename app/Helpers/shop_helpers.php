<?php

use App\Helpers\Settings\ShopSettingsHelper;

if (! function_exists('plural_uk')) {
    /**
     * Українська плюралізація: 1 → one, 2-4 → few, 5+ → many.
     * Враховує особливості 11-14 (завжди many).
     */
    function plural_uk(int $n, string $one, string $few, string $many): string
    {
        $mod10 = abs($n) % 10;
        $mod100 = abs($n) % 100;
        if ($mod10 === 1 && $mod100 !== 11) return $one;
        if ($mod10 >= 2 && $mod10 <= 4 && ($mod100 < 12 || $mod100 > 14)) return $few;
        return $many;
    }
}

if (! function_exists('plural_uk_count')) {
    /** "5 товарів" / "1 товар" / "3 товари" */
    function plural_uk_count(int $n, string $one, string $few, string $many): string
    {
        return $n.' '.plural_uk($n, $one, $few, $many);
    }
}

if (! function_exists('shopSetting')) {
    /**
     * Отримати налаштування магазину
     */
    function shopSetting(string $key, $default = null)
    {
        return ShopSettingsHelper::get($key, $default);
    }
}

if (! function_exists('shopName')) {
    /**
     * Отримати назву магазину
     */
    function shopName(): string
    {
        return shopSetting('shop_name', config('app.name', 'SimpleShop'));
    }
}

if (! function_exists('shopEmail')) {
    /**
     * Отримати email магазину
     */
    function shopEmail(): string
    {
        return shopSetting('shop_email', 'admin@simpleshop.com');
    }
}

if (! function_exists('shopPhone')) {
    /**
     * Отримати телефон магазину
     */
    function shopPhone(): string
    {
        return shopSetting('shop_phone', '+380000000000');
    }
}

if (! function_exists('shopCurrency')) {
    /**
     * Отримати валюту магазину
     */
    function shopCurrency(): string
    {
        return shopSetting('default_currency', 'UAH');
    }
}

if (! function_exists('formatPrice')) {
    /**
     * Форматувати ціну з валютою (з підтримкою мультивалютності)
     */
    function formatPrice(float $price, ?string $currency = null): string
    {
        try {
            return app(\App\Services\Currency\CurrencyService::class)->convertAndFormat($price, $currency);
        } catch (\Throwable $e) {
            // Fallback if CurrencyService is not available
            $cur = shopCurrency();

            return match ($cur) {
                'UAH' => number_format($price, 0, '.', ' ').' ₴',
                'USD' => '$'.number_format($price, 2),
                'EUR' => '€'.number_format($price, 2),
                default => number_format($price, 2).' '.$cur,
            };
        }
    }
}

if (! function_exists('minOrderAmount')) {
    /**
     * Отримати мінімальну суму замовлення
     */
    function minOrderAmount(): float
    {
        return (float) shopSetting('min_order_amount', 50);
    }
}

if (! function_exists('freeShippingThreshold')) {
    /**
     * Отримати суму для безкоштовної доставки
     */
    function freeShippingThreshold(): float
    {
        return (float) shopSetting('free_shipping_threshold', 1000);
    }
}

if (! function_exists('isPhoneRequired')) {
    /**
     * Чи обов'язковий телефон при замовленні
     */
    function isPhoneRequired(): bool
    {
        return (bool) shopSetting('require_phone_for_order', true);
    }
}

if (! function_exists('allowCashPayment')) {
    /**
     * Чи дозволена оплата готівкою
     */
    function allowCashPayment(): bool
    {
        return (bool) shopSetting('allow_cash_payment', true);
    }
}

if (! function_exists('locale_url')) {
    /**
     * Generate a URL with the current locale prefix
     */
    function locale_url(string $path = '/'): string
    {
        $locale = app()->getLocale();
        return '/' . $locale . '/' . ltrim($path, '/');
    }
}

if (! function_exists('locale_route')) {
    /**
     * Generate a named route URL with the locale parameter
     */
    function locale_route(string $name, array|int|string $params = [], ?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        if (! is_array($params)) {
            $params = ['id' => $params];
        }
        return route($name, array_merge(['locale' => $locale], $params));
    }
}

if (! function_exists('switch_locale_url')) {
    /**
     * Get the current URL with a different locale prefix.
     * For product/category pages, swaps the slug to the target locale's slug.
     */
    function switch_locale_url(string $targetLocale): string
    {
        $path = request()->path();
        $available = config('app.available_locales', ['uk', 'en']);
        $currentLocale = app()->getLocale();

        // Remove current locale prefix
        $pathWithoutLocale = $path;
        foreach ($available as $loc) {
            if (str_starts_with($path, $loc . '/') || $path === $loc) {
                $pathWithoutLocale = substr($path, strlen($loc) + 1) ?: '';
                break;
            }
        }

        $slug = ltrim($pathWithoutLocale, '/');

        // If the slug matches a product or category, swap to target locale slug
        if ($slug && $currentLocale !== $targetLocale) {
            // Try product first
            $product = \App\Models\Product::findBySlug($slug, $currentLocale);
            if ($product) {
                $targetSlug = $product->getLocalizedSlug($targetLocale);
                if ($targetSlug) {
                    return '/' . $targetLocale . '/' . $targetSlug;
                }
            }

            // Try category
            $category = \App\Models\Category::findBySlug($slug, $currentLocale);
            if ($category) {
                $targetSlug = $category->getLocalizedSlug($targetLocale);
                if ($targetSlug) {
                    return '/' . $targetLocale . '/' . $targetSlug;
                }
            }
        }

        return '/' . $targetLocale . '/' . ltrim($pathWithoutLocale, '/');
    }
}
