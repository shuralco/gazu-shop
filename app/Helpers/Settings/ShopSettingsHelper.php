<?php

namespace App\Helpers\Settings;

use App\Models\ShopSettings;
use Illuminate\Support\Facades\Cache;

class ShopSettingsHelper
{
    public static function get(string $key, $default = null)
    {
        return Cache::remember("shop_setting_{$key}", 3600, function () use ($key, $default) {
            return ShopSettings::get($key, $default);
        });
    }

    public static function set(string $key, $value, string $type = 'string', string $group = 'general', ?string $description = null): void
    {
        ShopSettings::set($key, $value, $type, $group, $description);
        Cache::forget("shop_setting_{$key}");
    }

    public static function getShopInfo(): array
    {
        return [
            'name' => static::get('shop_name', 'SimpleShop'),
            'description' => static::get('shop_description', ''),
            'email' => static::get('shop_email', 'admin@simpleshop.com'),
            'phone' => static::get('shop_phone', '+380000000000'),
            'address' => static::get('shop_address', ''),
            'logo' => static::get('shop_logo', ''),
        ];
    }

    public static function getPaymentSettings(): array
    {
        return [
            'currency' => static::get('default_currency', 'UAH'),
            'allow_cash' => static::get('allow_cash_payment', false),
            'require_phone' => static::get('require_phone_for_order', true),
            'timeout_minutes' => static::get('payment_timeout_minutes', 60),
        ];
    }

    public static function getShippingSettings(): array
    {
        return [
            'default_cost' => static::get('default_shipping_cost', 50),
            'auto_calculate' => static::get('calculate_shipping_automatically', true),
            'default_provider' => static::get('default_shipping_provider', 'novaposhta'),
            'free_threshold' => static::get('free_shipping_threshold', 1000),
        ];
    }

    public static function getSecuritySettings(): array
    {
        return [
            'captcha_enabled' => static::get('enable_captcha', false),
            'webhook_ip_check' => static::get('webhook_ip_whitelist', true),
            'session_lifetime' => static::get('session_lifetime', 120),
            'admin_2fa' => static::get('enable_admin_2fa', false),
        ];
    }

    public static function getEmailSettings(): array
    {
        return [
            'send_order_emails' => static::get('send_order_emails', true),
            'send_payment_emails' => static::get('send_payment_emails', true),
            'admin_email' => static::get('admin_notification_email', ''),
        ];
    }
}
