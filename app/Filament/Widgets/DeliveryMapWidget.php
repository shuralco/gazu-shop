<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Support\Access\AccessControl;
use App\Support\Geo\UaCities;
use Filament\Widgets\Widget;

/**
 * Dashboard map of Ukraine plotting customer delivery locations (Nova Poshta /
 * Ukrposhta cities) aggregated from orders. Gated by RBAC (order data).
 */
class DeliveryMapWidget extends Widget
{
    protected static string $view = 'filament.widgets.delivery-map';

    // Не lazy: щоб @assets (Leaflet CSS/JS) інжектнулись у <head> при першому
    // рендері сторінки (lazy-фрагмент вантажиться після того, як head-хуки вже
    // відпрацювали, і ассети не потрапляють у документ).
    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 3;

    public static function canView(): bool
    {
        return AccessControl::can('OrderResource', 'view');
    }

    /**
     * @return array{points:array<int,array{lat:float,lng:float,city:string,count:int}>,total:int,mapped:int,unknown:int}
     */
    public function getMapData(): array
    {
        $rows = Order::query()
            ->whereNotNull('shipping_city')
            ->where('shipping_city', '!=', '')
            ->selectRaw('shipping_city, COUNT(*) as cnt')
            ->groupBy('shipping_city')
            ->get();

        $points = [];
        $total = 0;
        $unknown = 0;

        foreach ($rows as $r) {
            $cnt = (int) $r->cnt;
            $total += $cnt;
            $c = UaCities::coordsFor($r->shipping_city);
            if (! $c) {
                $unknown += $cnt;

                continue;
            }
            $k = $c[0].','.$c[1];
            if (! isset($points[$k])) {
                $points[$k] = ['lat' => $c[0], 'lng' => $c[1], 'city' => (string) $r->shipping_city, 'count' => 0];
            }
            $points[$k]['count'] += $cnt;
        }

        usort($points, fn ($a, $b) => $b['count'] <=> $a['count']);

        return [
            'points' => array_values($points),
            'total' => $total,
            'mapped' => $total - $unknown,
            'unknown' => $unknown,
        ];
    }
}
