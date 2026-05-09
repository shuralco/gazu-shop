<?php

namespace App\Livewire\Tracking;

use App\Models\NpShipment;
use App\Services\NovaPoshtaApiService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class TrackingComponent extends Component
{
    public string $ttn = '';
    public ?array $statuses = null;
    public ?NpShipment $shipment = null;
    public ?string $error = null;

    public function mount(?string $ttn = null): void
    {
        if ($ttn) {
            $this->ttn = $ttn;
            $this->track();
        }
    }

    public function track(): void
    {
        $this->error = null;
        $this->statuses = null;

        $ttn = trim($this->ttn);
        if (! preg_match('/^\d{10,20}$/', $ttn)) {
            $this->error = __('general.tracking_invalid_ttn');
            return;
        }

        // Try local first
        $this->shipment = NpShipment::where('ttn', $ttn)->first();

        // Get full status history from NP API
        try {
            $api = app(NovaPoshtaApiService::class);
            $resp = $api->getShipmentStatusHistory($ttn);

            if (! empty($resp['success']) && ! empty($resp['data'])) {
                $row = $resp['data'][0];
                $this->statuses = [
                    'status' => $row['Status'] ?? '',
                    'status_code' => $row['StatusCode'] ?? null,
                    'sender_city' => $row['CitySender'] ?? '',
                    'recipient_city' => $row['CityRecipient'] ?? '',
                    'recipient_warehouse' => $row['WarehouseRecipient'] ?? '',
                    'estimated' => $row['ScheduledDeliveryDate'] ?? null,
                    'actual_delivery' => $row['ActualDeliveryDate'] ?? null,
                    'date_scan' => $row['DateScan'] ?? null,
                    'date_received' => $row['DateReceived'] ?? null,
                    'document_weight' => $row['DocumentWeight'] ?? 0,
                    'document_cost' => $row['DocumentCost'] ?? 0,
                    'announced_price' => $row['AnnouncedPrice'] ?? 0,
                    'check_weight' => $row['CheckWeight'] ?? 0,
                    'cargo_description' => $row['CargoDescriptionString'] ?? '',
                ];
            } else {
                $this->error = __('general.tracking_not_found');
            }
        } catch (\Throwable $e) {
            $this->error = __('general.tracking_api_error');
        }
    }

    public function render()
    {
        return view('livewire.tracking.tracking-component', [
            'title' => __('general.tracking_title'),
        ]);
    }
}
