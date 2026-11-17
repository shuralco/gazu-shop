<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Order;
use App\Support\Tyn\TynTrack;
use Throwable;

/**
 * Серверні події Тин: order (створення) і order_status (paid, cancelled, refunded). Без контактів, адрес доставки,
 * коментаря й назв товарів: лише ref (HMAC номера), сума, позиції [id, к-сть, ціна], спосіб доставки й оплати.
 * Самі події йдуть після відповіді (TynTraffic::terminate); без TYN_TRACK нічого не робить.
 */
class TynOrderObserver
{
    public function created(Order $order): void
    {
        try {
            if (!TynTrack::enabled()) {
                return;
            }
            require_once __DIR__ . '/../Support/Tyn/tyn-lib.php';
            $items = [];
            foreach ($order->orderProducts()->get(['product_id', 'quantity', 'price']) as $line) {
                $items[] = [(string) $line->product_id, (int) $line->quantity, TynTrack::minor($line->price)];
            }
            TynTrack::add('order', [
                'ref' => TynTrack::orderRef($order->id),
                'amount' => TynTrack::minor($order->total),
                'cur' => 'UAH',
                'items' => $items,
                'ship' => \tyn_track_guess_ship((string) $order->shipping_method),
                'pay' => \tyn_track_guess_pay((string) $order->payment_method),
            ]);
        } catch (Throwable) {
        }
    }

    public function updated(Order $order): void
    {
        try {
            if (!TynTrack::enabled()) {
                return;
            }
            $status = '';
            if ($order->wasChanged('payment_status') && $order->payment_status === 'paid') {
                $status = 'paid';
            } elseif ($order->wasChanged('status')) {
                $status = match ((string) $order->status) {
                    'cancelled', 'canceled' => 'cancelled',
                    'refunded' => 'refunded',
                    default => '',
                };
            }
            if ($status !== '') {
                TynTrack::add('order_status', ['ref' => TynTrack::orderRef($order->id), 'status' => $status]);
            }
        } catch (Throwable) {
        }
    }
}
