<?php

namespace Database\Seeders;

use App\Models\ShopSettings;
use Illuminate\Database\Seeder;

class ShopSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Загальні налаштування
            ['key' => 'shop_name', 'value' => 'SimpleShop', 'type' => 'string', 'group' => 'general', 'description' => 'Назва інтернет-магазину', 'is_public' => true],
            ['key' => 'shop_description', 'value' => 'Сучасний інтернет-магазин з широким асортиментом товарів', 'type' => 'string', 'group' => 'general', 'description' => 'Опис магазину для SEO', 'is_public' => true],
            ['key' => 'shop_email', 'value' => 'admin@simpleshop.com', 'type' => 'string', 'group' => 'general', 'description' => 'Основний email магазину', 'is_public' => true],
            ['key' => 'shop_phone', 'value' => '+380123456789', 'type' => 'string', 'group' => 'general', 'description' => 'Телефон для зв\'язку', 'is_public' => true],
            ['key' => 'shop_address', 'value' => 'вул. Хрещатик, 1, Київ, Україна', 'type' => 'string', 'group' => 'general', 'description' => 'Адреса магазину', 'is_public' => true],

            // Meta теги
            ['key' => 'meta_title', 'value' => 'SimpleShop - Інтернет-магазин', 'type' => 'string', 'group' => 'seo', 'description' => 'Заголовок для пошукових систем', 'is_public' => true],
            ['key' => 'meta_description', 'value' => 'Купуйте якісні товари в нашому інтернет-магазині. Швидка доставка по всій Україні.', 'type' => 'string', 'group' => 'seo', 'description' => 'Опис для пошукових систем', 'is_public' => true],
            ['key' => 'meta_keywords', 'value' => 'інтернет-магазин, товари, доставка, україна', 'type' => 'string', 'group' => 'seo', 'description' => 'Ключові слова', 'is_public' => true],

            // Налаштування замовлень
            ['key' => 'auto_order_confirmation', 'value' => '0', 'type' => 'boolean', 'group' => 'orders', 'description' => 'Автоматично підтверджувати замовлення'],
            ['key' => 'default_order_status', 'value' => 'pending', 'type' => 'string', 'group' => 'orders', 'description' => 'Статус нових замовлень за замовчуванням'],
            ['key' => 'min_order_amount', 'value' => '50', 'type' => 'float', 'group' => 'orders', 'description' => 'Мінімальна сума замовлення'],
            ['key' => 'free_shipping_threshold', 'value' => '1000', 'type' => 'float', 'group' => 'orders', 'description' => 'Безкоштовна доставка від суми'],

            // Налаштування оплати
            ['key' => 'default_currency', 'value' => 'UAH', 'type' => 'string', 'group' => 'payment', 'description' => 'Валюта за замовчуванням'],
            ['key' => 'allow_cash_payment', 'value' => '1', 'type' => 'boolean', 'group' => 'payment', 'description' => 'Дозволити оплату готівкою при отриманні'],
            ['key' => 'require_phone_for_order', 'value' => '1', 'type' => 'boolean', 'group' => 'payment', 'description' => 'Обов\'язковий телефон при замовленні'],
            ['key' => 'payment_timeout_minutes', 'value' => '60', 'type' => 'integer', 'group' => 'payment', 'description' => 'Час на оплату до скасування (хвилини)'],

            // Налаштування доставки
            ['key' => 'default_shipping_cost', 'value' => '50', 'type' => 'float', 'group' => 'shipping', 'description' => 'Вартість доставки за замовчуванням'],
            ['key' => 'calculate_shipping_automatically', 'value' => '1', 'type' => 'boolean', 'group' => 'shipping', 'description' => 'Автоматичний розрахунок доставки через API'],
            ['key' => 'default_shipping_provider', 'value' => 'novaposhta', 'type' => 'string', 'group' => 'shipping', 'description' => 'Провайдер доставки за замовчуванням'],

            // Налаштування безпеки
            ['key' => 'enable_captcha', 'value' => '0', 'type' => 'boolean', 'group' => 'security', 'description' => 'Увімкнути CAPTCHA для форм'],
            ['key' => 'webhook_ip_whitelist', 'value' => '1', 'type' => 'boolean', 'group' => 'security', 'description' => 'Перевіряти IP адреси webhook\'ів'],
            ['key' => 'session_lifetime', 'value' => '120', 'type' => 'integer', 'group' => 'security', 'description' => 'Час життя сесії (хвилини)'],
            ['key' => 'enable_admin_2fa', 'value' => '0', 'type' => 'boolean', 'group' => 'security', 'description' => 'Двофакторна автентифікація для адміністраторів'],

            // Email налаштування
            ['key' => 'send_order_emails', 'value' => '1', 'type' => 'boolean', 'group' => 'email', 'description' => 'Надсилати email при замовленні'],
            ['key' => 'send_payment_emails', 'value' => '1', 'type' => 'boolean', 'group' => 'email', 'description' => 'Надсилати email при оплаті'],
            ['key' => 'admin_notification_email', 'value' => 'admin@simpleshop.com', 'type' => 'string', 'group' => 'email', 'description' => 'Email для сповіщень адміна'],

            // Додаткові налаштування які використовуються в формі
            ['key' => 'enable_pickup_points', 'value' => '1', 'type' => 'boolean', 'group' => 'shipping', 'description' => 'Дозволити пункти самовивозу'],
            ['key' => 'max_login_attempts', 'value' => '5', 'type' => 'integer', 'group' => 'security', 'description' => 'Максимум спроб входу'],
            ['key' => 'enable_rate_limiting', 'value' => '1', 'type' => 'boolean', 'group' => 'security', 'description' => 'Обмеження частоти запитів'],
            ['key' => 'enable_analytics', 'value' => '1', 'type' => 'boolean', 'group' => 'analytics', 'description' => 'Збирати аналітику'],
            ['key' => 'google_analytics_id', 'value' => '', 'type' => 'string', 'group' => 'analytics', 'description' => 'Google Analytics ID'],
            ['key' => 'facebook_pixel_id', 'value' => '', 'type' => 'string', 'group' => 'analytics', 'description' => 'Facebook Pixel ID'],
            ['key' => 'enable_order_tracking', 'value' => '1', 'type' => 'boolean', 'group' => 'analytics', 'description' => 'Відстеження замовлень'],
        ];

        foreach ($settings as $setting) {
            ShopSettings::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
