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

    /** Групи карток: ключ → [назва, [id карток у дефолтному порядку]]. */
    public const GROUPS = [
        'sales' => ['Продажі', ['orders_total', 'orders_pending', 'orders_done', 'orders_7d', 'orders_payments', 'revenue_today', 'revenue_7d', 'revenue_30d', 'revenue_all', 'avg_order']],
        'catalog' => ['Каталог і склад', ['products_total', 'in_stock', 'out_of_stock', 'low_stock', 'stock_value', 'categories', 'brands']],
        'service' => ['Клієнти та сервіс', ['users', 'callbacks', 'coupons', 'reviews', 'config_status', 'shipments']],
        'search' => ['Пошук', ['search_total', 'search_zero']],
    ];

    /** Група для конкретного id картки. */
    public static function groupFor(string $id): string
    {
        foreach (self::GROUPS as $key => [$label, $ids]) {
            if (in_array($id, $ids, true)) {
                return $key;
            }
        }

        return 'other';
    }

    /**
     * Картки, впорядковані/відфільтровані за конфігом адміна (DisplaySetting
     * dashboard_cards = [id => ['visible'=>bool,'order'=>int]]), згруповані.
     * Якщо конфіг порожній → дефолтні групи/порядок (дашборд не ламається).
     *
     * @return array<int, array{key:string,label:string,cards:array}>
     */
    public static function arrangedGroups(): array
    {
        $metrics = collect(self::all())->keyBy('id');
        $cfg = \App\Models\DisplaySetting::get('dashboard_cards');
        $cfg = is_array($cfg) ? $cfg : [];

        $out = [];
        foreach (self::GROUPS as $key => [$label, $ids]) {
            $cards = collect($ids)
                ->filter(fn ($id) => $metrics->has($id))
                ->filter(fn ($id) => ! isset($cfg[$id]['visible']) || $cfg[$id]['visible'])
                ->sortBy(fn ($id) => $cfg[$id]['order'] ?? array_search($id, $ids, true))
                ->map(fn ($id) => $metrics->get($id))
                ->values()
                ->all();
            if (! empty($cards)) {
                $out[] = ['key' => $key, 'label' => $label, 'cards' => $cards];
            }
        }

        // Картки, що не потрапили в жодну групу (нові id) — в кінець.
        $known = collect(self::GROUPS)->flatMap(fn ($g) => $g[1])->all();
        $rest = $metrics->keys()->reject(fn ($id) => in_array($id, $known, true))
            ->filter(fn ($id) => ! isset($cfg[$id]['visible']) || $cfg[$id]['visible'])
            ->map(fn ($id) => $metrics->get($id))->values()->all();
        if (! empty($rest)) {
            $out[] = ['key' => 'other', 'label' => 'Інше', 'cards' => $rest];
        }

        return $out;
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

        // -- Замовлення ------------------------------------------------------
        // orders.status — INTEGER: 0 = Очікує, 1 = Виконано (НЕ рядок!).
        // payment_status — рядок: pending|success|failed|refunded.
        $totalOrders = Order::count();
        $ordersToday = Order::whereDate('created_at', $today)->count();
        $orders7d = Order::where('created_at', '>=', $weekAgo)->count();
        $orders30d = Order::where('created_at', '>=', $monthAgo)->count();
        $ordersPending = Order::where('status', 0)->count();
        $ordersDone = Order::where('status', 1)->count();
        $donePct = $totalOrders > 0 ? round($ordersDone / $totalOrders * 100) : 0;
        $ordersSpark = self::dailySeries(fn ($q) => $q, 'COUNT(*)');

        // Розбивка за статусом оплати.
        $payCounts = Order::selectRaw('payment_status, COUNT(*) c')->groupBy('payment_status')->pluck('c', 'payment_status')->all();
        $payPaid = (int) ($payCounts['success'] ?? 0);
        $payPend = (int) ($payCounts['pending'] ?? 0);
        $payFail = (int) (($payCounts['failed'] ?? 0) + ($payCounts['refunded'] ?? 0));

        // -- Виручка (без невдалих/повернених; COD-pending лишається) --------
        $revScope = fn ($q) => $q->whereNotIn('payment_status', ['failed', 'refunded']);
        $revenueToday = (float) $revScope(Order::whereDate('created_at', $today))->sum('total');
        $revenue7d = (float) $revScope(Order::where('created_at', '>=', $weekAgo))->sum('total');
        $revenue30d = (float) $revScope(Order::where('created_at', '>=', $monthAgo))->sum('total');
        $revenueAll = (float) $revScope(Order::query())->sum('total');
        $avgOrder = $totalOrders > 0 ? $revenueAll / $totalOrders : 0;
        $revenueSpark = self::dailySeries($revScope, 'SUM(total)');

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
        $stockValue = $hasInventory
            ? (float) DB::table('inventory')->join('products', 'products.id', '=', 'inventory.product_id')->sum(DB::raw('inventory.quantity * products.price'))
            : (float) Product::sum(DB::raw('quantity * price'));

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

        // Заявки на дзвінок — операційна дія.
        $cbTotal = self::tableCount('callback_requests');
        $cbNew = self::tableCount('callback_requests', fn ($q) => $q->whereIn('status', ['new', 'pending', 'новий']));

        // -- Налаштування (конфіг-здоров'я) / доставка / пошук --------------
        $warehouses = self::tableCount('merchant_warehouses', fn ($q) => $q->where('is_active', true));
        $shippingMethods = self::tableCount('shipping_methods', fn ($q) => $q->where('is_active', true));
        $paymentSystems = self::tableCount('payment_gateway_settings', fn ($q) => $q->where('is_active', true));
        $configOk = $warehouses > 0 && $shippingMethods > 0 && $paymentSystems > 0;
        $shipments = self::tableCount('np_shipments') + self::tableCount('up_shipments');

        $searchTotal = self::tableCount('search_queries');
        $searchZero = self::tableCount('search_queries', fn ($q) => $q->where('results_count', 0));

        $clr = fn ($n, $g = 'gray', $ok = 'success') => $n > 0 ? $ok : $g;

        $cards = [
            // -- Продажі --
            ['id' => 'orders_total', 'label' => 'Усього замовлень', 'value' => self::num($totalOrders), 'sub' => $ordersToday > 0 ? "+{$ordersToday} сьогодні" : 'Сьогодні поки 0', 'icon' => 'heroicon-o-shopping-bag', 'color' => $clr($ordersToday), 'spark' => $ordersSpark],
            ['id' => 'orders_pending', 'label' => 'Очікують обробки', 'value' => self::num($ordersPending), 'sub' => $ordersPending > 0 ? 'Потребують уваги' : 'Усе оброблено', 'icon' => 'heroicon-o-clock', 'color' => $ordersPending > 0 ? 'warning' : 'success'],
            ['id' => 'orders_done', 'label' => 'Виконано замовлень', 'value' => self::num($ordersDone), 'sub' => $totalOrders > 0 ? "{$donePct}% від усіх" : '—', 'icon' => 'heroicon-o-check-circle', 'color' => 'success'],
            ['id' => 'orders_7d', 'label' => 'Замовлень за 7 днів', 'value' => self::num($orders7d), 'sub' => "За 30 днів: {$orders30d}", 'icon' => 'heroicon-o-calendar-days', 'color' => 'info', 'spark' => $ordersSpark],
            ['id' => 'orders_payments', 'label' => 'Статуси оплати', 'value' => self::num($totalOrders), 'sub' => $totalOrders > 0 ? "Оплач. {$payPaid} · Очік. {$payPend} · Відмова {$payFail}" : 'Поки немає', 'icon' => 'heroicon-o-chart-pie', 'color' => $payFail > 0 ? 'warning' : 'gray'],

            // -- Виручка --
            ['id' => 'revenue_today', 'label' => 'Виручка сьогодні', 'value' => self::money($revenueToday), 'sub' => 'За сьогодні', 'icon' => 'heroicon-o-banknotes', 'color' => $revenueToday > 0 ? 'success' : 'gray'],
            ['id' => 'revenue_7d', 'label' => 'Виручка 7 днів', 'value' => self::money($revenue7d), 'sub' => 'За тиждень', 'icon' => 'heroicon-o-banknotes', 'color' => 'success', 'spark' => $revenueSpark],
            ['id' => 'revenue_30d', 'label' => 'Виручка 30 днів', 'value' => self::money($revenue30d), 'sub' => 'За місяць', 'icon' => 'heroicon-o-banknotes', 'color' => 'success', 'spark' => $revenueSpark],
            ['id' => 'revenue_all', 'label' => 'Виручка усього', 'value' => self::money($revenueAll), 'sub' => 'LTV каталогу', 'icon' => 'heroicon-o-currency-dollar', 'color' => 'primary'],
            ['id' => 'avg_order', 'label' => 'Середній чек', 'value' => self::money($avgOrder), 'sub' => 'По всіх замовленнях', 'icon' => 'heroicon-o-calculator', 'color' => 'primary'],

            // -- Каталог / склад --
            ['id' => 'products_total', 'label' => 'Товарів у каталозі', 'value' => self::num($productsTotal), 'sub' => "{$productsActive} активних", 'icon' => 'heroicon-o-cube', 'color' => 'primary'],
            ['id' => 'in_stock', 'label' => 'У наявності', 'value' => "{$productsInStock} / {$productsActive}", 'sub' => "{$stockPct}% каталогу", 'icon' => 'heroicon-o-cube-transparent', 'color' => $stockPct >= 80 ? 'success' : ($stockPct >= 50 ? 'warning' : 'danger')],
            ['id' => 'out_of_stock', 'label' => 'Немає в наявності', 'value' => self::num($productsOut), 'sub' => $productsOut > 0 ? 'Поповнити' : 'Усе на місці', 'icon' => 'heroicon-o-archive-box-x-mark', 'color' => $productsOut > 0 ? 'danger' : 'success'],
            ['id' => 'low_stock', 'label' => 'Низький залишок', 'value' => self::num($productsLowStock), 'sub' => '1–5 шт.', 'icon' => 'heroicon-o-exclamation-triangle', 'color' => $productsLowStock > 0 ? 'warning' : 'gray'],
            ['id' => 'stock_value', 'label' => 'Залишок на суму', 'value' => self::money($stockValue), 'sub' => self::num($stockUnits).' одиниць', 'icon' => 'heroicon-o-scale', 'color' => 'primary'],
            ['id' => 'categories', 'label' => 'Категорій', 'value' => self::num($categoriesCount), 'sub' => "{$categoryLeaf} leaf-підкатегорій", 'icon' => 'heroicon-o-rectangle-stack', 'color' => 'gray'],
            ['id' => 'brands', 'label' => 'Брендів', 'value' => self::num($brandsCount), 'sub' => $topBrand ? "Топ: {$topBrand}" : 'Виробники', 'icon' => 'heroicon-o-bookmark', 'color' => 'gray'],

            // -- Клієнти / контент / операції --
            ['id' => 'users', 'label' => 'Користувачів', 'value' => self::num($usersTotal), 'sub' => $usersNew7d > 0 ? "+{$usersNew7d} за тиждень" : 'Нових немає', 'icon' => 'heroicon-o-users', 'color' => $clr($usersNew7d)],
            ['id' => 'callbacks', 'label' => 'Заявки на дзвінок', 'value' => self::num($cbTotal), 'sub' => $cbNew > 0 ? "{$cbNew} нових" : ($cbTotal > 0 ? 'Усі оброблені' : 'Поки немає'), 'icon' => 'heroicon-o-phone-arrow-down-left', 'color' => $cbNew > 0 ? 'warning' : 'gray'],
            ['id' => 'coupons', 'label' => 'Активних промокодів', 'value' => self::num($couponsActive), 'sub' => 'Купони у дії', 'icon' => 'heroicon-o-ticket', 'color' => 'info'],
            ['id' => 'reviews', 'label' => 'Відгуків', 'value' => self::num($reviewsTotal), 'sub' => $reviewsPending > 0 ? "{$reviewsPending} на модерації" : 'Усі оброблені', 'icon' => 'heroicon-o-star', 'color' => $reviewsPending > 0 ? 'warning' : 'gray'],
            ['id' => 'config_status', 'label' => 'Доставка та оплата', 'value' => $configOk ? 'Готово' : 'Перевірте', 'sub' => "{$warehouses} склад · {$shippingMethods} методи · {$paymentSystems} платіжка", 'icon' => 'heroicon-o-cog-6-tooth', 'color' => $configOk ? 'success' : 'warning'],
            ['id' => 'shipments', 'label' => 'ТТН / відправлень', 'value' => self::num($shipments), 'sub' => 'НП + УП', 'icon' => 'heroicon-o-paper-airplane', 'color' => 'gray'],

            // -- Пошук / SEO --
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
