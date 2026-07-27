<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CarMake extends Model
{
    protected $fillable = [
        'slug', 'name', 'logo_path', 'sort_order', 'is_active',
        'meta_title', 'meta_description', 'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function models(): HasMany
    {
        return $this->hasMany(CarModel::class, 'make_id');
    }

    /**
     * Resolve a usable logo URL from logo_path regardless of how it was stored:
     *   - full URL (http…)                              → as-is
     *   - root-relative public path ("/img/…")          → url()
     *   - Filament upload (public disk, "car-makes/x")   → /storage/car-makes/x
     * Returns null when no logo set (caller falls back to a letter badge).
     */
    public function getLogoUrlAttribute(): ?string
    {
        $p = $this->logo_path;

        if (\Illuminate\Support\Str::startsWith((string) $p, ['http://', 'https://'])) {
            return $p;
        }

        // Локальний файл міг зникнути (аплоади живуть у ФС контейнера, а не на
        // постійному томі — перезбірка їх стирає). Тоді краще показати лого з
        // репозиторію за slug'ом, а якщо і його немає — літерний бейдж, ніж
        // битий <img>.
        if ($p) {
            $relative = \Illuminate\Support\Str::startsWith($p, '/')
                ? ltrim($p, '/')
                : 'storage/'.ltrim($p, '/');

            if (is_file(public_path($relative))) {
                return url('/'.$relative);
            }
        }

        return self::repoLogoUrl((string) $this->slug);
    }

    /**
     * Лого марки, що лежить у репозиторії: public/img/car-makes/{slug}.(svg|png).
     * Кешуємо результат на час запиту — на сторінці десятки марок.
     */
    public static function repoLogoUrl(string $slug): ?string
    {
        static $cache = [];

        if ($slug === '') {
            return null;
        }
        if (array_key_exists($slug, $cache)) {
            return $cache[$slug];
        }

        foreach (['svg', 'png', 'webp', 'jpg'] as $ext) {
            $relative = "img/car-makes/{$slug}.{$ext}";
            if (is_file(public_path($relative))) {
                return $cache[$slug] = url('/'.$relative);
            }
        }

        return $cache[$slug] = null;
    }
}
