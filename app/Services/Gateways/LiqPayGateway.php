<?php

namespace App\Services\Gateways;

use App\Contracts\PaymentGatewayInterface;
use App\DTOs\PaymentResponse;
use App\DTOs\PaymentStatus;
use App\DTOs\RefundResponse;
use App\DTOs\WebhookResponse;
use App\Models\Order;
use App\Models\PaymentGatewaySettings;
use Illuminate\Http\Request;
use LiqPay;

class LiqPayGateway implements PaymentGatewayInterface
{
    private LiqPay $liqpay;

    private bool $sandboxMode;

    public function __construct()
    {
        $settings = $this->getSettings();

        $this->liqpay = new LiqPay(
            $settings['public_key'] ?? config('liqpay.public_key'),
            $settings['private_key'] ?? config('liqpay.private_key')
        );
        $this->sandboxMode = $settings['sandbox'] ?? config('liqpay.sandbox_mode');
    }

    private function getSettings(): array
    {
        $gatewaySettings = PaymentGatewaySettings::where('code', 'liqpay')->first();

        return $gatewaySettings ? ($gatewaySettings->configuration ?? []) : [];
    }

    public function createPayment(Order $order, array $options = []): PaymentResponse
    {
        $paymentData = [
            'version' => 3,
            'action' => 'pay',
            'amount' => $order->total,
            'currency' => 'UAH',
            'description' => "Замовлення #{$order->id} у SimpleShop",
            'order_id' => $order->id,
            'language' => 'uk',
            'result_url' => route('gazu.checkout.success', ['order' => $order->id]),
            'server_url' => route('webhooks.liqpay'),
        ];

        if ($order->user && $order->user->email) {
            $paymentData['sender_phone'] = $order->user->phone ?? '';
        }

        if (isset($options['recurring']) && $options['recurring']) {
            $paymentData['recurringbytoken'] = '1';
            $paymentData['customer_user_id'] = $order->user_id;
        }

        // Генеруємо дані та підпис для форми
        $data = base64_encode(json_encode($paymentData));
        $signature = $this->liqpay->cnb_signature($paymentData);

        // Формуємо URL для checkout
        $paymentUrl = 'https://www.liqpay.ua/api/3/checkout';

        // Отримуємо public_key з налаштувань
        $settings = $this->getSettings();
        $publicKey = $settings['public_key'] ?? config('liqpay.public_key');

        $formData = [
            'data' => $data,
            'signature' => $signature,
            'public_key' => $publicKey,  // Додаємо public_key до форми
        ];

        return new PaymentResponse([
            'status' => 'form_redirect',
            'form_data' => $formData,
            'form_action' => $paymentUrl,
            'external_id' => (string) $order->id,
            'gateway' => 'liqpay',
            'metadata' => [
                'order_id' => $order->id,
                'amount' => $order->total,
                'currency' => $paymentData['currency'],
            ],
        ]);
    }

    public function verifyPayment(string $paymentId): PaymentStatus
    {
        $statusData = $this->liqpay->api([
            'action' => 'status',
            'version' => 3,
            'order_id' => $paymentId,
        ]);

        return new PaymentStatus([
            'status' => $this->mapLiqPayStatus($statusData['status'] ?? 'pending'),
            'external_id' => $statusData['payment_id'] ?? null,
            'amount' => (float) ($statusData['amount'] ?? 0),
            'currency' => $statusData['currency'] ?? 'UAH',
            'raw_data' => $statusData,
        ]);
    }

    public function refundPayment(string $paymentId, float $amount): RefundResponse
    {
        $refundData = $this->liqpay->api([
            'action' => 'refund',
            'version' => 3,
            'order_id' => $paymentId,
            'amount' => $amount,
        ]);

        return new RefundResponse([
            'success' => ($refundData['status'] ?? '') === 'reversed',
            'refund_id' => $refundData['refund_id'] ?? null,
            'amount' => $amount,
            'raw_data' => $refundData,
        ]);
    }

    public function handleWebhook(Request $request): WebhookResponse
    {
        $data = $request->get('data');
        $signature = $request->get('signature');

        if (! $this->verifyWebhookSignature($data, $signature)) {
            throw new \Exception('Invalid webhook signature');
        }

        $webhookData = json_decode(base64_decode($data), true);

        return new WebhookResponse(
            success: true,
            order_id: $webhookData['order_id'],
            payment_id: null,
            status: $this->mapLiqPayStatus($webhookData['status']),
            amount: (float) $webhookData['amount'],
            message: 'Webhook оброблено успішно'
        );
    }

    public function getSupportedCurrencies(): array
    {
        return ['UAH', 'USD', 'EUR'];
    }

    public function isTestMode(): bool
    {
        return $this->sandboxMode;
    }

    public function getDisplayName(): string
    {
        return 'LiqPay';
    }

    public function getDescription(): string
    {
        return 'Оплата банківською карткою через ПриватБанк';
    }

    public function calculateFee(float $amount): float
    {
        $gatewaySettings = PaymentGatewaySettings::where('code', 'liqpay')->first();
        $feePercentage = $gatewaySettings ? $gatewaySettings->fee_percentage : 2.5;

        return $amount * ($feePercentage / 100);
    }

    public function getProcessingTime(): string
    {
        return 'Миттєво';
    }

    public function getSupportedFeatures(): array
    {
        return ['refund', 'recurring'];
    }

    public function getMinAmount(): float
    {
        $gatewaySettings = PaymentGatewaySettings::where('code', 'liqpay')->first();

        return $gatewaySettings ? $gatewaySettings->min_amount : 0.01;
    }

    public function getMaxAmount(): float
    {
        $gatewaySettings = PaymentGatewaySettings::where('code', 'liqpay')->first();

        return $gatewaySettings ? $gatewaySettings->max_amount : 50000.0;
    }

    public function supportsOrder(Order $order): bool
    {
        return $order->total >= $this->getMinAmount() &&
               $order->total <= $this->getMaxAmount();
    }

    private function mapLiqPayStatus(string $liqpayStatus): string
    {
        return match ($liqpayStatus) {
            'success' => 'success',
            'failure', 'error' => 'failed',
            'processing', 'wait_secure' => 'processing',
            'reversed' => 'reversed',
            default => 'pending'
        };
    }

    private function verifyWebhookSignature(string $data, string $signature): bool
    {
        $privateKey = config('liqpay.private_key');
        $expectedSignature = base64_encode(sha1($privateKey.$data.$privateKey, true));

        return hash_equals($expectedSignature, $signature);
    }
}
