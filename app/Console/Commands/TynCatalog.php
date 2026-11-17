<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Product;
use App\Support\Tyn\TynTrack;
use Illuminate\Console\Command;

/** Каталог товарів у Тин (події catalog: id, категорія, ціна, наявність), пачками по 500. Без назв і описів. */
class TynCatalog extends Command
{
    protected $signature = 'tyn:catalog';

    protected $description = 'Надіслати каталог (id, категорія, ціна, наявність) у Тин; потрібен TYN_TRACK=1';

    public function handle(): int
    {
        if (!TynTrack::enabled()) {
            $this->warn('TYN_TRACK вимкнено або немає ключа.');

            return self::FAILURE;
        }
        require_once __DIR__ . '/../../Support/Tyn/tyn-lib.php';
        $cfg = config('tyn');
        $opts = ['key' => (string) $cfg['site_key'], 'secret' => (string) $cfg['secret'], 'base_url' => (string) ($cfg['base_url'] ?? 'https://c.tyn.com.ua')];
        $sent = 0;
        $total = 0;
        Product::query()->where('is_active', true)->select(['id', 'category_id', 'price', 'price_currency', 'quantity'])
            ->chunkById(500, function ($chunk) use (&$sent, &$total, $opts) {
                $rows = [];
                foreach ($chunk as $p) {
                    $rows[] = [
                        'id' => (string) $p->id,
                        'cat' => $p->category_id ? [(string) $p->category_id] : [],
                        'price' => TynTrack::minor($p->display_price),
                        'cur' => 'UAH',
                        'stock' => ((int) $p->quantity) > 0 ? 1 : 0,
                    ];
                }
                $total += count($rows);
                $sent += \tyn_track_catalog_send($rows, $opts);
            });
        $this->info("Каталог: надіслано {$sent} з {$total}.");

        return self::SUCCESS;
    }
}
