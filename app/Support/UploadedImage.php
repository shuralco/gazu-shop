<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * URL завантаженого зображення — або null, якщо файла вже немає.
 *
 * Аплоади лежать у `storage/app/public` і віддаються через симлінк
 * `public/storage`. Якщо файл зник (том не персистентний, ручне видалення,
 * перенесення), шаблони раніше все одно ліпили <img src="/storage/…"> і
 * покупець бачив битий квадрат. Тепер відсутній файл = null, а шаблони на null
 * малюють генеративний плейсхолдер — тобто деградація м'яка.
 */
class UploadedImage
{
    /** Кеш перевірок на час запиту: на сторінці каталогу десятки зображень. */
    private static array $exists = [];

    public static function url(?string $path): ?string
    {
        $path = is_string($path) ? trim($path) : '';
        if ($path === '') {
            return null;
        }

        // Зовнішні URL не перевіряємо — не наш диск.
        if (Str::startsWith($path, ['http://', 'https://', 'data:'])) {
            return $path;
        }

        $relative = Str::startsWith($path, '/')
            ? ltrim($path, '/')
            : 'storage/'.ltrim($path, '/');

        if (! (self::$exists[$relative] ??= is_file(public_path($relative)))) {
            return null;
        }

        return url('/'.$relative);
    }

    /** Скидання per-request кешу (Octane: воркер живе між запитами). */
    public static function flush(): void
    {
        self::$exists = [];
    }
}
