<?php

namespace App\Support;

use App\Models\DisplaySetting;

/**
 * Центральні SEO-шаблони таксономій (gazu.com.ua).
 *
 * Один источник правди для title/description усіх типів сторінок:
 * сторінка адмінки «Шаблони SEO» зберігає override у DisplaySetting
 * (ключі seo_{key}_template, історично home — seo_home_title/description),
 * а фронтові blade-в'юхи рендерять через ::render().
 *
 * Плейсхолдери — іменовані {name}, {price}, {sku}…; легасі sprintf-«%s»
 * шаблони (збережені старою версією сторінки) теж підтримуються —
 * %s підставляються у порядку self::POSITIONAL[$key].
 */
class SeoTemplates
{
    /** Базові шаблони GAZU. Ключ => [title, description, позиційні %s-поля]. */
    public const DEFAULTS = [
        'home' => [
            'title' => '{shop} — автозапчастини для китайських авто з доставкою по Україні',
            'description' => 'Інтернет-магазин {shop}: запчастини для BYD, Chery, Geely, Haval, MG та інших. Оригінали та аналоги в наявності, доставка Новою Поштою, гарантія.',
        ],
        'category' => [
            'title' => '{name} — купити з доставкою по Україні | {shop}',
            'description' => 'Купити {name} для китайських авто (BYD, Chery, Geely, Haval). У наявності {count}. Оригінали та аналоги, доставка Новою Поштою, гарантія від {shop}.',
            'positional' => ['name'],
        ],
        'product' => [
            'title' => '{name} — купити за {price} грн | {shop}',
            'description' => 'Купити {name} за {price} грн ✓ Артикул {sku} ✓ Наявність і характеристики ✓ Доставка по Україні, гарантія від {shop}.',
            'positional' => ['name', 'price', 'excerpt'],
        ],
        'brand' => [
            'title' => '{name} — автозапчастини бренду, каталог і ціни | {shop}',
            'description' => 'Автозапчастини {name} в каталозі {shop}: {count}. Оригінальна продукція з гарантією, доставка по Україні.',
        ],
        'brands' => [
            'title' => 'Усі бренди автозапчастин — каталог виробників | {shop}',
            'description' => 'Каталог брендів автозапчастин у {shop}: оригінали та перевірені аналоги від світових виробників. Гарантія, доставка по Україні.',
        ],
        'car' => [
            'title' => 'Запчастини {car} — каталог, ціни, наявність | {shop}',
            'description' => '{car} — оригінальні запчастини та аналоги: {count}. Підбір за маркою і моделлю, доставка по Україні, гарантія від {shop}.',
        ],
        'search' => [
            'title' => '«{query}» — результати пошуку | {shop}',
            'description' => 'Пошук «{query}» у каталозі {shop}: знайдено {count}. Перевірте наявність і ціни, доставка по Україні.',
        ],
        'page' => [
            'title' => '{name} | {shop}',
            'description' => '{name} — корисна інформація від інтернет-магазину автозапчастин {shop}.',
            'positional' => ['name'],
        ],
        'blog' => [
            'title' => 'Блог про запчастини та ремонт китайських авто | {shop}',
            'description' => 'Поради з підбору запчастин, ремонту й обслуговування BYD, Chery, Geely, Haval від експертів {shop}.',
        ],
        'blog_post' => [
            'title' => '{name} — блог {shop}',
            'description' => '{name}. Читайте в блозі {shop}: підбір запчастин, ремонт і обслуговування китайських авто.',
        ],
    ];

    public static function shopName(): string
    {
        return (string) DisplaySetting::get('seo_shop_name', 'GAZU');
    }

    /** Згенерувати title за шаблоном таксономії. */
    public static function title(string $key, array $vars = []): string
    {
        return static::render($key, 'title', $vars);
    }

    /** Згенерувати description за шаблоном таксономії. */
    public static function description(string $key, array $vars = []): string
    {
        return static::render($key, 'description', $vars);
    }

    /** Активний шаблон (override з DisplaySetting або базовий GAZU). */
    public static function template(string $key, string $field): string
    {
        $settingKey = $key === 'home'
            ? "seo_home_{$field}" // історичні ключі сторінки «Шаблони SEO»
            : "seo_{$key}_{$field}_template";

        $default = self::DEFAULTS[$key][$field] ?? '';
        $tpl = trim((string) DisplaySetting::get($settingKey, $default));

        // Затерті/легасі SimpleShop-дефолти ігноруємо на користь GAZU-базових.
        if ($tpl === '' || str_contains($tpl, 'SimpleShop')) {
            return $default;
        }

        return $tpl;
    }

    public static function render(string $key, string $field, array $vars = []): string
    {
        $tpl = static::template($key, $field);
        $vars = array_merge(['shop' => static::shopName()], $vars);

        // Легасі sprintf-шаблони: %s підставляємо у визначеному порядку.
        if (str_contains($tpl, '%s')) {
            $order = self::DEFAULTS[$key]['positional'] ?? ['name'];
            $args = array_map(fn ($k) => (string) ($vars[$k] ?? ''), $order);
            $tpl = vsprintf($tpl, array_pad($args, substr_count($tpl, '%s'), ''));
        }

        $replace = [];
        foreach ($vars as $k => $v) {
            $replace['{'.$k.'}'] = (string) $v;
        }
        $out = strtr($tpl, $replace);

        // Невикористані плейсхолдери прибираємо, пробіли й пунктуацію чистимо.
        $out = preg_replace('/\{[a-z_]+\}/i', '', $out);
        $out = preg_replace('/\s{2,}/u', ' ', $out);
        $out = preg_replace('/\s+([,.;:!?])/u', '$1', $out);

        // НЕ byte-trim: у списку є multibyte (·—), trim() ламає перший
        // utf-8 символ рядка (напр. «) — тому чистимо preg-ом.
        return trim((string) preg_replace('/^[\s·—\-|,]+|[\s·—\-|,]+$/u', '', (string) $out));
    }
}
