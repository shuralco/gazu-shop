<?php

namespace App\Support;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Єдине джерело показників для кастомного дашборду (перетягувана сітка карток).
 *
 * all() повертає впорядкований список карток у форматі, який рендерить
 * resources/views/filament/pages/gazu-dashboard.blade.php. Кешується на 60с —
 * це і пришвидшує рендер (немає lazy-затримки віджетів), і захищає від
 * важких запитів при кожному заході.
 *
 * Кожна картка: id (стабільний ключ для localStorage-порядку), label, value,
 * sub, icon (heroicon), color (success|warning|danger|info|primary|gray),
 * spark (необов'язковий масив для міні-графіка).
 */
class DashboardMetrics
{
    public static function all(): array
    {
        return Cache::remember('gazu:dashboard:metrics', 60, fn () => self::compute());
    }

    public static function flush(): void
    {
        Cache::forget('gazu:dashboard:metrics');
    }

    private static function compute(): array
    {
        $today = Carbon::today();
        $weekAgo = Carbon::today()->subDays(7);
        $monthAgo = Carbon::today()->subDays(30);
        $hasInventory = Schema::hasTable('inventory');

        // -- Замовлення / виручка --------------------------------------------
        $totalOrders = Order::count();
        $ordersToday = Order::whereDate('created_at', $today)->count();
        $orders7d = Order::where('created_at', '>=', $weekAgo)->count();
        $orders30d = Order::where('created_at', '>=', $monthAgo)->count();
        $ordersSpark = self::dailySeries(fn ($q) => $q, 'COUNT(*)');
        $revenueSpark = self::dailySeries(fn ($q) => $q->where('status', '!=', 'cancelled'), 'SUM(total)');

        $revenue30d = (float) Order::where('created_at', '>=', $monthAgo)->where('status', '!=', 'cancelled')->sum('total');
        $revenueAll = (float) Order::where('status', '!=', 'cancelled')->sum('total');
        $avgOrder = $totalOrders > 0 ? $revenueAll / $totalOrders : 0;

        $pending = Order::whereIn('status', ['pending', 'new', 'processing'])->count();
        $cancelled = Order::where('status', 'cancelled')->count();

        // -- Каталог / склад -------------------------------------------------
        $productsTotal = Product::count();
        $productsActive = Product::where('is_active', true)->count();
        $productsInStock = $hasInventory
            ? DB::table('inventory')->where('quantity', '>', 0)->distinct('product_id')->count('product_id')
            : Product::where('quantity', '>', 0)->count();
        $productsLowStock = $hasInventory
            ? DB::table('inventory')->whereBetween('quantity', [1, 5])->distinct('product_id')->count('product_id')
            : Product::whereBetween('quantity', [1, 5])->count();
        $productsOut = max(0, $productsActive - $productsInStock);
        $stockPct = $productsActive > 0 ? round($productsInStock / $productsActive * 100) : 0;
        $stockUnits = $hasInventory ? (int) DB::table('inventory')->sum('quantity') : (int) Product::sum('quantity');

        $brandsCount = Brand::count();
        $categoriesCount = Category::count();
        $categoryLeaf = Category::whereDoesntHave('children')->count();

        $topBrand = self::topBrandName();

        // -- Клієнти / промо / контент --------------------------------------
        $usersTotal = User::count();
        $usersNew7d = User::where('created_at', '>=', $weekAgo)->count();

        $couponsActive = self::tableCount('coupons', fn ($q) => $q->where('is_active', true));
        $reviewsTotal = self::tableCount('reviews');
        $reviewsPending = self::tableCount('reviews', fn ($q) => $q->where(function ($w) {
            $w->where('is_approved', false)->orWhereNull('is_approved')->orWhere('status', 'pending');
        }));

        // -- Доставка / пошук ------------------------------------------------
        $warehouses = self::tableCount('merchant_warehouses', fn ($q) => $q->where('is_active', true));
        $shippingMethods = self::tableCount('shipping_methods', fn ($q) => $q->where('is_active', true));
        $paymentSystems = self::tableCount('payment_gateway_settings', fn ($q) => $q->where('is_active', true));
        $shipments = self::tableCount('np_shipments') + self::tableCount('up_shipments');

        $searchTotal = self::tableCount('search_queries');
        $searchZero = self::tableCount('search_queries', fn ($q) => $q->where('results_count', 0));

        $clr = fn ($n, $g = 'gray', $ok = 'success') => $n > 0 ? $ok : $g;

        $cards = [
            ['id' => 'orders_total', 'label' => 'Усього замовлень', 'value' => self::num($totalOrders), 'sub' => $ordersToday > 0 ? "+{$ordersToday} сьогодні" : 'Сьогодні поки 0', 'icon' => 'heroicon-o-shopping-bag', 'color' => $clr($ordersToday), 'spark' => $ordersSpark],
            ['id' => 'orders_today', 'label' => 'Замовлень сьогодні', 'value' => self::num($ordersToday), 'sub' => $ordersToday > 0 ? 'Свіжі' : 'Поки тиша', 'icon' => 'heroicon-o-shopping-cart', 'color' => $clr($ordersToday)],
            ['id' => 'orders_7d', 'label' => 'За 7 днів', 'value' => self::num($orders7d), 'sub' => "За 30 днів: {$orders30d}", 'icon' => 'heroicon-o-calendar-days', 'color' => 'info', 'spark' => $ordersSpark],
            ['id' => 'orders_pending', 'label' => 'Очікують обробки', 'value' => self::num($pending), 'sub' => $pending > 0 ? 'Потребують уваги' : 'Усе оброблено', 'icon' => 'heroicon-o-clock', 'color' => $pending > 0 ? 'warning' : 'success'],
            ['id' => 'revenue_30d', 'label' => 'Виручка 30 днів', 'value' => self::money($revenue30d), 'sub' => 'Без скасованих', 'icon' => 'heroicon-o-banknotes', 'color' => 'success', 'spark' => $revenueSpark],
            ['id' => 'revenue_all', 'label' => 'Виручка усього', 'value' => self::money($revenueAll), 'sub' => 'LTV каталогу', 'icon' => 'heroicon-o-currency-dollar', 'color' => 'primary'],
            ['id' => 'avg_order', 'label' => 'Середній чек', 'value' => self::money($avgOrder), 'sub' => 'По всіх замовленнях', 'icon' => 'heroicon-o-calculator', 'color' => 'primary'],
            ['id' => 'orders_cancelled', 'label' => 'Скасовані', 'value' => self::num($cancelled), 'sub' => $totalOrders > 0 ? round($cancelled / max($totalOrders, 1) * 100).'% від усіх' : '—', 'icon' => 'heroicon-o-x-circle', 'color' => $cancelled > 0 ? 'danger' : 'gray'],

            ['id' => 'products_total', 'label' => 'Товарів у каталозі', 'value' => self::num($productsTotal), 'sub' => "{$productsActive} активних", 'icon' => 'heroicon-o-cube', 'color' => 'primary'],
            ['id' => 'in_stock', 'label' => 'У наявності', 'value' => "{$productsInStock} / {$productsActive}", 'sub' => "{$stockPct}% каталогу", 'icon' => 'heroicon-o-cube-transparent', 'color' => $stockPct >= 80 ? 'success' : ($stockPct >= 50 ? 'warning' : 'danger')],
            ['id' => 'out_of_stock', 'label' => 'Немає в наявності', 'value' => self::num($productsOut), 'sub' => $productsOut > 0 ? 'Поповнити' : 'Усе на місці', 'icon' => 'heroicon-o-archive-box-x-mark', 'color' => $productsOut > 0 ? 'danger' : 'success'],
            ['id' => 'low_stock', 'label' => 'Низький залишок', 'value' => self::num($productsLowStock), 'sub' => '1–5 шт.', 'icon' => 'heroicon-o-exclamation-triangle', 'color' => $productsLowStock > 0 ? 'warning' : 'gray'],
            ['id' => 'stock_units', 'label' => 'Одиниць на складах', 'value' => self::num($stockUnits), 'sub' => 'Сумарний залишок', 'icon' => 'heroicon-o-squares-2x2', 'color' => 'info'],
            ['id' => 'categories', 'label' => 'Категорій', 'value' => self::num($categoriesCount), 'sub' => "{$categoryLeaf} leaf-підкатегорій", 'icon' => 'heroicon-o-rectangle-stack', 'color' => 'gray'],
            ['id' => 'brands', 'label' => 'Брендів', 'value' => self::num($brandsCount), 'sub' => $topBrand ? "Топ: {$topBrand}" : 'Виробники', 'icon' => 'heroicon-o-bookmark', 'color' => 'gray'],

            ['id' => 'users', 'label' => 'Користувачів', 'value' => self::num($usersTotal), 'sub' => $usersNew7d > 0 ? "+{$usersNew7d} за тиждень" : 'Нових немає', 'icon' => 'heroicon-o-users', 'color' => $clr($usersNew7d)],
            ['id' => 'coupons', 'label' => 'Активних промокодів', 'value' => self::num($couponsActive), 'sub' => 'Купони у дії', 'icon' => 'heroicon-o-ticket', 'color' => 'info'],
            ['id' => 'reviews', 'label' => 'Відгуків', 'value' => self::num($reviewsTotal), 'sub' => $reviewsPending > 0 ? "{$reviewsPending} на модерації" : 'Усі оброблені', 'icon' => 'heroicon-o-star', 'color' => $reviewsPending > 0 ? 'warning' : 'gray'],

            ['id' => 'warehouses', 'label' => 'Складів', 'value' => self::num($warehouses), 'sub' => 'Активних', 'icon' => 'heroicon-o-building-storefront', 'color' => 'gray'],
            ['id' => 'shipping_methods', 'label' => 'Методів доставки', 'value' => self::num($shippingMethods), 'sub' => 'Активних', 'icon' => 'heroicon-o-truck', 'color' => $clr($shippingMethods, 'danger')],
            ['id' => 'payment_systems', 'label' => 'Платіжних систем', 'value' => self::num($paymentSystems), 'sub' => 'Активних', 'icon' => 'heroicon-o-credit-card', 'color' => $clr($paymentSystems, 'danger')],
            ['id' => 'shipments', 'label' => 'ТТН / відправлень', 'value' => self::num($shipments), 'sub' => 'НП + УП', 'icon' => 'heroicon-o-paper-airplane', 'color' => 'gray'],

            ['id' => 'search_total', 'label' => 'Пошукових запитів', 'value' => self::num($searchTotal), 'sub' => 'Усього залоговано', 'icon' => 'heroicon-o-magnifying-glass', 'color' => 'info'],
            ['id' => 'search_zero', 'label' => 'Запитів без результату', 'value' => self::num($searchZero), 'sub' => $searchZero > 0 ? 'Можливості для SEO' : 'Усе знаходиться', 'icon' => 'heroicon-o-magnifying-glass-minus', 'color' => $searchZero > 0 ? 'warning' : 'success'],
        ];

        return $cards;
    }

    /** Останні 7 днів за агрегатом — для міні-спарклайна. */
    private static function dailySeries(callable $scope, string $agg): array
    {
        $q = Order::query()->where('created_at', '>=', Carbon::today()->subDays(6));
        $scope($q);
        $rows = $q->selectRaw("DATE(created_at) as d, {$agg} as v")->groupBy('d')->pluck('v', 'd')->all();
        $out = [];
        for ($i = 6; $i >= 0; $i--) {
            $out[] = (float) ($rows[Carbon::today()->subDays($i)->toDateString()] ?? 0);
        }
        return $out;
    }

    private static function topBrandName(): ?string
    {
        try {
            $id = Product::whereNotNull('brand_id')->selectRaw('brand_id, COUNT(*) c')
                ->groupBy('brand_id')->orderByDesc('c')->value('brand_id');
            if (! $id) return null;
            $b = Brand::find($id);
            if (! $b) return null;
            $name = $b->name;
            if (! $name) {
                $raw = $b->getRawOriginal('name');
                $name = is_string($raw) && str_starts_with($raw, '{') ? (json_decode($raw, true)['uk'] ?? null) : $raw;
            }
            return $name ? mb_substr((string) $name, 0, 16) : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private static function tableCount(string $table, ?callable $scope = null): int
    {
        if (! Schema::hasTable($table)) return 0;
        try {
            $q = DB::table($table);
            if ($scope) {
                // деякі колонки можуть не існувати у форку — guard через try
                $scope($q);
            }
            return (int) $q->count();
        } catch (\Throwable $e) {
            return Schema::hasTable($table) ? (int) DB::table($table)->count() : 0;
        }
    }

    private static function num(int|float $n): string
    {
        return number_format((float) $n, 0, '.', ' ');
    }

    private static function money(int|float $n): string
    {
        return '₴ '.number_format((float) $n, 0, '.', ' ');
    }
}
