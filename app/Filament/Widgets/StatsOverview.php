<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Загально користувачів', User::count())
                ->description('Зареєстровані користувачі')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('success'),

            Stat::make('Загально товарів', Product::count())
                ->description('Активні товари')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([15, 4, 10, 2, 12, 4, 12])
                ->color('primary'),

            Stat::make('Загально замовлень', Order::count())
                ->description('Всі замовлення')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([3, 5, 10, 12, 8, 15, 20])
                ->color('warning'),

            Stat::make('Дохід', '₴'.number_format(Order::sum('total'), 2))
                ->description('Загальний дохід')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([1000, 2000, 3500, 5000, 4500, 7000, 8500])
                ->color('danger'),
        ];
    }
}
