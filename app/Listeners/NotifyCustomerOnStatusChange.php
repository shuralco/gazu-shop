<?php

namespace App\Listeners;

use App\Events\NpShipmentStatusChanged;
use App\Mail\NpStatusChangedMail;
use App\Models\NpShipment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotifyCustomerOnStatusChange implements ShouldQueue
{
    public function handle(NpShipmentStatusChanged $event): void
    {
        $shipment = $event->shipment;
        $order = $shipment->order;

        if (! $order) {
            return;
        }

        $statusCode = $shipment->np_status_code;
        $template = $this->resolveTemplate($event->newStatus, $statusCode);

        if (! $template) {
            return;
        }

        // 1. Email to customer
        if (! empty($order->email)) {
            try {
                Mail::to($order->email)->send(new NpStatusChangedMail($shipment, $template));
                Log::info("NP status email sent: order #{$order->id}, template={$template}");
            } catch (\Throwable $e) {
                Log::error("Failed to send NP status email: {$e->getMessage()}");
            }
        }

        // 2. Telegram to admin (if configured)
        try {
            $tg = app(\App\Services\TelegramService::class);
            if ($tg->isConfigured()) {
                $emoji = ['shipped' => '📦', 'in_warehouse' => '🏪', 'delivered' => '✅', 'returned' => '↩️'];
                $titles = [
                    'shipped' => 'Відправлено',
                    'in_warehouse' => 'У відділенні',
                    'delivered' => 'Отримано',
                    'returned' => 'Повернуто',
                ];
                $msg = sprintf(
                    "%s <b>%s</b>\n\n📦 Замовлення #%s\n👤 %s %s\n📞 %s\n🚚 ТТН: <code>%s</code>\n📍 %s",
                    $emoji[$template] ?? '📨',
                    $titles[$template] ?? 'Зміна статусу',
                    $order->id,
                    $order->first_name ?? '',
                    $order->last_name ?? '',
                    $order->phone ?? '—',
                    $shipment->ttn ?? '—',
                    $shipment->np_status ?? '—',
                );
                $tg->send($msg);
            }
        } catch (\Throwable $e) {
            Log::error("Telegram NP status notify failed: {$e->getMessage()}");
        }
    }

    private function resolveTemplate(string $newStatus, ?string $statusCode): ?string
    {
        // Sent (in transit)
        if ($newStatus === NpShipment::STATUS_SENT) {
            // Code 7-8 = "Прибув у відділення"
            if (in_array($statusCode, ['7', '8'])) {
                return 'in_warehouse';
            }
            return 'shipped';
        }

        if ($newStatus === NpShipment::STATUS_DELIVERED) {
            return 'delivered';
        }

        if ($newStatus === NpShipment::STATUS_RETURNED) {
            return 'returned';
        }

        return null;
    }
}
