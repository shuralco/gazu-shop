<?php

namespace App\Services\Shipping;

use App\Models\NpShipment;
use App\Models\Order;
use App\Models\UpShipment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Splits an order into one draft shipment per source warehouse.
 *
 * Each order_products row may carry its own warehouse_id (multi-vendor
 * pricing). When the merchant wants to ship the order:
 *   - group lines by warehouse_id;
 *   - emit one Np/UpShipment per group, pinned to that warehouse;
 *   - each shipment inherits recipient + delivery prefs from order;
 *   - sender refs are resolved later by Nova Poshta / UkrPoshta service
 *     from shipment.warehouse (preferred) → order.warehouse → defaults.
 *
 * Caller (Filament action) decides whether to actually create TTN at the
 * carrier or save as draft. This service only writes shipment rows.
 */
class OrderShipmentSplitter
{
    /**
     * Build draft NpShipment rows for the given order — one per source warehouse.
     * Skips warehouses that already have an existing NpShipment (any status).
     *
     * @return Collection<int, NpShipment>
     */
    public function splitNova(Order $order): Collection
    {
        $order->load(['orderProducts.warehouse']);

        $byWarehouse = $order->orderProducts
            ->whereNotNull('warehouse_id')
            ->groupBy('warehouse_id');

        if ($byWarehouse->isEmpty()) {
            return collect();
        }

        $existing = NpShipment::where('order_id', $order->id)
            ->whereNotNull('warehouse_id')
            ->pluck('id', 'warehouse_id');

        return DB::transaction(fn () => $byWarehouse
            ->reject(fn ($_, $whId) => $existing->has((int) $whId))
            ->map(fn ($lines, $whId) => $this->createNpDraft($order, (int) $whId, $lines))
            ->values()
        );
    }

    /**
     * Same idea but for Ukrposhta.
     *
     * @return Collection<int, UpShipment>
     */
    public function splitUkr(Order $order): Collection
    {
        $order->load(['orderProducts.warehouse']);

        $byWarehouse = $order->orderProducts
            ->whereNotNull('warehouse_id')
            ->groupBy('warehouse_id');

        if ($byWarehouse->isEmpty()) {
            return collect();
        }

        $existing = UpShipment::where('order_id', $order->id)
            ->whereNotNull('warehouse_id')
            ->pluck('id', 'warehouse_id');

        return DB::transaction(fn () => $byWarehouse
            ->reject(fn ($_, $whId) => $existing->has((int) $whId))
            ->map(fn ($lines, $whId) => $this->createUpDraft($order, (int) $whId, $lines))
            ->values()
        );
    }

    /**
     * Convenience: pick provider from order.shipping_method-style hint and
     * delegate. Defaults to Nova Poshta.
     */
    public function split(Order $order, string $provider = 'nova'): Collection
    {
        return match (strtolower($provider)) {
            'ukr', 'ukrposhta', 'up' => $this->splitUkr($order),
            default => $this->splitNova($order),
        };
    }

    // ----------------------------------------------------------------------

    private function createNpDraft(Order $order, int $warehouseId, Collection $lines): NpShipment
    {
        $totalWeight = (float) $lines->sum(fn ($l) => max(0.1, (float) ($l->product?->weight ?? 0.5)) * $l->quantity);
        $totalCost = (float) $lines->sum(fn ($l) => (float) $l->price * (int) $l->quantity);

        return NpShipment::create([
            'order_id' => $order->id,
            'warehouse_id' => $warehouseId,
            'status' => 'draft',
            // Recipient — same for every split shipment of this order.
            'recipient_name' => trim(($order->first_name ?? '').' '.($order->last_name ?? '')),
            'recipient_phone' => $order->phone,
            'recipient_email' => $order->email,
            'recipient_city_ref' => $order->np_city_ref ?? null,
            'recipient_city_name' => $order->shipping_city,
            'recipient_warehouse_ref' => $order->np_warehouse_ref ?? null,
            'recipient_warehouse_name' => $order->shipping_warehouse,
            'recipient_address' => $order->shipping_address,
            // Cargo
            'service_type' => 'WarehouseWarehouse',
            'cargo_type' => 'Parcel',
            'weight' => round(max(0.1, $totalWeight), 3),
            'seats_amount' => 1,
            'cost' => $totalCost,
            'declared_cost' => $totalCost,
            'description' => 'Order #'.$order->id.' (split from warehouse '.$warehouseId.')',
            'payer_type' => 'Recipient',
            'payment_method' => 'Cash',
        ]);
    }

    private function createUpDraft(Order $order, int $warehouseId, Collection $lines): UpShipment
    {
        $totalWeight = (float) $lines->sum(fn ($l) => max(0.1, (float) ($l->product?->weight ?? 0.5)) * $l->quantity);
        $totalCost = (float) $lines->sum(fn ($l) => (float) $l->price * (int) $l->quantity);

        return UpShipment::create([
            'order_id' => $order->id,
            'warehouse_id' => $warehouseId,
            'status' => UpShipment::STATUS_NEW,
            'recipient_name' => trim(($order->first_name ?? '').' '.($order->last_name ?? '')),
            'recipient_phone' => $order->phone,
            'recipient_email' => $order->email,
            'recipient_city_id' => $order->up_city_id ?? null,
            'recipient_city_name' => $order->shipping_city,
            'recipient_branch_id' => $order->up_branch_id ?? null,
            'recipient_branch_address' => $order->shipping_warehouse,
            'recipient_postcode' => $order->shipping_postcode ?? null,
            'service_type' => 'standard',
            'weight' => round(max(0.1, $totalWeight), 3),
            'declared_value' => $totalCost,
            'shipping_cost' => 0,
            'description' => 'Order #'.$order->id.' (split from warehouse '.$warehouseId.')',
        ]);
    }
}
