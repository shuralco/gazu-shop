<?php

namespace App\Console\Commands;

use App\Models\NpShipment;
use App\Services\NovaPoshtaApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class NovaPoshtaTrack extends Command
{
    protected $signature = 'np:track
        {--ttn= : Номер ТТН для відстеження конкретного відправлення}';

    protected $description = 'Відстеження статусів відправлень Нової Пошти';

    public function handle(NovaPoshtaApiService $api): int
    {
        $specificTtn = $this->option('ttn');

        if ($specificTtn) {
            $shipments = NpShipment::where('ttn', $specificTtn)->get();

            if ($shipments->isEmpty()) {
                $this->error("Відправлення з ТТН {$specificTtn} не знайдено в базі.");

                return self::FAILURE;
            }
        } else {
            $shipments = NpShipment::needsTracking()->get();
        }

        if ($shipments->isEmpty()) {
            $this->info('Немає відправлень для відстеження.');

            return self::SUCCESS;
        }

        $this->info("Відстеження {$shipments->count()} відправлень...");
        $bar = $this->output->createProgressBar($shipments->count());
        $bar->start();

        $updated = 0;
        $errors = 0;

        foreach ($shipments as $shipment) {
            try {
                $changed = $this->trackShipment($api, $shipment);
                if ($changed) {
                    $updated++;
                }
            } catch (\Exception $e) {
                $errors++;
                Log::error('NP Track error', [
                    'ttn' => $shipment->ttn,
                    'error' => $e->getMessage(),
                ]);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Оновлено: {$updated}, Помилок: {$errors}");

        return self::SUCCESS;
    }

    /**
     * Відстежити конкретне відправлення
     */
    private function trackShipment(NovaPoshtaApiService $api, NpShipment $shipment): bool
    {
        $response = $api->getShipmentTrackingInfo($shipment->ttn);

        if (! ($response['success'] ?? false) || empty($response['data'])) {
            return false;
        }

        $tracking = $response['data'][0];
        $newNpStatusCode = $tracking['StatusCode'] ?? null;
        $newNpStatus = $tracking['Status'] ?? null;
        $newStatus = NpShipment::resolveStatusFromCode($newNpStatusCode);

        $oldStatus = $shipment->status;
        $statusChanged = $oldStatus !== $newStatus;

        // Оновлюємо історію відстеження
        $history = $shipment->tracking_history ?? [];
        $history[] = [
            'status' => $newNpStatus,
            'status_code' => $newNpStatusCode,
            'city_recipient' => $tracking['CityRecipient'] ?? '',
            'warehouse_recipient' => $tracking['WarehouseRecipient'] ?? '',
            'scheduled_delivery_date' => $tracking['ScheduledDeliveryDate'] ?? null,
            'actual_delivery_date' => $tracking['ActualDeliveryDate'] ?? null,
            'checked_at' => now()->toISOString(),
        ];

        $shipment->update([
            'status' => $newStatus,
            'np_status' => $newNpStatus,
            'np_status_code' => $newNpStatusCode,
            'shipping_cost' => $tracking['DocumentCost'] ?? $shipment->shipping_cost,
            'estimated_delivery_date' => $tracking['ScheduledDeliveryDate'] ?? $shipment->estimated_delivery_date,
            'tracking_history' => $history,
            'last_tracked_at' => now(),
        ]);

        // Якщо статус змінився — оновимо статус замовлення
        if ($statusChanged) {
            $this->updateOrderStatus($shipment, $newStatus);
        }

        return $statusChanged;
    }

    /**
     * Оновити статус замовлення на основі статусу відправлення
     */
    private function updateOrderStatus(NpShipment $shipment, string $newStatus): void
    {
        $order = $shipment->order;

        if (! $order) {
            return;
        }

        $orderStatusMap = [
            NpShipment::STATUS_SENT => 'shipped',
            NpShipment::STATUS_DELIVERED => 'delivered',
            NpShipment::STATUS_RETURNED => 'returned',
        ];

        $newOrderStatus = $orderStatusMap[$newStatus] ?? null;

        if ($newOrderStatus && $order->status !== $newOrderStatus) {
            $order->update(['status' => $newOrderStatus]);

            Log::info('Order status updated by NP tracking', [
                'order_id' => $order->id,
                'ttn' => $shipment->ttn,
                'old_status' => $order->status,
                'new_status' => $newOrderStatus,
            ]);
        }
    }
}
