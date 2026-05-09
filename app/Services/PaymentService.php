<?php

namespace App\Services;

use App\DTOs\PaymentResponse;
use App\DTOs\WebhookResponse;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentGatewaySettings;
use App\Models\PaymentLog;
use App\Services\Gateways\LiqPayGateway;
use App\Services\Gateways\MonobankGateway;
use App\Services\Gateways\WayForPayGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class PaymentService
{
    private array $gateways;

    public function __construct(
        private LiqPayGateway $liqpay,
        private WayForPayGateway $wayforpay,
        private MonobankGateway $monobank,
    ) {
        $this->gateways = [
            'liqpay' => $this->liqpay,
            'wayforpay' => $this->wayforpay,
            'monobank' => $this->monobank,
        ];
    }

    /**
     * Handle incoming webhook request: rate limit, process, and log.
     */
    public function handleWebhook(string $gateway, Request $request): WebhookResponse
    {
        $key = "{$gateway}-webhook:{$request->ip()}";

        if (RateLimiter::tooManyAttempts($key, 100)) {
            Log::warning("{$gateway} webhook rate limit exceeded", ['ip' => $request->ip()]);
            throw new \RuntimeException('Too many requests', 429);
        }

        RateLimiter::hit($key, 60);

        $webhookResponse = $this->processWebhook($gateway, $request);

        Log::info("{$gateway} webhook processed successfully", [
            'order_id' => $webhookResponse->order_id,
            'status' => $webhookResponse->status,
            'amount' => $webhookResponse->amount ?? null,
        ]);

        return $webhookResponse;
    }

    /**
     * Перевірити статус платежу
     */
    public function verifyPayment(string $gateway, string $paymentId)
    {
        if (! isset($this->gateways[$gateway])) {
            throw new \Exception("Unsupported gateway: {$gateway}");
        }

        return $this->gateways[$gateway]->verifyPayment($paymentId);
    }

    /**
     * Повернути кошти
     */
    public function refundPayment(string $gateway, string $paymentId, float $amount)
    {
        if (! isset($this->gateways[$gateway])) {
            throw new \Exception("Unsupported gateway: {$gateway}");
        }

        return $this->gateways[$gateway]->refundPayment($paymentId, $amount);
    }

    public function getAvailableGateways(Order $order): array
    {
        $available = [];

        // Отримати налаштування з БД
        $gatewaySettings = PaymentGatewaySettings::where('is_active', true)->get()->keyBy('code');

        foreach ($this->gateways as $key => $gateway) {
            $settings = $gatewaySettings->get($key);

            // Пропустити якщо шлюз відключений в налаштуваннях
            if (! $settings || ! $settings->is_active) {
                continue;
            }

            // Перевірити чи підтримується замовлення з урахуванням налаштувань
            if ($order->total < $settings->min_amount || $order->total > $settings->max_amount) {
                continue;
            }

            if ($gateway->supportsOrder($order)) {
                $available[$key] = [
                    'key' => $key,
                    'name' => $settings->name ?? $gateway->getDisplayName(),
                    'icon' => "/images/gateways/{$key}-logo.svg",
                    'description' => $settings->description ?? $gateway->getDescription(),
                    'fee' => $settings->fee_percentage > 0 ? ($order->total * $settings->fee_percentage / 100) : $gateway->calculateFee($order->total),
                    'processing_time' => $gateway->getProcessingTime(),
                    'features' => $gateway->getSupportedFeatures(),
                    'min_amount' => $settings->min_amount,
                    'max_amount' => $settings->max_amount,
                    'settings' => $settings,
                ];
            }
        }

        return $available;
    }

    public function createPayment(Order $order, string $gateway, array $options = []): PaymentResponse
    {
        if (! isset($this->gateways[$gateway])) {
            throw new \Exception("Unsupported gateway: {$gateway}");
        }

        if (! $this->gateways[$gateway]->supportsOrder($order)) {
            throw new \Exception("Gateway {$gateway} does not support this order");
        }

        return DB::transaction(function () use ($order, $gateway, $options) {
            $payment = Payment::create([
                'id' => Str::uuid(),
                'order_id' => $order->id,
                'gateway' => $gateway,
                'status' => 'pending',
                'amount' => $order->total,
                'currency' => 'UAH',
            ]);

            try {
                $response = $this->gateways[$gateway]->createPayment($order, $options);

                if ($response->status !== 'error') {
                    $payment->update([
                        'status' => 'processing',
                    ]);
                } else {
                    $payment->update(['status' => 'failed']);
                }

                $this->logPaymentAction($payment, 'create_payment', [], ['response' => [
                    'status' => $response->status,
                    'gateway' => $response->gateway,
                    'external_id' => $response->external_id,
                ]]);

                return $response;

            } catch (\Exception $e) {
                $payment->update(['status' => 'failed']);
                $this->logPaymentAction($payment, 'create_payment_error', [], ['error' => $e->getMessage()]);
                throw $e;
            }
        });
    }

    public function processWebhook(string $gateway, Request $request): WebhookResponse
    {
        if (! isset($this->gateways[$gateway])) {
            throw new \Exception("Unsupported gateway: {$gateway}");
        }

        $webhookResponse = $this->gateways[$gateway]->handleWebhook($request);

        $order = Order::findOrFail($webhookResponse->order_id);
        $payment = $order->payments()->where('gateway', $gateway)->latest()->first();

        if (! $payment) {
            Log::warning("{$gateway} webhook: Payment not found", [
                'order_id' => $webhookResponse->order_id,
            ]);
            throw new \Exception('Payment not found');
        }

        $payment->update([
            'status' => $webhookResponse->status,
            'external_id' => $webhookResponse->external_id ?? $payment->external_id,
            'webhook_received_at' => now(),
            'processed_at' => $webhookResponse->status === 'success' ? now() : null,
            'metadata' => array_merge(
                $payment->metadata ?? [],
                ['webhook_data' => $webhookResponse->raw_data]
            ),
        ]);

        $this->logPaymentAction(
            $payment,
            'webhook_received',
            $request->except(['password', 'token', 'secret', 'signature', 'private_key']),
            $webhookResponse->raw_data,
        );

        if ($webhookResponse->status === 'success') {
            $this->handleSuccessfulPayment($order, $payment);
        } elseif ($webhookResponse->status === 'failed') {
            $this->handleFailedPayment($order, $payment);
        }

        return $webhookResponse;
    }

    private function handleSuccessfulPayment(Order $order, Payment $payment): void
    {
        DB::transaction(function () use ($order, $payment) {
            $order->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            // Тут можна додати логіку списання товарів з inventory
            // або інші дії при успішній оплаті

            Log::info('Payment successful', [
                'order_id' => $order->id,
                'payment_id' => $payment->id,
                'amount' => $payment->amount,
            ]);
        });
    }

    private function handleFailedPayment(Order $order, Payment $payment): void
    {
        $order->update(['status' => 'payment_failed']);

        Log::warning('Payment failed', [
            'order_id' => $order->id,
            'payment_id' => $payment->id,
            'gateway' => $payment->gateway,
        ]);
    }

    private function logPaymentAction(Payment $payment, string $action, array $requestData = [], array $responseData = []): void
    {
        PaymentLog::create([
            'payment_id' => $payment->id,
            'action' => $action,
            'request_data' => $requestData,
            'response_data' => $responseData,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
