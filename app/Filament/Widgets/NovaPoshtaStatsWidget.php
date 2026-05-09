<?php

namespace App\Filament\Widgets;

use App\Models\NpShipment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class NovaPoshtaStatsWidget extends BaseWidget
{
    protected static ?int $sort = 5;
    protected int|string|array $columnSpan = 'full';
    protected static ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $total = NpShipment::count();
        $active = NpShipment::query()->needsTracking()->count();
        $deliveredToday = NpShipment::where('status', NpShipment::STATUS_DELIVERED)
            ->whereDate('updated_at', today())
            ->count();
        $deliveredTotal = NpShipment::where('status', NpShipment::STATUS_DELIVERED)->count();
        $returned = NpShipment::where('status', NpShipment::STATUS_RETURNED)->count();

        $successRate = $total > 0
            ? round(($deliveredTotal / $total) * 100, 1)
            : 0;

        return [
            Stat::make('Усього ТТН', $total)
                ->description('У базі магазину')
                ->descriptionIcon('heroicon-o-truck')
                ->color('gray'),

            Stat::make('Активні', $active)
                ->description('У дорозі або очікуються')
                ->descriptionIcon('heroicon-o-arrow-path')
                ->color('warning'),

            Stat::make('Доставлено сьогодні', $deliveredToday)
                ->description("Загалом: {$deliveredTotal}")
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('Повернень', $returned)
                ->description('Скасовані / повернуті')
                ->descriptionIcon('heroicon-o-arrow-uturn-left')
                ->color($returned > 0 ? 'danger' : 'gray'),

            Stat::make('Успішність', $successRate.'%')
                ->description('Доставлено / усього')
                ->descriptionIcon($successRate >= 90 ? 'heroicon-o-trophy' : 'heroicon-o-chart-bar')
                ->color($successRate >= 90 ? 'success' : ($successRate >= 70 ? 'warning' : 'danger')),
        ];
    }

    public static function canView(): bool
    {
        return NpShipment::query()->limit(1)->exists();
    }
}
