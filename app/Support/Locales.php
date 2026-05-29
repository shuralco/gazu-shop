<?php

namespace App\Support;

/**
 * Єдина точка визначення активних мов магазину.
 *
 * Мультимовність — окремий (преміум) модуль `multilang`. Коли він вимкнений,
 * сайт працює лише дефолтною мовою (app.locale, зазвичай uk): перемикач мов
 * прихований, а в адмінці translatable-поля показують лише одну мову.
 * Коли увімкнений — доступні всі app.available_locales.
 *
 * Перевірка стану модуля захищена try/catch, бо викликається й під час раннього
 * boot (реєстрація Filament-панелі), коли БД ще може бути недоступна — тоді
 * відкочуємось до ENV/config.
 */
class Locales
{
    /** Каталог підтримуваних мов: code => людська назва. */
    public const CATALOG = [
        'uk' => 'Українська',
        'en' => 'English',
    ];

    /** Прапори для перемикача (емодзі), code => emoji. */
    public const FLAGS = [
        'uk' => '🇺🇦',
        'en' => '🇬🇧',
    ];

    public static function enabled(): bool
    {
        try {
            return \App\Support\ModuleManager::for('multilang')->enabled();
        } catch (\Throwable $e) {
            // Ранній boot / немає БД → ENV, потім config.
            $env = env('MODULE_MULTILANG');
            if ($env !== null) {
                return filter_var($env, FILTER_VALIDATE_BOOLEAN);
            }
            return (bool) (config('modules.multilang.enabled') ?? false);
        }
    }

    /** Дефолтна мова застосунку. */
    public static function default(): string
    {
        return (string) config('app.locale', 'uk');
    }

    /**
     * Активні коди мов. Модуль вимкнено → лише дефолтна.
     *
     * @return string[]
     */
    public static function active(): array
    {
        $default = self::default();
        if (! self::enabled()) {
            return [$default];
        }
        $avail = (array) config('app.available_locales', [$default]);
        $codes = array_values(array_filter($avail, fn ($l) => isset(self::CATALOG[$l])));

        return $codes ?: [$default];
    }

    /** Чи показувати перемикач (модуль увімкнено + більше однієї мови). */
    public static function switchable(): bool
    {
        return self::enabled() && count(self::active()) > 1;
    }

    /** @return array<string,string> code => назва, лише активні. */
    public static function labels(): array
    {
        $out = [];
        foreach (self::active() as $code) {
            $out[$code] = self::CATALOG[$code] ?? mb_strtoupper($code);
        }

        return $out;
    }

    public static function current(): string
    {
        return app()->getLocale();
    }
}
