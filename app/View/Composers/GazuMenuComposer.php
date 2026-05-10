<?php

namespace App\View\Composers;

use App\Helpers\Cart\Cart;
use App\Models\Brand;
use App\Models\DisplaySetting;
use App\Models\MerchantWarehouse;
use App\Models\Product;
use App\Services\Gazu\MegaMenuBuilder;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

/**
 * Шерить мега-дерево + список брендів між усіма GAZU views (header → mega-menu).
 * Кешує на 10 хвилин у пам'яті процесу, щоб не нагружати DB на кожен рендер.
 */
class GazuMenuComposer
{
    private static ?array $cachedTree = null;
    private static ?array $cachedBrands = null;

    public function __construct(private MegaMenuBuilder $builder) {}

    public function compose(View $view): void
    {
        if (self::$cachedTree === null) {
            self::$cachedTree = $this->builder->build();
        }
        if (self::$cachedBrands === null) {
            self::$cachedBrands = $this->builder->brands();
        }

        $view->with('megaTree', self::$cachedTree);

        // Composer's `$brands` is for header/mega-menu/brand-strip — do NOT
        // clobber a `$brands` already passed by a controller (e.g. brand-list
        // page passes a Brand-Collection with products_count). Set only when
        // missing.
        if (! array_key_exists('brands', $view->getData())) {
            $view->with('brands', self::$cachedBrands);
        }

        // Live cart count — не кешуємо, має оновлюватись на кожен запит.
        $view->with('cartCount', Cart::getCartQuantityItems());

        // Computed shop stats (warehouses, brands, products) — replaces hardcoded
        // marketing numbers like "12 відділень" / "240+ брендів" / "50 000+ артикулів"
        // when admin has not overridden the setting in Filament.
        $view->with('shopStats', $this->loadShopStats());

        // GAZU visual settings (з DisplaySetting або defaults).
        $view->with('gazuSettings', $this->loadVisualSettings());
    }

    private function loadShopStats(): array
    {
        return Cache::remember('gazu_shop_stats', 600, function () {
            $whCount = MerchantWarehouse::query()->where('is_active', true)->count();
            $cities = MerchantWarehouse::query()
                ->where('is_active', true)
                ->whereNotNull('city')
                ->orderBy('sort_order')
                ->limit(3)
                ->pluck('city')
                ->filter()
                ->unique()
                ->implode(', ');
            $brandsCount = Brand::query()->where('is_active', true)->count();
            $productsCount = Product::query()->where('is_active', true)->count();

            // Tier-aware marketing labels — keep promises honest at small scale.
            $bucket = fn (int $n, array $tiers) => collect($tiers)->reverse()
                ->first(fn ($threshold) => $n >= $threshold) ?? $n;

            $brandsLabel = $brandsCount >= 10
                ? $bucket($brandsCount, [10, 50, 100, 240]).'+'
                : (string) $brandsCount;
            $productsLabel = $productsCount >= 100
                ? $bucket($productsCount, [100, 500, 1000, 5000, 10000, 50000]).'+'
                : (string) $productsCount;

            return [
                'warehouses_count' => $whCount,
                'warehouses_label' => $whCount > 0 ? $whCount.' '.\plural_uk($whCount, 'відділення', 'відділення', 'відділень') : '',
                'cities' => $cities ?: 'Україна',
                'cities_with_count' => $cities && $whCount
                    ? $cities.' · '.$whCount.' '.\plural_uk($whCount, 'відділення', 'відділення', 'відділень')
                    : ($cities ?: 'Україна'),
                'brands_count' => $brandsCount,
                'brands_label' => $brandsLabel.' '.\plural_uk($brandsCount, 'бренд', 'бренди', 'брендів'),
                'products_count' => $productsCount,
                'products_label' => $productsLabel.' '.\plural_uk($productsCount, 'артикул', 'артикули', 'артикулів'),
            ];
        });
    }

    private function loadVisualSettings(): array
    {
        $defaults = \App\Filament\Pages\GazuVisualSettings::$defaults ?? [];
        $out = [];
        foreach ($defaults as $k => $default) {
            $val = DisplaySetting::get($k);
            // Honour explicit admin override; fall back to default ONLY if it's
            // non-null. Null defaults signal "compute from shopStats in the
            // blade fallback (`$s[key] ?? $shopStats[...]`)".
            if ($val !== null && $val !== '') {
                $out[$k] = $val;
            } elseif ($default !== null) {
                $out[$k] = $default;
            }
            // else: leave key absent → blade fallback kicks in
        }
        return $out;
    }
}
