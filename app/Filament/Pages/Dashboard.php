<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $title = 'Панель управління';

    protected static ?string $navigationLabel = 'Головна';

    public function getWidgets(): array
    {
        return [
            // Row 1 — 12 stat cards (full width)
            \App\Filament\Widgets\StatsOverview::class,
            // Row 2 — два графіки розподілу
            \App\Filament\Widgets\CatalogDistributionChart::class,
            \App\Filament\Widgets\BrandDistributionChart::class,
            // Row 3 — shipping health + графік замовлень
            \App\Filament\Widgets\NovaPoshtaWidget::class,
            \App\Filament\Widgets\ShippingApiHealthWidget::class,
            \App\Filament\Widgets\OrdersChart::class,
            // Row 4 — таблиці
            \App\Filament\Widgets\LowStockProducts::class,
            \App\Filament\Widgets\RecentActivity::class,
            \App\Filament\Widgets\LatestOrders::class,
            \App\Filament\Widgets\TopProducts::class,
        ];
    }

    public function getColumns(): int|string|array
    {
        return [
            'sm' => 1,
            'md' => 2,
            'xl' => 4,
        ];
    }
}
