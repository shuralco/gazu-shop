<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Cause-agnostic safety-net проти cold-cache рецидиву (~500ms TTFB).
 *
 * Запускається щохвилини планувальником. Незалежно від ПРИЧИНИ холодного стану
 * (ручний view:clear, Filament-кнопка «очистити кеш», observer-flush на
 * замовлення, TTL-expiry, частковий рестарт) — детектить і сам доварює, тож
 * будь-яке cold-вікно обмежене ≤60с замість «до першого органічного хіта».
 *
 * Дешева коли тепло: glob + 2 loopback-probe + dbsize. Важкий cache:warm —
 * лише при детекті, під 5-хв cooldown-локом (не частіше 1 warm / 5 хв).
 */
class EnsureWarm extends Command
{
    protected $signature = 'gazu:ensure-warm
        {--threshold=400 : Поріг TTFB (ms) понад який сторінка вважається холодною}
        {--force : Прогріти примусово, ігноруючи probe}
        {--products : Прогрівати й сторінки товарів (важче)}';

    protected $description = 'Детект cold-стану storefront + авто-прогрів (safety-net проти 500ms рецидиву)';

    public function handle(): int
    {
        $threshold = (int) $this->option('threshold');

        // 1) Compiled views: миттєвий self-heal якщо порожньо (view:clear лишив cold).
        $views = count(glob(storage_path('framework/views/*.php')) ?: []);
        if ($views === 0) {
            Log::warning('[ensure-warm] compiled views = 0 → view:cache');
            Artisan::call('view:cache');
            $views = count(glob(storage_path('framework/views/*.php')) ?: []);
        }

        // 2) Probe через loopback (без зовнішнього round-trip) з Host реального домену.
        $host = parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'localhost';
        $cold = false;
        $samples = [];
        foreach (['/', '/catalog'] as $path) {
            try {
                $start = microtime(true);
                $resp = Http::withHeaders(['Host' => $host])
                    ->withoutVerifying()
                    ->timeout(8)
                    ->get('http://127.0.0.1:80'.$path);
                $ms = (int) round((microtime(true) - $start) * 1000);
                $samples[$path] = $ms;
                if ($resp->successful() && $ms > $threshold) {
                    $cold = true;
                }
            } catch (\Throwable $e) {
                Log::warning('[ensure-warm] probe failed '.$path.': '.$e->getMessage());
            }
        }

        // 3) Heal під cooldown-локом (макс 1 важкий warm / 5 хв).
        $force = (bool) $this->option('force');
        if (($cold || $force) && Cache::add('ensure-warm:lock', 1, now()->addMinutes(5))) {
            Log::warning('[ensure-warm] COLD storefront '.json_encode($samples).' → cache:warm');
            if (app()->bound('sentry')) {
                try {
                    \Sentry\captureMessage(
                        '[ensure-warm] cold storefront detected: '.json_encode($samples),
                        \Sentry\Severity::warning()
                    );
                } catch (\Throwable $e) {
                    // sentry не критичний
                }
            }
            Artisan::call('cache:warm'.($this->option('products') ? ' --products' : ''));
        }

        // 4) Завжди — 1 метрик-рядок (для логів/моніторингу).
        $this->info('[ensure-warm] views='.$views.' samples='.json_encode($samples).' cold='.($cold ? '1' : '0'));

        return self::SUCCESS;
    }
}
