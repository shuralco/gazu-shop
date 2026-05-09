<?php

namespace App\Filament\Resources\UpShipmentResource\Pages;

use App\Filament\Resources\UpShipmentResource;
use App\Models\Order;
use Filament\Resources\Pages\CreateRecord;

class CreateUpShipment extends CreateRecord
{
    protected static string $resource = UpShipmentResource::class;

    public function mount(): void
    {
        parent::mount();

        $orderId = request()->query('order_id');
        if (! $orderId) {
            return;
        }

        $order = Order::find($orderId);
        if (! $order) {
            return;
        }

        $name = trim("{$order->last_name} {$order->first_name} {$order->middle_name}") ?: ($order->name ?? '');

        $this->form->fill([
            'order_id' => $order->id,
            'recipient_name' => $name,
            'recipient_phone' => $order->phone ?? '',
            'recipient_email' => $order->email ?? '',
            'declared_value' => (float) $order->total,
            'service_type' => 'branch',
            'description' => "Замовлення #{$order->id}",
        ]);
    }
}
