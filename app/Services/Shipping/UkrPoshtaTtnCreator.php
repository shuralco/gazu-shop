<?php

namespace App\Services\Shipping;

use App\Models\MerchantWarehouse;
use App\Models\UpShipment;
use App\Services\UkrPoshtaEcomService;
use Illuminate\Support\Facades\Log;

/**
 * Wraps UkrPoshtaEcomService into a single createForShipment() entry point
 * that mirrors NovaPoshtaTtnCreator. Persists the returned uuid/barcode
 * back onto the local UpShipment.
 */
class UkrPoshtaTtnCreator
{
    public function __construct(private UkrPoshtaEcomService $api) {}

    /**
     * @return array{success:bool,ttn:?string,uuid:?string,errors:array}
     */
    public function createForShipment(UpShipment $shipment): array
    {
        if (! empty($shipment->ttn)) {
            return [
                'success' => false,
                'ttn' => $shipment->ttn,
                'uuid' => null,
                'errors' => ['ТТН вже існує: '.$shipment->ttn],
            ];
        }

        // Bind ecom service to shipment's order warehouse so up_sender_*
        // come from the per-warehouse config, not global DisplaySetting.
        $warehouse = $shipment->order?->warehouse ?? MerchantWarehouse::default();
        $this->api = $this->api->forWarehouse($warehouse);

        try {
            // Step 1: register recipient address.
            $addrResult = $this->api->createAddress($this->api->buildAddressPayload($shipment));
            if (! $addrResult['success']) {
                return [
                    'success' => false,
                    'ttn' => null,
                    'uuid' => null,
                    'errors' => array_merge(['Не вдалося створити адресу:'], $addrResult['errors']),
                ];
            }

            // Step 2: register recipient client referencing addressId.
            $clientResult = $this->api->createClient(
                $this->api->buildClientPayload($shipment, $addrResult['address_id'])
            );
            if (! $clientResult['success']) {
                return [
                    'success' => false,
                    'ttn' => null,
                    'uuid' => null,
                    'errors' => array_merge(['Не вдалося створити клієнта-отримувача:'], $clientResult['errors']),
                ];
            }

            // Step 3: build shipment payload referencing recipient uuid.
            $payload = $this->api->buildPayloadFromShipment($shipment);
            $payload['recipient'] = ['uuid' => $clientResult['uuid']];
            $result = $this->api->createShipment($payload);

            if ($result['success']) {
                $barcode = $result['barcode'] ?? '';
                $uuid = $result['uuid'];

                $shipment->update([
                    'ttn' => $barcode ?: null,
                    'up_status_text' => 'Створено через eCom',
                    'up_status_code' => '01',
                    'status' => UpShipment::STATUS_NEW,
                ]);

                if ($shipment->order) {
                    app(\App\Services\Warehouse\OrderFulfillmentService::class)
                        ->shipOrder($shipment->order);
                }

                return [
                    'success' => true,
                    'ttn' => $barcode,
                    'uuid' => $uuid,
                    'errors' => [],
                ];
            }

            Log::error('UP create TTN failed', ['errors' => $result['errors'], 'shipment_id' => $shipment->id]);

            return [
                'success' => false,
                'ttn' => null,
                'uuid' => null,
                'errors' => $result['errors'],
            ];
        } catch (\Throwable $e) {
            Log::error('UP create TTN exception: '.$e->getMessage(), ['shipment_id' => $shipment->id]);

            return [
                'success' => false,
                'ttn' => null,
                'uuid' => null,
                'errors' => [$e->getMessage()],
            ];
        }
    }
}
