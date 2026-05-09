<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;

class OrdersChart extends ChartWidget
{
    protected static ?string $heading = 'Огляд замовлень';

    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $orders = Order::selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(total) as revenue')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Замовлень на день',
                    'data' => $orders->pluck('count')->toArray(),
                    'backgroundColor' => '#3B82F6',
                    'borderColor' => '#1E40AF',
                ],
                [
                    'label' => 'Дохід на день',
                    'data' => $orders->pluck('revenue')->toArray(),
                    'backgroundColor' => '#10B981',
                    'borderColor' => '#047857',
                ],
            ],
            'labels' => $orders->pluck('date')->map(fn ($date) => \Carbon\Carbon::parse($date)->format('M j')
            )->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
