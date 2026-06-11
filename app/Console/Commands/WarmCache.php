<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Прогрів ResponseCache: обходить ключові публічні URL власного сайту, щоб
 * перший реальний відвідувач після деплою отримав уже закешовану (швидку)
 * відповідь, а не холодний рендер (~1с проти ~0.18с).
 *
 * Джерело URL — sitemap сайту (там уже коректні слаги, нові сторінки
 * враховуються автоматично; обходимо проблему JSON-слагів категорій).
 *
 *   php artisan cache:warm                    # main + категорії + бренди
 *   php artisan cache:warm --products         # + усі товари (важко, 1000+)
 *   php artisan cache:warm --base=https://gazu.uno
 *
 * Запускається автоматично після деплою (docker-entrypoint.sh, у фоні, коли
 * Octane вже відповідає). Безпечний, ідемпотентний, без побічних ефектів.
 */
class WarmCache extends Command
{
    protected $signature = 'cache:warm
        {--base= : Базовий URL (дефолт config(app.url))}
        {--products : Прогріти й сторінки товарів (sitemap-products, їх багато)}';

    protected $description = 'Прогріти ResponseCache ключових сторінок (після деплою)';

    public function handle(): int
    {
        $base = rtrim((string) ($this->option('base') ?: config('app.url')), '/');
        if (! str_starts_with($base, 'http')) {
            $this->error("Невалідний base URL: '{$base}' (задайте --base або APP_URL)");

            return self::FAILURE;
        }

        // Які sub-sitemap обходимо. Товари — лише за прапорцем (їх 1000+).
        $maps = ['/sitemap-main.xml', '/sitemap-categories.xml', '/sitemap-brands.xml'];
        if ($this->option('products')) {
            $maps[] = '/sitemap-products.xml';
        }

        $paths = ['/', '/catalog'];
        foreach ($maps as $map) {
            foreach ($this->locs($base.$map) as $loc) {
                // Беремо лише ШЛЯХ із <loc> — щоб прогрів ішов на $base незалежно
                // від того, який хост зашитий у sitemap (dev/stage/prod).
                $path = parse_url($loc, PHP_URL_PATH);
                if ($path) {
                    $q = parse_url($loc, PHP_URL_QUERY);
                    $paths[] = $path.($q ? '?'.$q : '');
                }
            }
        }
        $urls = array_map(fn ($p) => $base.$p, array_values(array_unique($paths)));
        $total = count($urls);
        if ($total === 0) {
            $this->warn('Не знайдено URL у sitemap — нічого прогрівати.');

            return self::SUCCESS;
        }

        $this->info("Прогрів {$total} URL → {$base}");
        @ini_set('memory_limit', '256M');
        $ok = 0;
        $fail = 0;
        $t0 = microtime(true);

        // Послідовно з негайним звільненням тіла — прогрів не потребує тіла
        // відповіді (важливо лише, щоб запит дійшов і ResponseCache наповнився).
        // Http::pool тримав би всі HTML у памʼяті → OOM на 200+ сторінках.
        foreach ($urls as $u) {
            try {
                $code = Http::timeout(30)->withHeaders(['X-Warm' => '1'])->get($u)->status();
            } catch (\Throwable) {
                $code = 0;
            }
            if ($code >= 200 && $code < 400) {
                $ok++;
            } else {
                $fail++;
                if ($this->getOutput()->isVerbose()) {
                    $this->warn('  ✗ '.$u.' ('.$code.')');
                }
            }
        }

        $sec = round(microtime(true) - $t0, 1);
        $this->info("✓ Прогріто {$ok}/{$total} за {$sec}с".($fail ? " (помилок: {$fail})" : ''));

        return self::SUCCESS;
    }

    /** Витягти <loc> з XML-sitemap. @return list<string> */
    private function locs(string $sitemapUrl): array
    {
        try {
            $body = Http::timeout(20)->get($sitemapUrl)->body();
        } catch (\Throwable) {
            return [];
        }
        if (! preg_match_all('#<loc>\s*([^<\s]+)\s*</loc>#', $body, $m)) {
            return [];
        }

        return array_map(fn ($u) => html_entity_decode($u), $m[1]);
    }
}
