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
 * Що робить:
 *   - лого марки: якщо файл зник, але в репозиторії є public/img/car-makes/{slug}
 *     → переставляє шлях на нього; інакше очищає (буде літерний бейдж);
 *   - фото товару: якщо файл зник → очищає (буде генеративний плейсхолдер).
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

        $products = Product::query()
            ->whereNotNull('image')
            ->where('image', '!=', '')
            ->get(['id', 'title', 'sku', 'image']);

        foreach ($products as $p) {
            if (UploadedImage::url((string) $p->image) !== null) {
                continue;
            }

            $rows[] = ['Товар', mb_strimwidth((string) ($p->sku ?: $p->title), 0, 26, '…'), mb_strimwidth((string) $p->image, 0, 34, '…'), '— очищено —'];

            if (! $dry) {
                $p->image = null;
                $p->save();
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
