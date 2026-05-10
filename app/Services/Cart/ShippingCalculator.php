<?php

namespace App\Services\Cart;

use App\Helpers\Cart\Cart;
use App\Models\MerchantWarehouse;
use Illuminate\Support\Collection;

/**
 * Per-warehouse shipping cost calculator (Phase 7).
 *
 * Splits cart lines by warehouse_id, sums each group's subtotal,
 * applies the warehouse's shipping_cost (waived if subtotal ≥
 * free_shipping_threshold). Lines with no warehouse_id contribute to
 * a synthetic "default" group that uses the default warehouse's rate.
 */
class ShippingCalculator
{
    /**
     * @return array{
     *   groups: list<array{warehouse: ?MerchantWarehouse, subtotal: float, shipping: float, free: bool, items: int}>,
     *   subtotal: float,
     *   shipping_total: float,
     *   grand_total: float
     * }
     */
    public function breakdown(?array $cart = null): array
    {
        $cart = $cart ?? Cart::getCart();

        $byWarehouse = collect($cart)->groupBy(fn ($line) => (int) ($line['warehouse_id'] ?? 0));

        $warehouseIds = $byWarehouse->keys()->filter()->all();
        $warehouses = MerchantWarehouse::query()->whereIn('id', $warehouseIds)->get()->keyBy('id');
        $default = $warehouseIds ? null : MerchantWarehouse::default();

        $groups = [];
        $subtotal = 0.0;
        $shippingTotal = 0.0;

        foreach ($byWarehouse as $whId => $lines) {
            $wh = $whId ? ($warehouses[$whId] ?? null) : $default;
            $groupSubtotal = 0.0;
            $groupItems = 0;
            foreach ($lines as $line) {
                $qty = (int) ($line['quantity'] ?? 1);
                $price = (float) ($line['price'] ?? 0);
                $groupSubtotal += $price * $qty;
                $groupItems += $qty;
            }

            $shippingCost = (float) ($wh->shipping_cost ?? 0);
            $threshold = $wh?->free_shipping_threshold !== null ? (float) $wh->free_shipping_threshold : null;
            $free = $threshold !== null && $groupSubtotal >= $threshold;
            $shipping = $free ? 0.0 : $shippingCost;

            $groups[] = [
                'warehouse' => $wh,
                'subtotal' => $groupSubtotal,
                'shipping' => $shipping,
                'free' => $free,
                'items' => $groupItems,
            ];

            $subtotal += $groupSubtotal;
            $shippingTotal += $shipping;
        }

        return [
            'groups' => $groups,
            'subtotal' => $subtotal,
            'shipping_total' => $shippingTotal,
            'grand_total' => $subtotal + $shippingTotal,
        ];
    }
}
