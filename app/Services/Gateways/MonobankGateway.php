<?php

namespace App\Services\Gateways;

use App\Contracts\PaymentGatewayInterface;
use App\DTOs\PaymentResponse;
use App\DTOs\PaymentStatus;
use App\DTOs\RefundResponse;
use App\DTOs\WebhookResponse;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentGatewaySettings;
use App\Models\PaymentLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MonobankGateway implements PaymentGatewayInterface
{
    private ?string $merchantId;

    private ?string $apiToken;

    private string $webHookUrl;

    private bool $sandboxMode;

    private string $apiUrl;

    public function __construct()
    {
        $settings = $this->getSettings();

        $this->merchantId = $settings['merchant_id'] ?? config('services.monobank.merchant_id') ?? null;
        $this->apiToken = $settings['api_token'] ?? config('services.monobank.api_token') ?? null;
        $this->webHookUrl = route('webhooks.monobank');
        $this->sandboxMode = $settings['sandbox'] ?? config('services.monobank.sandbox', false);
        $this->apiUrl = $this->sandboxMode
            ? 'https://api.monobank.ua/api/merchant/test'
            : 'https://api.monobank.ua/api/merchant';
    }

    private function getSettings(): array
    {
        $gatewaySettings = PaymentGatewaySettings::where('code', 'monobank')->first();

        return $gatewaySettings ? ($gatewaySettings->configuration ?? []) : [];
    }

    /**
     * Створити платіж
     */
    public function createPayment(Order $order, array $options = []): PaymentResponse
    {
        try {
            // Створити запис платежу в базі
            $payment = Payment::where('order_id', $order->id)
                ->where('gateway', 'monobank')
                ->where('status', 'pending')
                ->first();

            if (! $payment) {
                $payment = Payment::create([
                    'id' => Str::uuid(),
                    'order_id' => $order->id,
                    'gateway' => 'monobank',
                    'amount' => $order->total,
                    'currency' => 'UAH',
                    'status' => 'pending',
                    'metadata' => $options,
                ]);
            }

            // Підготувати дані для створення інвойсу
            $invoiceData = [
                'merchantPaymInfo' => [
                    'reference' => $payment->id,
                    'destination' => "Оплата замовлення #{$order->id}",
                    'comment' => $order->note ?? '',
                    'customerEmails' => [$order->email],
                ],
                'amount' => (int) round($order->total * 100), // сума в копійках
                'ccy' => 980, // код валюти UAH
                'redirectUrl' => route('orders.success', $order),
                'webHookUrl' => $this->webHookUrl,
                'validity' => 3600, // термін дії посилання в секундах (1 година)
                'paymentType' => 'debit', // тип операції
                'qrId' => null, // ID QR-каси
                'saveCardData' => null, // зберегти дані картки
            ];

            // Додати товари якщо є
            if ($order->orderProducts->count() > 0) {
                $basketOrder = [];
                foreach ($order->orderProducts as $orderProduct) {
                    $basketOrder[] = [
                        'name' => $orderProduct->title,
                        'qty' => $orderProduct->quantity * 1000, // кількість в тисячних
                        'sum' => (int) round($orderProduct->price * $orderProduct->quantity * 100), // сума в копійках
                        'icon' => null,
                        'unit' => 'шт.',
                        'code' => (string) $orderProduct->product_id,
                        'barcode' => null,
                        'header' => null,
                        'footer' => null,
                        'tax' => null,
                        'uktzed' => null,
                    ];
                }
                $invoiceData['merchantPaymInfo']['basketOrder'] = $basketOrder;
            }

            // Відправити запит на створення інвойсу
            $response = Http::connectTimeout(5)
                ->timeout(30)
                ->retry(3, 100, throw: false)
                ->withHeaders([
                    'X-Token' => $this->apiToken,
                    'Content-Type' => 'application/json',
                ])->post("{$this->apiUrl}/invoice/create", $invoiceData);

            if ($response->successful()) {
                $responseData = $response->json();

                // Зберегти invoice ID
                $payment->update([
                    'external_id' => $responseData['invoiceId'],
                    'metadata' => array_merge(
                        $payment->metadata ?? [],
                        ['monobank_invoice' => $responseData]
                    ),
                ]);

                // Логування
                PaymentLog::create([
                    'payment_id' => $payment->id,
                    'type' => 'request',
                    'data' => [
                        'request' => $invoiceData,
                        'response' => $responseData,
                    ],
                    'status' => 'success',
                ]);

                return new PaymentResponse([
                    'status' => 'redirect',
                    'external_id' => $payment->id,
                    'redirect_url' => $responseData['pageUrl'],
                    'gateway' => 'monobank',
                    'metadata' => [
                        'order_id' => $order->id,
                        'amount' => $order->total,
                    ],
                ]);
            } else {
                throw new \Exception($response->json()['errText'] ?? 'Помилка створення інвойсу');
            }

        } catch (\Exception $e) {
            Log::error('Monobank payment creation failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            if (isset($payment)) {
                PaymentLog::create([
                    'payment_id' => $payment->id,
                    'type' => 'error',
                    'data' => ['error' => $e->getMessage()],
                    'status' => 'failed',
                ]);
            }

            return new PaymentResponse([
                'status' => 'error',
                'gateway' => 'monobank',
                'metadata' => [
                    'error' => $e->getMessage(),
                ],
            ]);
        }
    }

    /**
     * Перевірити статус платежу
     */
    public function verifyPayment(string $paymentId): PaymentStatus
    {
        try {
            $payment = Payment::findOrFail($paymentId);

            if (! $payment->external_id) {
                throw new \Exception('Invoice ID not found');
            }

            // Відправити запит на перевірку статусу
            $response = Http::connectTimeout(5)
                ->timeout(30)
                ->retry(3, 100, throw: false)
                ->withHeaders([
                    'X-Token' => $this->apiToken,
                ])->get("{$this->apiUrl}/invoice/status", [
                    'invoiceId' => $payment->external_id,
                ]);

            if ($response->successful()) {
                $responseData = $response->json();

                // Мапінг статусів Monobank на наші статуси
                $statusMap = [
                    'created' => 'pending',
                    'processing' => 'processing',
                    'hold' => 'processing',
                    'success' => 'success',
                    'failure' => 'failed',
                    'reversed' => 'reversed',
                    'expired' => 'failed',
                ];

                $status = $statusMap[$responseData['status']] ?? 'failed';

                // Оновити статус платежу
                if ($payment->status !== $status) {
                    $payment->update(['status' => $status]);

                    // Оновити замовлення якщо платіж успішний
                    if ($status === 'success' && ! $payment->order->paid_at) {
                        $payment->order->update([
                            'status' => 'paid',
                            'paid_at' => now(),
                        ]);
                    }
                }

                PaymentLog::create([
                    'payment_id' => $payment->id,
                    'type' => 'status_check',
                    'data' => $responseData,
                    'status' => 'success',
                ]);

                return new PaymentStatus(
                    success: $status === 'success',
                    status: $status,
                    amount: $responseData['amount'] / 100, // конвертувати з копійок
                    currency: 'UAH',
                    transactionId: $payment->external_id,
                    message: "Статус платежу: {$status}"
                );
            } else {
                throw new \Exception($response->json()['errText'] ?? 'Помилка перевірки статусу');
            }

        } catch (\Exception $e) {
            Log::error('Monobank payment verification failed', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);

            return new PaymentStatus(
                success: false,
                status: 'error',
                amount: 0,
                currency: 'UAH',
                transactionId: null,
                message: 'Помилка перевірки платежу: '.$e->getMessage()
            );
        }
    }

    /**
     * Повернути кошти
     */
    public function refundPayment(string $paymentId, float $amount): RefundResponse
    {
        try {
            $payment = Payment::findOrFail($paymentId);

            if ($payment->status !== 'success') {
                throw new \Exception('Платіж не може бути повернений');
            }

            if (! $payment->external_id) {
                throw new \Exception('Invoice ID not found');
            }

            // Підготувати дані для повернення
            $refundData = [
                'invoiceId' => $payment->external_id,
                'amount' => (int) round($amount * 100), // сума в копійках
                'items' => [], // товари для часткового повернення
                'extRef' => Str::uuid()->toString(), // унікальний ідентифікатор операції
            ];

            // Відправити запит на повернення
            $response = Http::connectTimeout(5)
                ->timeout(30)
                ->retry(3, 100, throw: false)
                ->withHeaders([
                    'X-Token' => $this->apiToken,
                    'Content-Type' => 'application/json',
                ])->post("{$this->apiUrl}/invoice/cancel", $refundData);

            if ($response->successful()) {
                $responseData = $response->json();

                // Оновити статус платежу
                if ($amount >= $payment->amount) {
                    $payment->update(['status' => 'reversed']);
                }

                PaymentLog::create([
                    'payment_id' => $payment->id,
                    'type' => 'refund',
                    'data' => [
                        'request' => $refundData,
                        'response' => $responseData,
                    ],
                    'status' => 'success',
                ]);

                return new RefundResponse(
                    success: true,
                    refundId: $responseData['createdDate'] ?? $refundData['extRef'],
                    amount: $amount,
                    currency: 'UAH',
                    message: 'Кошти успішно повернено'
                );
            } else {
                throw new \Exception($response->json()['errText'] ?? 'Помилка повернення коштів');
            }

        } catch (\Exception $e) {
            Log::error('Monobank refund failed', [
                'payment_id' => $paymentId,
                'amount' => $amount,
                'error' => $e->getMessage(),
            ]);

            if (isset($payment)) {
                PaymentLog::create([
                    'payment_id' => $payment->id,
                    'type' => 'refund_error',
                    'data' => [
                        'amount' => $amount,
                        'error' => $e->getMessage(),
                    ],
                    'status' => 'failed',
                ]);
            }

            return new RefundResponse(
                success: false,
                refundId: null,
                amount: 0,
                currency: 'UAH',
                message: 'Помилка повернення коштів: '.$e->getMessage()
            );
        }
    }

    /**
     * Обробити webhook від Monobank
     */
    public function handleWebhook(Request $request): WebhookResponse
    {
        try {
            // Отримати дані webhook
            $data = $request->all();

            // Перевірити підпис
            $publicKeyBase64 = config('services.monobank.webhook_public_key');
            $signature = $request->header('X-Sign');

            if ($publicKeyBase64 && $signature) {
                $publicKey = base64_decode($publicKeyBase64);
                $message = $request->getContent();
                $signatureBinary = base64_decode($signature);

                if (! openssl_verify($message, $signatureBinary, $publicKey, OPENSSL_ALGO_SHA256)) {
                    throw new \Exception('Invalid webhook signature');
                }
            }

            // Знайти платіж за reference
            $reference = $data['reference'] ?? null;
            if (! $reference) {
                throw new \Exception('Reference not found in webhook data');
            }

            $payment = Payment::findOrFail($reference);

            // Логування webhook
            PaymentLog::create([
                'payment_id' => $payment->id,
                'type' => 'webhook',
                'data' => $data,
                'status' => 'received',
            ]);

            // Мапінг статусів Monobank на наші статуси
            $statusMap = [
                'created' => 'pending',
                'processing' => 'processing',
                'hold' => 'processing',
                'success' => 'success',
                'failure' => 'failed',
                'reversed' => 'reversed',
                'expired' => 'failed',
            ];

            $newStatus = $statusMap[$data['status']] ?? 'failed';

            // Оновити платіж
            $payment->update([
                'status' => $newStatus,
                'external_id' => $data['invoiceId'] ?? $payment->external_id,
                'metadata' => array_merge(
                    $payment->metadata ?? [],
                    [
                        'monobank_status' => $data['status'],
                        'modified_date' => $data['modifiedDate'] ?? null,
                        'payment_info' => $data['paymentInfo'] ?? null,
                        'cancel_list' => $data['cancelList'] ?? null,
                    ]
                ),
            ]);

            // Оновити замовлення якщо платіж успішний
            if ($newStatus === 'success' && ! $payment->order->paid_at) {
                $payment->order->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);
            }

            return new WebhookResponse(
                success: true,
                order_id: $payment->order_id,
                payment_id: $payment->id,
                status: $newStatus,
                amount: ($data['amount'] ?? 0) / 100, // конвертувати з копійок
                message: 'Webhook оброблено успішно'
            );

        } catch (\Exception $e) {
            Log::error('Monobank webhook processing failed', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return new WebhookResponse(
                success: false,
                order_id: null,
                payment_id: null,
                status: 'error',
                amount: 0,
                message: 'Помилка обробки webhook: '.$e->getMessage()
            );
        }
    }

    /**
     * Отримати доступні способи оплати
     */
    public function getAvailableMethods(): array
    {
        return [
            'monobank' => [
                'name' => 'Monobank',
                'icon' => 'monobank',
                'description' => 'Оплата через додаток monobank',
            ],
            'card' => [
                'name' => 'Банківська картка',
                'icon' => 'credit-card',
                'description' => 'Visa, Mastercard через Monobank',
            ],
            'google_pay' => [
                'name' => 'Google Pay',
                'icon' => 'google-pay',
                'description' => 'Швидка оплата через Google Pay',
            ],
            'apple_pay' => [
                'name' => 'Apple Pay',
                'icon' => 'apple-pay',
                'description' => 'Швидка оплата через Apple Pay',
            ],
        ];
    }

    /**
     * Перевірити чи підтримується замовлення
     */
    public function supportsOrder(Order $order): bool
    {
        // Перевірити мінімальну та максимальну суму
        return $order->total >= 1 && $order->total <= 999999;
    }

    /**
     * Отримати назву для відображення
     */
    public function getDisplayName(): string
    {
        return 'Monobank';
    }

    /**
     * Отримати опис
     */
    public function getDescription(): string
    {
        return 'Оплата через Monobank або банківську картку';
    }

    /**
     * Розрахувати комісію
     */
    public function calculateFee(float $amount): float
    {
        $settings = $this->getSettings();
        $feePercentage = $settings['fee_percentage'] ?? 0;

        return $feePercentage > 0 ? $amount * ($feePercentage / 100) : 0;
    }

    /**
     * Отримати час обробки
     */
    public function getProcessingTime(): string
    {
        return 'Миттєво';
    }

    /**
     * Отримати підтримувані функції
     */
    public function getSupportedFeatures(): array
    {
        return ['refund', 'partial_refund', 'recurring'];
    }

    /**
     * Отримати мінімальну суму
     */
    public function getMinAmount(): float
    {
        $settings = $this->getSettings();

        return $settings['min_amount'] ?? 1.0;
    }

    /**
     * Отримати максимальну суму
     */
    public function getMaxAmount(): float
    {
        $settings = $this->getSettings();

        return $settings['max_amount'] ?? 999999.0;
    }

    /**
     * Отримати підтримувані валюти
     */
    public function getSupportedCurrencies(): array
    {
        return ['UAH'];
    }

    /**
     * Перевірити чи працює в тестовому режимі
     */
    public function isTestMode(): bool
    {
        return $this->sandboxMode;
    }
}
