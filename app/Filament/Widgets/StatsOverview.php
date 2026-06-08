<?php

namespace App\Filament\Widgets;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class StatsOverview extends BaseWidget
{
    public static function canView(): bool
    {
        return \App\Support\Access\AccessControl::can('OrderResource', 'view');
    }

    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $today = Carbon::today();
        $weekAgo = Carbon::today()->subDays(7);
        $monthAgo = Carbon::today()->subDays(30);

        $totalOrders = Order::count();
        $ordersToday = Order::whereDate('created_at', $today)->count();
        $orders7d = Order::where('created_at', '>=', $weekAgo)->count();
        $orders30d = Order::where('created_at', '>=', $monthAgo)->count();
        $ordersChart = $this->dailyOrdersChart(7);

        $revenue30d = (float) Order::where('created_at', '>=', $monthAgo)
            ->where('status', '!=', 'cancelled')
            ->sum('total');
        $revenueAll = (float) Order::where('status', '!=', 'cancelled')->sum('total');
        $avgOrder = $totalOrders > 0 ? $revenueAll / $totalOrders : 0;
        $revenueChart = $this->dailyRevenueChart(7);

        $productsTotal = Product::count();
        $productsActive = Product::where('is_active', true)->count();
        $productsInStock = Schema::hasTable('inventory')
            ? Inventory::where('quantity', '>', 0)->distinct('product_id')->count('product_id')
            : Product::where('quantity', '>', 0)->count();
        $productsLowStock = Schema::hasTable('inventory')
            ? Inventory::whereBetween('quantity', [1, 5])->distinct('product_id')->count('product_id')
            : Product::whereBetween('quantity', [1, 5])->count();
        $productsOutOfStock = max(0, $productsActive - $productsInStock);
        $stockPct = $productsActive > 0 ? round($productsInStock / $productsActive * 100) : 0;

        $brandsCount = Brand::count();
        $categoriesCount = Category::count();
        $categoryLeafCount = Category::whereDoesntHave('children')->count();

        $usersTotal = User::count();
        $usersNew7d = User::where('created_at', '>=', $weekAgo)->count();

        $couponsActive = Schema::hasTable('coupons')
            ? \DB::table('coupons')->where('is_active', true)->count()
            : 0;

        return [
            // Row 1 — Замовлення & виручка
            Stat::make('Усього замовлень', number_format($totalOrders, 0, '.', ' '))
                ->description($ordersToday > 0 ? "+{$ordersToday} сьогодні" : 'Сьогодні поки 0')
                ->descriptionIcon($ordersToday > 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-minus')
                ->chart($ordersChart)
                ->color($ordersToday > 0 ? 'success' : 'gray'),

            Stat::make('За 7 днів', $orders7d)
                ->description("За 30 днів: {$orders30d}")
                ->descriptionIcon('heroicon-m-calendar-days')
                ->chart($ordersChart)
                ->color('info'),

            Stat::make('Виручка 30 днів', '₴ '.number_format($revenue30d, 0, '.', ' '))
                ->description('Без скасованих')
                ->descriptionIcon('heroicon-m-banknotes')
                ->chart($revenueChart)
                ->color('success'),

            Stat::make('Середній чек', '₴ '.number_format($avgOrder, 0, '.', ' '))
                ->description('По всіх замовленнях')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('primary'),

            // Row 2 — Каталог
            Stat::make('Товарів у каталозі', number_format($productsTotal, 0, '.', ' '))
                ->description("{$productsActive} активних · {$brandsCount} брендів")
                ->descriptionIcon('heroicon-m-cube')
                ->color('primary'),

            Stat::make('У наявності', "{$productsInStock} / {$productsActive}")
                ->description("{$stockPct}% каталогу · ".($productsOutOfStock > 0 ? "{$productsOutOfStock} нема" : 'усе в наявності'))
                ->descriptionIcon('heroicon-m-cube-transparent')
                ->color($stockPct >= 80 ? 'success' : ($stockPct >= 50 ? 'warning' : 'danger')),

            Stat::make('Низький залишок', $productsLowStock)
                ->description('1–5 шт. — поповнити')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($productsLowStock > 0 ? 'warning' : 'gray'),

            Stat::make('Категорій', "{$categoriesCount}")
                ->description("{$categoryLeafCount} підкатегорій (leaf)")
                ->descriptionIcon('heroicon-m-rectangle-stack')
                ->color('gray'),

            // Row 3 — Користувачі та промо
            Stat::make('Користувачів', $usersTotal)
                ->description($usersNew7d > 0 ? "+{$usersNew7d} за тиждень" : 'Нових немає')
                ->descriptionIcon('heroicon-m-users')
                ->color($usersNew7d > 0 ? 'success' : 'gray'),

            Stat::make('Активних промокодів', $couponsActive)
                ->description('Купони у дії')
                ->descriptionIcon('heroicon-m-ticket')
                ->color('info'),

            Stat::make('Виручка усього', '₴ '.number_format($revenueAll, 0, '.', ' '))
                ->description('LTV каталогу')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('primary'),

            Stat::make('Замовлень сьогодні', $ordersToday)
                ->description($ordersToday > 0 ? 'Свіжі замовлення' : 'Поки тиша')
                ->descriptionIcon($ordersToday > 0 ? 'heroicon-m-shopping-cart' : 'heroicon-m-moon')
                ->color($ordersToday > 0 ? 'success' : 'gray'),
        ];
    }

    /** Last N days' order counts (oldest → today). */
    private function dailyOrdersChart(int $days): array
    {
        $rows = Order::query()
            ->where('created_at', '>=', Carbon::today()->subDays($days - 1))
            ->selectRaw('DATE(created_at) as d, COUNT(*) as c')
            ->groupBy('d')
            ->pluck('c', 'd')
            ->all();

        $out = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $day = Carbon::today()->subDays($i)->toDateString();
            $out[] = (int) ($rows[$day] ?? 0);
        }
        return $out;
    }

    private function dailyRevenueChart(int $days): array
    {
        $rows = Order::query()
            ->where('created_at', '>=', Carbon::today()->subDays($days - 1))
            ->where('status', '!=', 'cancelled')
            ->selectRaw('DATE(created_at) as d, SUM(total) as s')
            ->groupBy('d')
            ->pluck('s', 'd')
            ->all();

        $out = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $day = Carbon::today()->subDays($i)->toDateString();
            $out[] = (float) ($rows[$day] ?? 0);
        }
        return $out;
    }
}
