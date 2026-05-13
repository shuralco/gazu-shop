<?php

namespace App\Filament\Widgets;

use App\Models\Brand;
use App\Models\Product;
use Filament\Widgets\ChartWidget;

class BrandDistributionChart extends ChartWidget
{
    protected static ?string $heading = 'Топ-12 брендів за SKU';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = ['md' => 1, 'xl' => 2];

    protected static ?string $maxHeight = '320px';

    protected function getData(): array
    {
        $rows = Product::query()
            ->where('is_active', true)
            ->whereNotNull('brand_id')
            ->selectRaw('brand_id, COUNT(*) as c')
            ->groupBy('brand_id')
            ->orderByDesc('c')
            ->limit(12)
            ->pluck('c', 'brand_id')
            ->all();

        if (empty($rows)) {
            return [
                'datasets' => [['data' => [], 'label' => 'Товарів']],
                'labels' => [],
            ];
        }

        $brands = Brand::whereIn('id', array_keys($rows))->get(['id', 'name'])->keyBy('id');

        $labels = [];
        $data = [];
        foreach ($rows as $brandId => $cnt) {
            $labels[] = (string) ($brands[$brandId]->name ?? '—');
            $data[] = (int) $cnt;
        }

        return [
            'datasets' => [[
                'label' => 'SKU',
                'data' => $data,
                'backgroundColor' => '#2453ff',
                'borderRadius' => 6,
            ]],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'plugins' => [
                'legend' => ['display' => false],
            ],
            'scales' => [
                'x' => ['beginAtZero' => true],
            ],
        ];
    }
}
