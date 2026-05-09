<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\Integrations\IntegrationManager;
use App\Services\TelegramService;
use Illuminate\Support\Facades\Log;

class OrderNotificationObserver
{
    /**
     * Handle the Order "created" event.
     * Sends Telegram notification if integration is enabled.
     */
    public function created(Order $order): void
    {
        if (! $this->isTelegramEnabled()) {
            return;
        }

        $config = $this->getTelegramConfig();
        if (empty($config['notify_new_orders'] ?? true)) {
            return;
        }

        try {
            app(TelegramService::class)->sendOrderNotification($order);
        } catch (\Throwable $e) {
            Log::error('Telegram order notification failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle the Order "updated" event.
     * Sends Telegram notification on status change if enabled.
     */
    public function updated(Order $order): void
    {
        if (! $order->isDirty('status')) {
            return;
        }

        if (! $this->isTelegramEnabled()) {
            return;
        }

        $config = $this->getTelegramConfig();
        if (empty($config['notify_status_changes'] ?? false)) {
            return;
        }

        try {
            app(TelegramService::class)->notifyOrderStatusChanged($order);
        } catch (\Throwable $e) {
            Log::error('Telegram status notification failed', [
                'order_id' => $order->id,
                'status' => $order->status,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function isTelegramEnabled(): bool
    {
        $telegram = app(IntegrationManager::class)->get('telegram');

        return $telegram && $telegram->isEnabled();
    }

    private function getTelegramConfig(): array
    {
        $telegram = app(IntegrationManager::class)->get('telegram');

        return $telegram?->getConfig() ?? [];
    }
}
