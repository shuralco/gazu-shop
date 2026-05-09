<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class HomepageModule extends Model
{
    protected $fillable = ['type', 'title', 'settings', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order');
    }

    public function getSetting(string $key, $default = null)
    {
        return data_get($this->settings, $key, $default);
    }

    /**
     * Get a setting value with automatic translation of known default Ukrainian strings.
     * If the stored value matches a known Ukrainian default, return the translated version.
     * Otherwise return the stored value as-is (user-customized content).
     */
    public function getTranslatedSetting(string $key, string $translationKey, $default = null): string
    {
        $value = $this->getSetting($key, $default);

        if ($value === null || $value === '') {
            return __('general.' . $translationKey);
        }

        // Map of known Ukrainian defaults to their translation keys
        $knownDefaults = self::getKnownDefaultValues();

        // If value matches a known Ukrainian default, use the translation system
        if (isset($knownDefaults[$value])) {
            return __('general.' . $knownDefaults[$value]);
        }

        return $value;
    }

    /**
     * Map of known Ukrainian default values to their translation keys.
     * When a DB value matches one of these, we translate it instead of showing raw Ukrainian.
     */
    private static function getKnownDefaultValues(): array
    {
        return [
            // Hero
            'E-COMMERCE 2025' => 'hero_subtitle_default',
            'СУЧАСНИЙ' => 'hero_title_line1_default',
            'МАГАЗИН' => 'hero_title_line2_default',
            "ЯКІСНІ ТОВАРИ.\nШВИДКА ДОСТАВКА.\nПРОСТИЙ СЕРВІС." => 'hero_description_default',
            'ПОЧАТИ ПОКУПКИ' => 'hero_button_default',

            // Countdown
            'MEGA РОЗПРОДАЖ' => 'countdown_title_default',
            'Знижки до 70% на всі товари' => 'countdown_description_default',

            // Banner
            'БЕЗКОШТОВНА ДОСТАВКА ВІД 1500 ГРН' => 'banner_text_default',
            'На всі замовлення по Україні' => 'banner_subtext_default',
            'ЗАМОВИТИ' => 'banner_button_default',

            // Newsletter
            'ПІДПИШІТЬСЯ НА РОЗСИЛКУ' => 'newsletter_title_default',
            'ОТРИМУЙТЕ ЕКСКЛЮЗИВНІ ПРОПОЗИЦІЇ ТА ЗНИЖКИ' => 'newsletter_description_default',
            'ПІДПИСАТИСЯ' => 'newsletter_button_default',

            // Advantages
            'БЕЗКОШТОВНА ДОСТАВКА' => 'advantages_free_delivery',
            'При замовленні від 1500 грн' => 'advantages_free_delivery_text',
            'БЕЗПЕЧНА ОПЛАТА' => 'advantages_safe_payment',
            'LiqPay, WayForPay, Monobank' => 'advantages_safe_payment_text',
            'ПОВЕРНЕННЯ 14 ДНІВ' => 'advantages_return_14',
            'Гарантія повернення' => 'advantages_return_14_text',
            'ПІДТРИМКА 24/7' => 'advantages_support_24',
            "Завжди на зв'язку" => 'advantages_support_24_text',

            // Slider
            'АКЦІЯ' => 'slider_subtitle_sale',
            'ЗНИЖКИ ДО 50%' => 'slider_title_sale',
            'На вибрані категорії товарів' => 'slider_desc_sale',
            'ДИВИТИСЬ' => 'slider_btn_sale',
            'НОВИНКИ' => 'slider_subtitle_new',
            'НОВА КОЛЕКЦІЯ' => 'slider_title_new',
            'Найкращі товари сезону' => 'slider_desc_new',
            'ПЕРЕГЛЯНУТИ' => 'slider_btn_new',

            // Recently viewed
            'НЕЩОДАВНО ПЕРЕГЛЯНУТІ' => 'recently_viewed',

            // Module titles
            'ХІТИ ПРОДАЖІВ' => 'hits',
            'КАТЕГОРІЇ' => 'categories',
            'НОВИНКИ' => 'new_products',
            'АКЦІЇ' => 'specials',
            'БРЕНДИ' => 'brands',
        ];
    }

    /**
     * Translate a value if it matches a known Ukrainian default.
     */
    public static function translateValue(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $knownDefaults = self::getKnownDefaultValues();

        if (isset($knownDefaults[$value])) {
            return __('general.' . $knownDefaults[$value]);
        }

        return $value;
    }

    public static function getAvailableTypes(): array
    {
        return [
            'hero' => ['name' => 'Hero банер', 'icon' => 'heroicon-o-photo', 'emoji' => '🖼️', 'description' => 'Головний банер з текстом та кнопкою'],
            'products_grid' => ['name' => 'Сітка товарів', 'icon' => 'heroicon-o-shopping-bag', 'emoji' => '🛍️', 'description' => 'Товари з фільтром (хіти, новинки, акції)'],
            'categories' => ['name' => 'Категорії', 'icon' => 'heroicon-o-squares-2x2', 'emoji' => '📂', 'description' => 'Сітка категорій з іконками'],
            'banner' => ['name' => 'Банер', 'icon' => 'heroicon-o-megaphone', 'emoji' => '📢', 'description' => 'Рекламний банер з текстом та посиланням'],
            'text' => ['name' => 'Текстовий блок', 'icon' => 'heroicon-o-document-text', 'emoji' => '📝', 'description' => 'Довільний HTML/текст'],
            'brands' => ['name' => 'Бренди', 'icon' => 'heroicon-o-tag', 'emoji' => '🏷️', 'description' => 'Логотипи брендів'],
            'advantages' => ['name' => 'Переваги', 'icon' => 'heroicon-o-check-badge', 'emoji' => '✅', 'description' => 'Блок переваг магазину'],
            'newsletter' => ['name' => 'Підписка', 'icon' => 'heroicon-o-envelope', 'emoji' => '📧', 'description' => 'Форма підписки на розсилку'],
            'reviews' => ['name' => 'Відгуки', 'icon' => 'heroicon-o-star', 'emoji' => '⭐', 'description' => 'Останні відгуки покупців'],
            'countdown' => ['name' => 'Таймер акції', 'icon' => 'heroicon-o-clock', 'emoji' => '⏰', 'description' => 'Зворотний відлік до кінця акції'],
            'recently_viewed' => ['name' => 'Нещодавно переглянуті', 'icon' => 'heroicon-o-eye', 'emoji' => '👁️', 'description' => 'Товари, які переглядав відвідувач'],
            'hero_slider' => ['name' => 'Hero слайдер', 'icon' => 'heroicon-o-photo', 'emoji' => '🎠', 'description' => 'Кілька слайдів з автопрокруткою'],
        ];
    }

    public static function getDefaultSettings(string $type): array
    {
        return match ($type) {
            'hero' => [
                'subtitle' => 'E-COMMERCE 2025',
                'title_line1' => 'СУЧАСНИЙ',
                'title_line2' => 'МАГАЗИН',
                'description' => "ЯКІСНІ ТОВАРИ.\nШВИДКА ДОСТАВКА.\nПРОСТИЙ СЕРВІС.",
                'button_text' => 'ПОЧАТИ ПОКУПКИ',
                'button_url' => '/specials',
                'bg_color' => '#ffffff',
            ],
            'products_grid' => [
                'filter' => 'hits',
                'limit' => 8,
                'columns' => 4,
            ],
            'categories' => [
                'limit' => 6,
                'style' => 'grid',
            ],
            'banner' => [
                'text' => 'БЕЗКОШТОВНА ДОСТАВКА ВІД 1500 ГРН',
                'subtext' => 'На всі замовлення по Україні',
                'button_text' => 'ЗАМОВИТИ',
                'button_url' => '/specials',
                'bg_color' => '#000000',
                'text_color' => '#ffffff',
            ],
            'text' => [
                'content' => '<p>Ваш текст тут</p>',
            ],
            'brands' => [
                'limit' => 12,
            ],
            'advantages' => [
                'items' => [
                    ['icon' => '🚚', 'title' => 'БЕЗКОШТОВНА ДОСТАВКА', 'text' => 'При замовленні від 1500 грн'],
                    ['icon' => '💳', 'title' => 'БЕЗПЕЧНА ОПЛАТА', 'text' => 'LiqPay, WayForPay, Monobank'],
                    ['icon' => '🔄', 'title' => 'ПОВЕРНЕННЯ 14 ДНІВ', 'text' => 'Гарантія повернення'],
                    ['icon' => '📞', 'title' => 'ПІДТРИМКА 24/7', 'text' => 'Завжди на зв\'язку'],
                ],
            ],
            'newsletter' => [
                'title' => 'ПІДПИШІТЬСЯ НА РОЗСИЛКУ',
                'description' => 'ОТРИМУЙТЕ ЕКСКЛЮЗИВНІ ПРОПОЗИЦІЇ ТА ЗНИЖКИ',
                'button_text' => 'ПІДПИСАТИСЯ',
            ],
            'reviews' => [
                'limit' => 6,
            ],
            'countdown' => [
                'end_date' => now()->addDays(7)->format('Y-m-d H:i:s'),
                'title' => 'MEGA РОЗПРОДАЖ',
                'description' => 'Знижки до 70% на всі товари',
            ],
            'recently_viewed' => [
                'limit' => 8,
            ],
            'hero_slider' => [
                'slides' => [
                    [
                        'subtitle' => 'АКЦІЯ',
                        'title' => 'ЗНИЖКИ ДО 50%',
                        'description' => 'На вибрані категорії товарів',
                        'button_text' => 'ДИВИТИСЬ',
                        'button_url' => '/specials',
                        'bg_color' => '#000000',
                        'text_color' => '#ffffff',
                    ],
                    [
                        'subtitle' => 'НОВИНКИ',
                        'title' => 'НОВА КОЛЕКЦІЯ',
                        'description' => 'Найкращі товари сезону',
                        'button_text' => 'ПЕРЕГЛЯНУТИ',
                        'button_url' => '/new',
                        'bg_color' => '#ffffff',
                        'text_color' => '#000000',
                    ],
                ],
                'autoplay' => true,
                'interval' => 5000,
            ],
            default => [],
        };
    }
}
