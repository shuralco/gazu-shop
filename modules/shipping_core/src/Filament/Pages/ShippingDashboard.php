<?php

namespace App\Filament\Pages;

use App\Models\Order;
use App\Models\ShippingWarehouse;
use Filament\Pages\Page;
use Filament\Widgets\StatsOverviewWidget as BaseStatsOverviewWidget;

class ShippingDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationGroup = 'Склад і доставка';

    protected static ?string $title = 'Панель доставки';

    protected static ?string $navigationLabel = 'Панель доставки';

    protected static ?int $navigationSort = 10;

    protected static string $view = 'filament.pages.shipping-dashboard';

    public function getWidgets(): array
    {
        return [
            ShippingStatsWidget::class,
        ];
    }
}

class ShippingStatsWidget extends BaseStatsOverviewWidget
{
    protected function getStats(): array
    {
        $activeWarehouses = ShippingWarehouse::active()->count();
        $totalWarehouses = ShippingWarehouse::count();
        $postomates = ShippingWarehouse::where('type', 'postomat')->active()->count();
        $warehouses = ShippingWarehouse::where('type', 'warehouse')->active()->count();

        $ordersWithShipping = Order::whereNotNull('shipping_provider')->count();
        $novaPoshtaOrders = Order::where('shipping_provider', 'novaposhta')->count();

        return [
            BaseStatsOverviewWidget\Stat::make('Активні точки доставки', $activeWarehouses)
                ->description("Загалом: {$totalWarehouses}")
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color('success'),

            BaseStatsOverviewWidget\Stat::make('Відділення НП', $warehouses)
                ->description('Відділення Нової Пошти')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('primary'),

            BaseStatsOverviewWidget\Stat::make('Поштомати НП', $postomates)
                ->description('Поштомати Нової Пошти')
                ->descriptionIcon('heroicon-m-cube')
                ->color('info'),

            BaseStatsOverviewWidget\Stat::make('Замовлення з доставкою', $ordersWithShipping)
                ->description("НП: {$novaPoshtaOrders}")
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('warning'),
        ];
    }
}
