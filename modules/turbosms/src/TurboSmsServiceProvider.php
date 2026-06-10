<?php

namespace Modules\TurboSms;

use App\Jobs\SendTemplatedSms;
use App\Models\DisplaySetting;
use App\Models\Order;
use Illuminate\Support\ServiceProvider;

/**
 * TurboSMS (SMS + Viber) — транзакційні повідомлення по подіях замовлення.
 *
 * Кожна подія має власний тоггл у DisplaySetting (адмінка → TurboSMS):
 *   turbosms_event_order_created   — SMS при створенні замовлення
 *   turbosms_event_order_paid     — при отриманні оплати
 *   turbosms_event_order_shipped  — при створенні ТТН (NpShipment)
 *   turbosms_event_status_changed — при зміні статусу замовлення
 *
 * Відправка — queued (SendTemplatedSms) → не блокує запит, лог у sms_messages.
 */
class TurboSmsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(\App\Services\TurboSms\TurboSmsClient::class);
    }

    public function boot(): void
    {
        // Глобальний гейт: модуль вимкнено → жодних слухачів.
        if (! \App\Support\ModuleManager::for('turbosms')->enabled()) {
            return;
        }

        // --- Замовлення створено -------------------------------------------------
        Order::created(function (Order $order) {
            if (! self::eventOn('turbosms_event_order_created')) {
                return;
            }
            self::queueForOrder('order.created', $order);
        });

        // --- Оплата отримана / статус змінено ------------------------------------
        Order::updated(function (Order $order) {
            // оплата: payment_status → paid АБО paid_at щойно зʼявився
            $paidNow = ($order->wasChanged('payment_status') && $order->payment_status === 'paid')
                || ($order->wasChanged('paid_at') && $order->paid_at !== null);
            if ($paidNow && self::eventOn('turbosms_event_order_paid')) {
                self::queueForOrder('order.paid', $order);
            }

            if ($order->wasChanged('status') && self::eventOn('turbosms_event_status_changed')) {
                $label = \App\Models\OrderStatus::options()[$order->status] ?? $order->status;
                self::queueForOrder('order.status_changed', $order, ['status_label' => $label]);
            }
        });

        // --- ТТН створено (Нова Пошта) -------------------------------------------
        if (class_exists(\App\Models\NpShipment::class)) {
            \App\Models\NpShipment::created(function ($shipment) {
                if (! self::eventOn('turbosms_event_order_shipped')) {
                    return;
                }
                $order = $shipment->order ?? Order::find($shipment->order_id);
                if (! $order) {
                    return;
                }
                self::queueForOrder('order.shipped', $order, [
                    'ttn' => (string) ($shipment->ttn ?? $shipment->int_doc_number ?? ''),
                    'carrier' => 'Нова Пошта',
                ]);
            });
        }
    }

    private static function eventOn(string $key): bool
    {
        return (bool) DisplaySetting::get($key, false);
    }

    private static function queueForOrder(string $templateKey, Order $order, array $extra = []): void
    {
        if (! $order->phone) {
            return;
        }

        SendTemplatedSms::dispatch($templateKey, (string) $order->phone, [
            'order' => array_merge([
                'id' => (string) $order->id,
                'total' => number_format((float) $order->total, 0, '.', ' '),
                'customer_name' => trim($order->first_name.' '.$order->last_name),
            ], $extra),
        ], $order->id);
    }
}
