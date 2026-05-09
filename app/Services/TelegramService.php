<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Services\Integrations\IntegrationManager;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    private string $botToken;
    private string $chatId;

    public function __construct()
    {
        $integration = app(IntegrationManager::class)->get('telegram');
        $config = $integration?->getConfig() ?? [];

        $this->botToken = $config['bot_token']
            ?? config('services.telegram.bot_token', '');
        $this->chatId = $config['chat_id']
            ?? config('services.telegram.chat_id', '');
    }

    /**
     * Send a message to a specific chat via Telegram Bot API.
     */
    public function sendMessage(string $chatId, string $text, array $options = []): bool
    {
        if (! $this->botToken) {
            Log::warning('Telegram: bot token not configured');
            return false;
        }

        if (! $chatId) {
            Log::warning('Telegram: chat_id is empty');
            return false;
        }

        try {
            $payload = array_merge([
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML',
            ], $options);

            $response = Http::connectTimeout(5)
                ->timeout(10)
                ->post("https://api.telegram.org/bot{$this->botToken}/sendMessage", $payload);

            if (! $response->successful()) {
                Log::error('Telegram API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'chat_id' => $chatId,
                ]);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('Telegram send error', [
                'error' => $e->getMessage(),
                'chat_id' => $chatId,
            ]);
            return false;
        }
    }

    /**
     * Send a message to the default configured chat.
     */
    public function send(string $text, array $options = []): bool
    {
        return $this->sendMessage($this->chatId, $text, $options);
    }

    /**
     * Send a rich order notification with full details.
     */
    public function sendOrderNotification(Order $order): bool
    {
        $order->loadMissing('orderProducts');

        $customerName = trim(($order->first_name ?? '') . ' ' . ($order->last_name ?? ''));
        if (! $customerName) {
            $customerName = $order->name ?? 'Невідомий';
        }

        $lines = [
            "🛒 <b>Нове замовлення #{$order->id}</b>",
            '',
            "👤 Клієнт: {$customerName}",
        ];

        if ($order->phone) {
            $lines[] = "📱 Телефон: {$order->phone}";
        }

        if ($order->email) {
            $lines[] = "📧 Email: {$order->email}";
        }

        $lines[] = '';
        $lines[] = '📦 Товари:';

        foreach ($order->orderProducts as $item) {
            $itemTotal = number_format($item->price * $item->quantity, 0, ',', ' ');
            $lines[] = "• {$item->title} ({$item->quantity} шт.) — {$itemTotal} ₴";
        }

        $lines[] = '';
        $lines[] = '💰 Разом: ' . number_format($order->total, 0, ',', ' ') . ' ₴';

        if ($order->discount_amount > 0) {
            $lines[] = '🏷 Знижка: -' . number_format($order->discount_amount, 0, ',', ' ') . ' ₴';
        }

        if ($order->shipping_provider) {
            $lines[] = "🚚 Доставка: {$order->shipping_provider}";
        }

        if ($order->payment_method) {
            $paymentLabels = [
                'liqpay' => 'LiqPay',
                'wayforpay' => 'WayForPay',
                'monobank' => 'Monobank',
                'cod' => 'Накладний платіж',
                'cash' => 'Готівка',
                'bank_transfer' => 'Банківський переказ',
            ];
            $paymentLabel = $paymentLabels[$order->payment_method] ?? $order->payment_method;
            $lines[] = "💳 Оплата: {$paymentLabel}";
        }

        $address = $this->buildAddressLine($order);
        if ($address) {
            $lines[] = "📍 Адреса: {$address}";
        }

        if ($order->note) {
            $lines[] = '';
            $lines[] = "💬 Коментар: {$order->note}";
        }

        return $this->send(implode("\n", $lines));
    }

    /**
     * Send a low stock alert for a product.
     */
    public function sendLowStockAlert(Product $product): bool
    {
        $title = $product->getTranslation('title', 'uk', false)
            ?? $product->getTranslation('title', 'en', false)
            ?? $product->name
            ?? "ID: {$product->id}";

        $threshold = $product->min_quantity ?? 5;

        $lines = [
            '⚠️ <b>Низький залишок</b>',
            '',
            "📦 Товар: {$title}",
            "🔢 Залишок: {$product->quantity} шт.",
            "📊 Поріг: {$threshold} шт.",
        ];

        if ($product->sku) {
            $lines[] = "🏷 SKU: {$product->sku}";
        }

        return $this->send(implode("\n", $lines));
    }

    /**
     * Notify about a new order (called from OrderObserver).
     */
    public function notifyNewOrder(Order $order): bool
    {
        return $this->sendOrderNotification($order);
    }

    /**
     * Notify about order status change (called from OrderObserver).
     */
    public function notifyOrderStatusChanged(Order $order): bool
    {
        $statuses = [
            'pending' => '🆕 Нове',
            'processing' => '⚙️ В обробці',
            'shipped' => '🚚 Відправлено',
            'delivered' => '✅ Доставлено',
            'cancelled' => '❌ Скасовано',
            'refunded' => '↩️ Повернення',
        ];

        $statusLabel = $statuses[$order->status] ?? $order->status;
        $customerName = trim(($order->first_name ?? '') . ' ' . ($order->last_name ?? ''));

        $lines = [
            "📋 <b>Замовлення #{$order->id}</b> — {$statusLabel}",
        ];

        if ($customerName) {
            $lines[] = "👤 {$customerName}";
        }

        $lines[] = '💰 ' . number_format($order->total, 0, ',', ' ') . ' ₴';

        if ($order->status === 'shipped' && $order->getTrackingNumber()) {
            $lines[] = "📦 ТТН: {$order->getTrackingNumber()}";
        }

        return $this->send(implode("\n", $lines));
    }

    /**
     * Build a human-readable address line from order data.
     */
    private function buildAddressLine(Order $order): string
    {
        $parts = [];

        if ($order->shipping_city) {
            $parts[] = $order->shipping_city;
        }

        if ($order->shipping_warehouse) {
            $parts[] = $order->shipping_warehouse;
        } elseif ($order->shipping_post_office) {
            $parts[] = $order->shipping_post_office;
        } elseif ($order->shipping_address) {
            $parts[] = $order->shipping_address;
        }

        return implode(', ', $parts);
    }

    /**
     * Check if Telegram integration is configured and enabled.
     */
    public function isConfigured(): bool
    {
        return ! empty($this->botToken) && ! empty($this->chatId);
    }
}
