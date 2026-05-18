<?php

namespace App\Observers;

use App\Mail\TemplatedMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * При створенні TTN (NpShipment/UpShipment) — автоматичний email клієнту
 * з template order.shipped (editable у /admin/email-templates).
 *
 * Триггерить на 'created' події — як тільки admin створює запис у адмінці
 * через NpShipmentResource / UpShipmentResource, клієнт отримує лист з ТТН.
 */
class ShipmentNotificationObserver
{
    public function created($shipment): void
    {
        if (! ($shipment->ttn ?? null)) {
            return; // No TTN yet — admin still drafting, no notification.
        }
        $this->sendShippedEmail($shipment);
    }

    public function updated($shipment): void
    {
        // Trigger only при ПЕРШІЙ появі ttn (raw ttn was empty before).
        if (! ($shipment->wasChanged('ttn'))) return;
        if (! ($shipment->ttn ?? null)) return;
        $original = $shipment->getOriginal('ttn');
        if (! empty($original)) return; // Already had a TTN — оновлення лише, не нова відправка.
        $this->sendShippedEmail($shipment);
    }

    private function sendShippedEmail($shipment): void
    {
        try {
            $order = $shipment->order ?? \App\Models\Order::find($shipment->order_id);
            if (! $order || empty($order->email)) {
                return;
            }

            $carrier = $shipment instanceof \App\Models\NpShipment ? 'Нова Пошта' : 'Укрпошта';
            $trackingUrl = $shipment instanceof \App\Models\NpShipment
                ? 'https://novaposhta.ua/tracking/'.$shipment->ttn
                : 'https://track.ukrposhta.ua/tracking_UA.html?barcode='.$shipment->ttn;

            Mail::to($order->email)->queue(new TemplatedMail('order.shipped', [
                'order' => [
                    'id' => $order->id,
                    'customer_name' => $order->name ?: 'Клієнт',
                    'ttn' => $shipment->ttn,
                    'carrier' => $carrier,
                    'tracking_url' => $trackingUrl,
                    'expected_date' => optional($shipment->preferred_delivery_date ?? null)->format('d.m.Y') ?: '1-3 дні',
                ],
            ]));
        } catch (\Throwable $e) {
            Log::warning('Shipment shipped email failed: '.$e->getMessage(), ['shipment_id' => $shipment->id ?? null]);
        }
    }
}
