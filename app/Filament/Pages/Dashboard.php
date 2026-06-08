<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $title = 'Панель управління';

    protected static ?string $navigationLabel = 'Головна';

    // Кастомний view: зверху перетягувана сітка метрик (DashboardMetrics, 24
    // показники, порядок у localStorage), нижче — звичайні Filament-віджети.
    protected static string $view = 'filament.pages.gazu-dashboard';

    public function getWidgets(): array
    {
        return [
            // StatsOverview прибрано — його 12 карток замінила багатша
            // перетягувана сітка показників у верхній частині view.
            // Row 2 — два графіки розподілу
            \App\Filament\Widgets\CatalogDistributionChart::class,
            \App\Filament\Widgets\BrandDistributionChart::class,
            // Row 3 — shipping health + графік замовлень
            \App\Filament\Widgets\NovaPoshtaWidget::class,
            \App\Filament\Widgets\ShippingApiHealthWidget::class,
            \App\Filament\Widgets\OrdersChart::class,
            // Row 4 — карта географії доставок (full-width)
            \App\Filament\Widgets\DeliveryMapWidget::class,
            // Row 5 — таблиці
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
