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
            \App\Filament\Widgets\StatsOverview::class,
            \App\Filament\Widgets\NovaPoshtaWidget::class,
            \App\Filament\Widgets\ShippingApiHealthWidget::class,
            \App\Filament\Widgets\OrdersChart::class,
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
