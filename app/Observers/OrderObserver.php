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
        }

        if ($order->isDirty('payment_status') && $order->payment_status === 'paid') {
            $checkbox = app(CheckboxService::class);
            if ($checkbox->isEnabled()) {
                $checkbox->createReceipt($order);
            }
        }
    }
}
