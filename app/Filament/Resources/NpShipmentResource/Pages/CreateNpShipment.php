<?php

namespace App\Filament\Resources\NpShipmentResource\Pages;

use App\Filament\Resources\NpShipmentResource;
use App\Models\DisplaySetting;
use App\Models\NpCity;
use App\Models\NpShipment;
use App\Models\Order;
use App\Services\Shipping\NovaPoshtaTtnCreator;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateNpShipment extends CreateRecord
{
    protected static string $resource = NpShipmentResource::class;

    public function mount(): void
    {
        parent::mount();

        // Auto-fill from order if order_id is passed via query parameter
        $orderId = request()->query('order_id');
        if ($orderId) {
            $order = Order::with('orderProducts.product')->find($orderId);
            if ($order) {
                $fillData = $this->buildDataFromOrder($order);
                $this->data = array_merge($this->data ?? [], $fillData);
                $this->form->fill($this->data);
            }
        }
    }

    protected function buildDataFromOrder(Order $order): array
    {
        $data = ['order_id' => $order->id];

        $recipientName = trim("{$order->last_name} {$order->first_name} {$order->middle_name}");
        $data['recipient_name'] = $recipientName ?: ($order->name ?? '');
        $data['recipient_phone'] = $order->phone ?? '';

        // Declared value
        $method = DisplaySetting::get('np_declared_value_method', 'order_total');
        $declaredValue = match ($method) {
            'order_total' => (float) $order->total,
            'products_total' => (float) $order->orderProducts->sum(fn ($op) => $op->price * $op->quantity),
            'custom' => (float) DisplaySetting::get('np_default_declared_value', 300),
            default => (float) $order->total,
        };
        $minValue = (float) DisplaySetting::get('np_min_declared_value', 100);
        $data['cost'] = max($declaredValue, $minValue);

        // COD
        if (in_array($order->payment_method, ['cod', 'cash_on_delivery', 'cash'])) {
            $data['cod_amount'] = (float) $order->total;
        }

        $data['weight'] = $order->calculateTotalWeight();

        // Description
        $template = DisplaySetting::get('np_description_template', 'Замовлення #{order_id}');
        $products = $order->orderProducts->map(fn ($op) => $op->product?->name ?? 'Товар')->implode(', ');
        $data['description'] = str_replace(
            ['{order_id}', '{products}', '{total}', '{customer_name}'],
            [$order->id, $products, $order->total, $recipientName],
            $template
        );

        // Shipping data
        $shippingData = is_array($order->shipping_data) ? $order->shipping_data : json_decode($order->shipping_data ?? '{}', true);
        if (! empty($shippingData['city_ref'])) {
            $data['recipient_city_ref'] = $shippingData['city_ref'];
            $city = NpCity::where('ref', $shippingData['city_ref'])->first();
            $data['recipient_city_name'] = $city?->description ?? ($shippingData['city'] ?? '');
        }
        if (! empty($shippingData['warehouse_ref'])) {
            $data['recipient_warehouse_ref'] = $shippingData['warehouse_ref'];
        }

        // Service type
        $shippingMethod = $order->shipping_method ?? '';
        $data['service_type'] = match ($shippingMethod) {
            'courier' => 'WarehouseDoors',
            default => 'WarehouseWarehouse',
        };

        if ($shippingMethod === 'courier') {
            $data['recipient_street'] = $shippingData['street'] ?? '';
            $data['recipient_house'] = $shippingData['building'] ?? $shippingData['house'] ?? '';
            $data['recipient_flat'] = $shippingData['apartment'] ?? $shippingData['flat'] ?? '';
            $data['recipient_floor'] = $shippingData['floor'] ?? null;
            $data['recipient_has_elevator'] = ! empty($shippingData['has_elevator']);
        }

        // Legal entity (company)
        if (! empty($shippingData['is_company'])) {
            $data['recipient_edrpou'] = $shippingData['edrpou'] ?? '';
            $data['recipient_company_name'] = $shippingData['company_name'] ?? '';
            $data['recipient_contact_name'] = $shippingData['contact_person'] ?? '';
        }

        // Preferred delivery
        if (! empty($shippingData['preferred_date'])) {
            $data['preferred_delivery_date'] = $shippingData['preferred_date'];
        }
        if (! empty($shippingData['preferred_time'])) {
            $parts = explode('-', $shippingData['preferred_time']);
            $data['preferred_delivery_time_from'] = $parts[0] ?? null;
            $data['preferred_delivery_time_to'] = $parts[1] ?? null;
        }

        // Defaults from settings
        $data['cargo_type'] = DisplaySetting::get('np_default_cargo_type', 'Parcel');
        $data['seats_amount'] = DisplaySetting::get('np_default_seats_amount', 1);
        $data['payer_type'] = DisplaySetting::get('np_default_payer', 'Recipient');
        $data['payment_method'] = DisplaySetting::get('np_default_payment_form', 'Cash');
        // Sender refs: prefer order's warehouse (Phase 3), fall back to legacy DisplaySetting.
        $warehouse = $order->warehouse ?? \App\Models\MerchantWarehouse::default();
        $data['sender_ref'] = $warehouse?->np_sender_ref ?: DisplaySetting::get('np_sender_ref', '');
        $data['sender_city_ref'] = $warehouse?->np_sender_city_ref ?: DisplaySetting::get('np_sender_city_ref', '');
        $data['sender_warehouse_ref'] = $warehouse?->np_sender_warehouse_ref ?: DisplaySetting::get('np_sender_warehouse_ref', '');

        return $data;
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set status to new initially
        $data['status'] = NpShipment::STATUS_NEW;

        // Resolve city name if we have a ref
        if (! empty($data['recipient_city_ref']) && empty($data['recipient_city_name'])) {
            $city = NpCity::where('ref', $data['recipient_city_ref'])->first();
            $data['recipient_city_name'] = $city?->description ?? '';
        }

        // Build address for courier delivery
        if (in_array($data['service_type'] ?? '', ['WarehouseDoors', 'DoorsDoors'])) {
            $address = trim(implode(', ', array_filter([
                $data['recipient_street'] ?? '',
                $data['recipient_house'] ?? '',
                ! empty($data['recipient_flat']) ? "кв. {$data['recipient_flat']}" : '',
                ! empty($data['recipient_floor']) ? "{$data['recipient_floor']} пов." : '',
            ])));
            $data['recipient_address'] = $address;
        }

        // Compute volume_weight from parcels if provided
        if (! empty($data['parcels']) && is_array($data['parcels'])) {
            $totalVolume = 0;
            foreach ($data['parcels'] as $p) {
                $l = (float) ($p['length'] ?? 0);
                $w = (float) ($p['width'] ?? 0);
                $h = (float) ($p['height'] ?? 0);
                if ($l > 0 && $w > 0 && $h > 0) {
                    $totalVolume += ($l * $w * $h) / 4000;
                }
            }
            if ($totalVolume > 0) {
                $data['volume_weight'] = round($totalVolume, 3);
            }
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $result = app(NovaPoshtaTtnCreator::class)->createForShipment($this->record);

        if ($result['success']) {
            Notification::make()
                ->title('ТТН створено!')
                ->body("Номер: {$result['ttn']}")
                ->success()
                ->duration(10000)
                ->send();
        } else {
            $errors = implode('; ', $result['errors']);
            Notification::make()
                ->title('Помилка створення ТТН')
                ->body("ТТН збережено локально, але НП API повернув помилку: {$errors}\n\nВи можете виправити дані та повторити запит.")
                ->warning()
                ->duration(15000)
                ->send();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }
}
