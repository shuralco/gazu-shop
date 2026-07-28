<?php

namespace App\Console\Commands;

use App\Models\CarMake;
use App\Models\Product;
use App\Support\UploadedImage;
use Illuminate\Console\Command;

/**
 * Прибирає з БД посилання на зображення, яких уже немає на диску.
 *
 * Аплоади живуть у `storage/app/public` — тека всередині контейнера. Якщо для
 * неї не налаштований постійний том, перезбірка образу стирає всі завантажені
 * файли, а в БД лишаються «привиди»: логотипи марок і фото товарів, що ведуть
 * у нікуди.
 *
 * Що робить (усі місця, де зберігається шлях до зображення):
 *   - лого марки авто та лого бренду: якщо файл зник, але в репозиторії є
 *     public/img/car-makes/{slug} → переставляє шлях на нього; інакше очищає;
 *   - фото товару й додаткові фото галереї: якщо файл зник → прибирає;
 *   - зображення категорії: якщо файл зник → очищає (буде типова плитка).
 *
 * Ідемпотентно. Запуск: php artisan gazu:restore-images [--dry-run]
 */
class RestoreMissingImages extends Command
{
    protected $signature = 'gazu:restore-images {--dry-run : лише показати, нічого не змінювати}';

    protected $description = 'Полагодити посилання на зображення, файли яких зникли з диска';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $rows = [];

        foreach (CarMake::query()->get(['id', 'slug', 'name', 'logo_path']) as $make) {
            $path = (string) $make->logo_path;
            if ($path === '' || UploadedImage::url($path) !== null) {
                continue; // або нема чого лагодити, або файл на місці
            }

            $repo = $this->repoAsset((string) $make->slug);
            $rows[] = ['Марка', $make->name, mb_strimwidth($path, 0, 34, '…'), $repo ?? '— очищено —'];

            if (! $dry) {
                $make->logo_path = $repo;
                $make->save();
            }
        }

        foreach (Product::query()->get(['id', 'title', 'sku', 'image', 'gallery']) as $p) {
            $label = mb_strimwidth((string) ($p->sku ?: $p->title), 0, 26, '…');
            $dirty = false;

            if ($p->image && UploadedImage::url((string) $p->image) === null) {
                $rows[] = ['Товар', $label, mb_strimwidth((string) $p->image, 0, 34, '…'), '— очищено —'];
                $p->image = null;
                $dirty = true;
            }

            // Галерея: прибираємо лише зниклі кадри, решту лишаємо як є.
            $gallery = array_values(array_filter((array) $p->gallery));
            $alive = array_values(array_filter(
                $gallery,
                fn ($g) => is_string($g) && UploadedImage::url($g) !== null
            ));
            if (count($alive) !== count($gallery)) {
                foreach (array_diff($gallery, $alive) as $gone) {
                    $rows[] = ['Галерея', $label, mb_strimwidth((string) $gone, 0, 34, '…'), '— прибрано —'];
                }
                $p->gallery = $alive;
                $dirty = true;
            }

            if ($dirty && ! $dry) {
                $p->save();
            }
        }

        // Лого брендів — та сама логіка, що й для марок авто.
        if (class_exists(\App\Models\Brand::class) && \Schema::hasColumn('brands', 'logo')) {
            foreach (\App\Models\Brand::query()->get(['id', 'slug', 'name', 'logo']) as $brand) {
                $path = (string) $brand->logo;
                if ($path === '' || UploadedImage::url($path) !== null) {
                    continue;
                }

                $repo = $this->repoAsset((string) $brand->slug);
                $rows[] = ['Бренд', (string) $brand->name, mb_strimwidth($path, 0, 34, '…'), $repo ?? '— очищено —'];

                if (! $dry) {
                    $brand->logo = $repo;
                    $brand->save();
                }
            }
        }

        // Зображення категорій — без репо-фолбеку, просто чистимо мертве.
        if (\Schema::hasColumn('categories', 'image')) {
            foreach (\App\Models\Category::query()->get(['id', 'slug', 'image']) as $cat) {
                $path = (string) $cat->image;
                if ($path === '' || UploadedImage::url($path) !== null) {
                    continue;
                }

                $rows[] = ['Категорія', (string) $cat->slug, mb_strimwidth($path, 0, 34, '…'), '— очищено —'];

                if (! $dry) {
                    $cat->image = null;
                    $cat->save();
                }
            }
        }

        if (! $rows) {
            $this->info('Мертвих посилань на зображення немає.');

            return self::SUCCESS;
        }

        $this->table(['Що', 'Назва', 'Зникло', 'Замінено на'], $rows);
        $this->line($dry
            ? '--dry-run: нічого не змінено.'
            : 'Готово: '.count($rows).' записів полагоджено.');

        if (! $dry) {
            \App\Models\Filter::flushCatalogCache();
        }

        return self::SUCCESS;
    }

    /** Шлях до лого в репозиторії, якщо воно там є. */
    private function repoAsset(string $slug): ?string
    {
        if ($slug === '') {
            return null;
        }
        foreach (['svg', 'png', 'webp', 'jpg'] as $ext) {
            $relative = "img/car-makes/{$slug}.{$ext}";
            if (is_file(public_path($relative))) {
                return '/'.$relative;
            }
        }

        return null;
    }
}
