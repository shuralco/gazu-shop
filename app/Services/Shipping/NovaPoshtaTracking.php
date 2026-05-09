<?php

namespace App\Services\Shipping;

use App\Models\NpShipment;
use App\Services\NovaPoshtaApiService;
use Illuminate\Support\Facades\Log;

/**
 * Bulk-tracker for all active shipments. Call NP API in batches of 100,
 * detect status changes, dispatch events for email/SMS notifications.
 */
class NovaPoshtaTracking
{
    public function __construct(private NovaPoshtaApiService $api)
    {
    }

    /**
     * Track all shipments that need updating.
     * Returns count of (total, updated, status_changed).
     */
    public function trackAll(): array
    {
        $shipments = NpShipment::query()
            ->needsTracking()
            ->whereNotNull('ttn')
            ->get();

        if ($shipments->isEmpty()) {
            return ['total' => 0, 'updated' => 0, 'status_changed' => 0];
        }

        $statusChanged = 0;
        $updated = 0;

        // NP allows up to 100 documents per call
        foreach ($shipments->chunk(100) as $chunk) {
            $documents = $chunk->map(fn (NpShipment $s) => [
                'DocumentNumber' => $s->ttn,
                'Phone' => '',
            ])->values()->toArray();

            try {
                $result = $this->api->trackDocuments($documents);
            } catch (\Throwable $e) {
                Log::error('NP bulk-track failed: ' . $e->getMessage());
                continue;
            }

            if (empty($result['success']) || empty($result['data'])) {
                continue;
            }

            // Map TTN → response row
            $byTtn = collect($result['data'])->keyBy('Number');

            foreach ($chunk as $shipment) {
                $row = $byTtn->get($shipment->ttn);
                if (! $row) {
                    continue;
                }

                $oldStatus = $shipment->status;
                $statusCode = $row['StatusCode'] ?? null;
                $newStatus = NpShipment::resolveStatusFromCode($statusCode);

                $history = $shipment->tracking_history ?? [];
                $lastEntry = end($history);
                // Only append if status text changed
                if (! $lastEntry || ($lastEntry['status'] ?? '') !== ($row['Status'] ?? '')) {
                    $history[] = [
                        'status' => $row['Status'] ?? '',
                        'status_code' => $statusCode,
                        'date' => now()->toDateTimeString(),
                        'city' => $row['CityRecipient'] ?? '',
                        'warehouse' => $row['WarehouseRecipient'] ?? '',
                    ];
                }

                $shipment->update([
                    'status' => $newStatus,
                    'np_status' => $row['Status'] ?? $shipment->np_status,
                    'np_status_code' => $statusCode,
                    'estimated_delivery_date' => $row['ScheduledDeliveryDate'] ?? $shipment->estimated_delivery_date,
                    'actual_shipping_date' => $row['DateScan'] ?? $shipment->actual_shipping_date,
                    'recipient_date' => ! empty($row['DateReceived']) ? $row['DateReceived'] : $shipment->recipient_date,
                    'tracking_history' => $history,
                    'last_tracked_at' => now(),
                ]);

                $updated++;
                if ($oldStatus !== $newStatus) {
                    $statusChanged++;
                    event(new \App\Events\NpShipmentStatusChanged($shipment->fresh(), $oldStatus, $newStatus));
                }
            }
        }

        return [
            'total' => $shipments->count(),
            'updated' => $updated,
            'status_changed' => $statusChanged,
        ];
    }
}
