<?php

namespace App\Support;

/**
 * Галерея товару: поле форми `gallery_images` ↔ колонка `products.gallery`.
 *
 * Раніше поле форми називалось gallery_images, а колонка — gallery, і ніщо їх
 * не з'єднувало: фото лягали на диск (products/gallery), але до товару не
 * прив'язувались і на сайті не з'являлись.
 *
 * Формат у БД: `/storage/products/gallery/xxx.jpg` — саме його чекає
 * UploadedImage::url() (шлях із «/» вважається відносним до public/).
 * У формі (FilePond) шлях відносний до диска: `products/gallery/xxx.jpg`.
 */
class ProductGallery
{
    private const PREFIX = '/storage/';

    /** Значення колонки (акцесор повертає масив шляхів) → шляхи для FileUpload. */
    public static function forForm(?array $gallery): array
    {
        $out = [];
        foreach ((array) $gallery as $path) {
            if (is_string($path) && str_starts_with($path, self::PREFIX)) {
                $out[] = substr($path, strlen(self::PREFIX));
            }
        }

        return $out;
    }

    /**
     * Шляхи з FileUpload → значення колонки. Записи, яких немає в полі форми
     * (старі `/assets/...`), зберігаємо як були, щоб правка галереї їх не стирала.
     */
    public static function forStorage(?array $formPaths, ?array $current): ?array
    {
        $legacy = array_values(array_filter(
            (array) $current,
            fn ($p) => is_string($p) && $p !== '' && ! str_starts_with($p, self::PREFIX)
        ));

        $uploaded = [];
        foreach ((array) $formPaths as $path) {
            if (is_string($path) && $path !== '') {
                $uploaded[] = self::PREFIX.ltrim($path, '/');
            }
        }

        $all = array_values(array_unique(array_merge($uploaded, $legacy)));

        return $all ?: null;
    }
}
