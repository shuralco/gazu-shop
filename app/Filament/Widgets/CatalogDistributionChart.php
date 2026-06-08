<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use App\Models\Product;
use Filament\Widgets\ChartWidget;

class CatalogDistributionChart extends ChartWidget
{
    public static function canView(): bool
    {
        return \App\Support\Access\AccessControl::can('ProductResource', 'view');
    }

    protected static ?string $heading = 'Каталог за категоріями (L1)';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = ['md' => 1, 'xl' => 2];

    protected static ?string $maxHeight = '320px';

    protected function getData(): array
    {
        // Resolve parent map once
        $parents = Category::query()->pluck('parent_id', 'id')->all();
        $rootOf = function (int $id) use ($parents): int {
            $cur = $id;
            while (! empty($parents[$cur])) {
                $cur = $parents[$cur];
            }
            return $cur;
        };

        // Sum products per root category
        $counts = Product::query()
            ->where('is_active', true)
            ->whereNotNull('category_id')
            ->selectRaw('category_id, COUNT(*) as c')
            ->groupBy('category_id')
            ->pluck('c', 'category_id')
            ->all();

        $totals = [];
        foreach ($counts as $catId => $cnt) {
            $root = $rootOf((int) $catId);
            $totals[$root] = ($totals[$root] ?? 0) + (int) $cnt;
        }

        arsort($totals);
        $roots = Category::whereIn('id', array_keys($totals))->get(['id', 'title'])->keyBy('id');

        $labels = [];
        $data = [];
        foreach ($totals as $rootId => $cnt) {
            $labels[] = (string) ($roots[$rootId]->title ?? 'Інше');
            $data[] = $cnt;
        }

        return [
            'datasets' => [[
                'label' => 'Товарів',
                'data' => $data,
                'backgroundColor' => [
                    '#2453ff', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6',
                    '#0ea5e9', '#ec4899', '#84cc16', '#6366f1', '#14b8a6',
                ],
                'borderWidth' => 0,
            ]],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['position' => 'right'],
            ],
            'cutout' => '55%',
        ];
    }
}
