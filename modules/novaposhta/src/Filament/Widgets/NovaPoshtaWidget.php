<?php

namespace App\Filament\Widgets;

use App\Models\NpShipment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class NovaPoshtaWidget extends BaseWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $pollingInterval = '60s';

    public function getHeading(): ?string
    {
        return 'Нова Пошта — операційні метрики';
    }

    public static function canView(): bool
    {
        return \Schema::hasTable('np_shipments');
    }

    protected function getStats(): array
    {
        $today = now()->toDateString();

        $todayTotal = NpShipment::whereDate('created_at', $today)->count();
        $awaitingPickup = NpShipment::where('status', NpShipment::STATUS_CREATED)->count();
        $inTransit = NpShipment::where('status', NpShipment::STATUS_SENT)->count();
        $deliveredToday = NpShipment::where('status', NpShipment::STATUS_DELIVERED)
            ->whereDate('last_tracked_at', $today)
            ->count();

        return [
            Stat::make('Створено сьогодні', (string) $todayTotal)
                ->description('нових ТТН')
                ->color($todayTotal > 0 ? 'info' : 'gray')
                ->icon('heroicon-o-document-plus'),

            Stat::make('Очікують забору', (string) $awaitingPickup)
                ->description('у статусі «створено»')
                ->color($awaitingPickup > 0 ? 'warning' : 'gray')
                ->icon('heroicon-o-clock'),

            Stat::make('В дорозі', (string) $inTransit)
                ->description('активна доставка')
                ->color('primary')
                ->icon('heroicon-o-truck'),

            Stat::make('Доставлено сьогодні', (string) $deliveredToday)
                ->description('завершено')
                ->color('success')
                ->icon('heroicon-o-check-circle'),
        ];
    }
}
