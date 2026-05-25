<?php

namespace App\Services\Shipping;

use App\Models\DisplaySetting;
use App\Models\MerchantWarehouse;
use App\Models\NpShipment;
use App\Services\NovaPoshtaApiService;
use Illuminate\Support\Facades\Log;

/**
 * Send a stored NpShipment to Nova Poshta API and persist returned ttn/ref.
 * Used by both CreateNpShipment::afterCreate and EditNpShipment retry action.
 *
 * @return array{success:bool,ttn:?string,ref:?string,errors:array}
 */
class NovaPoshtaTtnCreator
{
    public function __construct(private NovaPoshtaApiService $api)
    {
    }

    public function createForShipment(NpShipment $shipment): array
    {
        try {
            $apiData = $this->buildApiPayload($shipment);
            $result = $this->api->createShipment($apiData);

            if (($result['success'] ?? false) && ! empty($result['data'])) {
                $doc = $result['data'][0];
                $ttn = $doc['IntDocNumber'] ?? '';
                $ref = $doc['Ref'] ?? '';

                $shipment->update([
                    'ttn' => $ttn,
                    'ref' => $ref,
                    'status' => NpShipment::STATUS_CREATED,
                    'shipping_cost' => $doc['CostOnSite'] ?? 0,
                    'estimated_delivery_date' => $doc['EstimatedDeliveryDate'] ?? null,
                ]);

                if ($shipment->order) {
                    app(\App\Services\Warehouse\OrderFulfillmentService::class)
                        ->shipOrder($shipment->order);
                }

                return ['success' => true, 'ttn' => $ttn, 'ref' => $ref, 'errors' => []];
            }

            $errors = $result['errors'] ?? ['Невідома помилка'];
            Log::error('NP create TTN failed', ['errors' => $errors, 'shipment_id' => $shipment->id]);

            return ['success' => false, 'ttn' => null, 'ref' => null, 'errors' => $errors];
        } catch (\Throwable $e) {
            Log::error('NP create TTN exception: '.$e->getMessage(), ['shipment_id' => $shipment->id]);

            return ['success' => false, 'ttn' => null, 'ref' => null, 'errors' => [$e->getMessage()]];
        }
    }

    protected function buildApiPayload(NpShipment $shipment): array
    {
        // NP API limit: Cost ≤ 1,000,000 UAH for cargo ≤ 30 kg.
        $weight = (float) ($shipment->weight ?? 0.5);
        $cost = (float) ($shipment->cost ?? 300);
        if ($weight <= 30 && $cost >= 1_000_000) {
            $cost = 999_999;
        }

        $sender = $this->resolveSender($shipment);

        $apiData = [
            'NewAddress' => '1',
            'PayerType' => $shipment->payer_type ?? 'Recipient',
            'PaymentMethod' => $shipment->payment_method ?? 'Cash',
            'CargoType' => $shipment->cargo_type ?? 'Parcel',
            'Weight' => (string) $weight,
            'ServiceType' => $shipment->service_type ?? 'WarehouseWarehouse',
            'SeatsAmount' => (string) ($shipment->seats_amount ?? 1),
            'Description' => $shipment->description ?? 'Товари',
            'Cost' => (string) $cost,
            'CitySender' => $sender['city_ref'],
            'Sender' => $sender['ref'],
            'SenderAddress' => $sender['warehouse_ref'],
            'ContactSender' => $sender['contact_ref'],
            'SendersPhone' => $sender['phone'],
            'CityRecipient' => $shipment->recipient_city_ref,
            'Recipient' => '',
            'RecipientAddress' => $shipment->recipient_warehouse_ref ?? '',
            'ContactRecipient' => '',
            'RecipientsPhone' => $this->normalizePhone($shipment->recipient_phone ?? ''),
            'RecipientName' => $this->normalizeRecipientName($shipment->recipient_name ?? ''),
        ];

        if ($shipment->volume && $shipment->volume > 0) {
            $apiData['VolumeGeneral'] = (string) $shipment->volume;
        }

        if ($shipment->cod_amount && $shipment->cod_amount > 0) {
            $apiData['BackwardDeliveryData'] = [[
                'PayerType' => 'Recipient',
                'CargoType' => 'Money',
                'RedeliveryString' => (string) $shipment->cod_amount,
            ]];
        }

        if (in_array($shipment->service_type, ['WarehouseDoors', 'DoorsDoors'])) {
            unset($apiData['RecipientAddress']);
            $apiData['RecipientAddressName'] = $shipment->recipient_street
                ?: ($shipment->recipient_address ?? '');
            $apiData['RecipientHouse'] = (string) ($shipment->recipient_house ?? '');
            $apiData['RecipientFlat'] = (string) ($shipment->recipient_flat ?? '');
        }

        return $apiData;
    }

    /**
     * NP requires ≥ 2 words for RecipientName (Прізвище Ім'я) AND only Cyrillic
     * letters (Latin names are rejected with "RecipientName incorrect").
     */
    public function normalizeRecipientName(string $name): string
    {
        $name = trim(preg_replace('/\s+/', ' ', $name));

        if ($name === '') {
            return 'Отримувач Замовлення';
        }

        // If string is purely Latin/ASCII, NP rejects it. Replace with safe Cyrillic placeholder.
        if (! preg_match('/[\x{0400}-\x{04FF}]/u', $name)) {
            return 'Отримувач Замовлення';
        }

        $words = array_values(array_filter(explode(' ', $name), fn ($w) => mb_strlen($w) >= 2));

        if (count($words) < 2) {
            $words[] = 'Клієнт';
        }

        return implode(' ', $words);
    }

    /**
     * Canonical NP phone format: 380XXXXXXXXX.
     */
    public function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);
        if ($digits === '') {
            return '';
        }
        if (str_starts_with($digits, '380')) {
            $digits = substr($digits, 3);
        } elseif (str_starts_with($digits, '38')) {
            $digits = substr($digits, 2);
        }
        if (str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }

        return '380'.$digits;
    }

    /**
     * Resolve NP sender refs in priority order:
     *   1) shipment.sender_* fields (explicit override on this TTN)
     *   2) shipment.warehouse.np_sender_* (split-TTN per-warehouse origin)
     *   3) order.warehouse.np_sender_* (single-warehouse order)
     *   4) DisplaySetting np_sender_* (legacy global fallback)
     */
    protected function resolveSender(NpShipment $shipment): array
    {
        $warehouse = $shipment->warehouse
            ?? $shipment->order?->warehouse
            ?? MerchantWarehouse::default();

        return [
            'ref' => $shipment->sender_ref
                ?: ($warehouse?->np_sender_ref ?: DisplaySetting::get('np_sender_ref', '')),
            'city_ref' => $shipment->sender_city_ref
                ?: ($warehouse?->np_sender_city_ref ?: DisplaySetting::get('np_sender_city_ref', '')),
            'warehouse_ref' => $warehouse?->np_sender_warehouse_ref
                ?: DisplaySetting::get('np_sender_warehouse_ref', ''),
            'contact_ref' => $warehouse?->np_contact_person_ref
                ?: DisplaySetting::get('np_contact_person_ref', ''),
            'phone' => $warehouse?->np_sender_phone
                ?: DisplaySetting::get('np_sender_phone', ''),
        ];
    }
}
