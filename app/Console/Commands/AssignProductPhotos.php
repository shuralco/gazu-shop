<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Support\PartImage;
use App\Support\ProductCardDecorator;
use Illuminate\Console\Command;

/**
 * Audits photo coverage across ALL products and reports the share that resolve
 * to a real kind-pool photo (public/img/parts/<kind>/NN.webp) versus the
 * monogram fallback.
 *
 * It mirrors the EXACT resolution chain the storefront uses:
 *   1. Real uploaded $product->image (takes priority)
 *   2. ProductCardDecorator::imageKindFor() → kind → PartImage pool
 *   3. Monogram (only if the kind has no pool — should never happen now)
 *
 * Goal: >95% of products resolve to a real pool photo.
 *
 *   php artisan products:assign-photos            # audit + summary
 *   php artisan products:assign-photos --by-category
 *   php artisan products:assign-photos --show-monograms
 */
class AssignProductPhotos extends Command
{
    protected $signature = 'products:assign-photos
        {--by-category : Break the report down per category}
        {--show-monograms : List products that still fall back to a monogram}';

    protected $description = 'Audit product photo coverage (kind-pool vs monogram) and report %';

    public function handle(): int
    {
        $pools = $this->availablePools();
        $this->info('Available photo pools ('.count($pools).'): '.implode(', ', array_keys($pools)));
        $this->newLine();

        $total = 0;
        $realUpload = 0;     // has its own uploaded photo
        $poolPhoto = 0;      // resolved to a kind that has a pool
        $monogram = 0;       // no pool → monogram fallback
        $kindHist = [];      // kind => count
        $catStats = [];      // catLabel => [pool=>, mono=>, total=>]
        $monogramRows = [];

        Product::with('category')
            ->select(['id', 'title', 'image', 'category_id'])
            ->chunk(200, function ($products) use (
                $pools, &$total, &$realUpload, &$poolPhoto, &$monogram,
                &$kindHist, &$catStats, &$monogramRows
            ) {
                foreach ($products as $p) {
                    $total++;

                    $catLabel = $this->catLabel($p);
                    $catStats[$catLabel] ??= ['pool' => 0, 'mono' => 0, 'total' => 0];
                    $catStats[$catLabel]['total']++;

                    // 1. Real uploaded photo wins.
                    if (! empty($p->image)) {
                        $realUpload++;
                        $poolPhoto++;
                        $catStats[$catLabel]['pool']++;
                        continue;
                    }

                    // 2. Decorator kind → does that kind have a pool?
                    $kind = ProductCardDecorator::imageKindFor($p);
                    $kindHist[$kind] = ($kindHist[$kind] ?? 0) + 1;

                    if (isset($pools[$kind]) && $pools[$kind] > 0) {
                        $poolPhoto++;
                        $catStats[$catLabel]['pool']++;
                    } else {
                        $monogram++;
                        $catStats[$catLabel]['mono']++;
                        if (count($monogramRows) < 200) {
                            $monogramRows[] = [$p->id, $catLabel, $kind ?: '—'];
                        }
                    }
                }
            });

        if ($total === 0) {
            $this->warn('No products found.');

            return self::SUCCESS;
        }

        $pct = round(100 * $poolPhoto / $total, 1);

        $this->line('Products audited ........ '.$total);
        $this->line('  real uploaded photo ... '.$realUpload);
        $this->line('  kind-pool photo ....... '.$poolPhoto.'  ('.$pct.'%)');
        $this->line('  monogram fallback ..... '.$monogram.'  ('.round(100 * $monogram / $total, 1).'%)');
        $this->newLine();

        $this->line('Kind usage histogram:');
        arsort($kindHist);
        foreach ($kindHist as $kind => $cnt) {
            $has = isset($pools[$kind]) && $pools[$kind] > 0 ? 'pool('.$pools[$kind].')' : 'NO-POOL';
            $this->line(sprintf('  %-14s %5d  [%s]', $kind, $cnt, $has));
        }
        $this->newLine();

        if ($this->option('by-category')) {
            $this->line('Per-category coverage:');
            ksort($catStats);
            foreach ($catStats as $label => $s) {
                $cp = $s['total'] ? round(100 * $s['pool'] / $s['total'], 0) : 0;
                $flag = $s['mono'] > 0 ? ' <-- '.$s['mono'].' monogram' : '';
                $this->line(sprintf('  %-32s %3d%%  (%d/%d)%s', mb_substr($label, 0, 32), $cp, $s['pool'], $s['total'], $flag));
            }
            $this->newLine();
        }

        if ($this->option('show-monograms') && $monogramRows) {
            $this->line('Products still on monogram:');
            $this->table(['id', 'category', 'kind'], $monogramRows);
        }

        if ($pct >= 95.0) {
            $this->info("OK — {$pct}% of products resolve to a real kind-pool photo (target >95%).");

            return self::SUCCESS;
        }

        if ($pct >= 90.0) {
            $this->warn("{$pct}% pool coverage (>90% but below 95% target).");

            return self::SUCCESS;
        }

        $this->error("Only {$pct}% pool coverage — below 90%. Extend kindFromCategory / CATEGORY_IMAGE_KINDS.");

        return self::FAILURE;
    }

    /** @return array<string,int> pool-slug => file count */
    private function availablePools(): array
    {
        $out = [];
        foreach (glob(public_path('img/parts/*'), GLOB_ONLYDIR) ?: [] as $dir) {
            $out[basename($dir)] = count(glob($dir.'/*.webp') ?: []);
        }
        ksort($out);

        return $out;
    }

    private function catLabel(Product $p): string
    {
        $cat = $p->relationLoaded('category') ? $p->getRelation('category') : null;
        if (! $cat) {
            return '(no category)';
        }
        $raw = $cat->getRawOriginal('title');
        $title = (is_string($raw) && str_starts_with($raw, '{'))
            ? (json_decode($raw, true)['uk'] ?? $raw)
            : ($cat->title ?? $raw);

        return ($cat->slug ?? '?').' | '.(is_string($title) ? $title : '?');
    }
}
