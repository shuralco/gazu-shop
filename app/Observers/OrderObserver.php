<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\User;
use App\Notifications\NewOrderAdminNotification;
use App\Notifications\OrderCreatedNotification;
use App\Notifications\OrderStatusChangedNotification;
use App\Services\Checkbox\CheckboxService;
use Illuminate\Support\Facades\Notification;

class OrderObserver
{
    public function created(Order $order): void
    {
        $order->load('orderProducts');

        if ($order->user) {
            $order->user->notify(new OrderCreatedNotification($order));
        }

        $admins = User::where('is_admin', true)->get();
        Notification::send($admins, new NewOrderAdminNotification($order));
    }

    public function updated(Order $order): void
    {
        if ($order->isDirty('status')) {
            if ($order->user) {
                $order->user->notify(new OrderStatusChangedNotification($order));
            }

            // Release reservations on cancel; convert reservation → ship on shipped.
            $newStatus = $order->status;
            $oldStatus = $order->getOriginal('status');
            if (in_array($newStatus, ['cancelled', 'canceled', 'refunded']) && ! in_array($oldStatus, ['cancelled', 'canceled', 'refunded'])) {
                $this->releaseReservations($order);
            } elseif ($newStatus === 'shipped' && $oldStatus !== 'shipped') {
                $this->shipReservations($order);
            }
        }

        if ($order->isDirty('payment_status') && $order->payment_status === 'paid') {
            $checkbox = app(CheckboxService::class);
            if ($checkbox->isEnabled()) {
                $checkbox->createReceipt($order);
            }
        }
    }

    private function releaseReservations(Order $order): void
    {
        $invService = app(\App\Services\Warehouse\InventoryService::class);
        foreach ($order->orderProducts()->whereNotNull('warehouse_id')->get() as $line) {
            $product = $line->product;
            $warehouse = $line->warehouse;
            if (! $product || ! $warehouse) {
                continue;
            }
            try {
                $invService->release(
                    $product, $warehouse, (int) $line->quantity,
                    $order, null, 'Auto-release on cancel/refund of order #'.$order->id,
                );
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }

    private function shipReservations(Order $order): void
    {
        $invService = app(\App\Services\Warehouse\InventoryService::class);
        foreach ($order->orderProducts()->whereNotNull('warehouse_id')->get() as $line) {
            $product = $line->product;
            $warehouse = $line->warehouse;
            if (! $product || ! $warehouse) {
                continue;
            }
            try {
                $invService->ship(
                    $product, $warehouse, (int) $line->quantity,
                    $order, null, 'Auto-ship for order #'.$order->id,
                );
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }
}
