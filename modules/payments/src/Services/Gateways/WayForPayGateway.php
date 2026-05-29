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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use WayForPay\SDK\Collection\ProductCollection;
use WayForPay\SDK\Credential\AccountSecretCredential;
use WayForPay\SDK\Domain\Client;
use WayForPay\SDK\Domain\Product;
use WayForPay\SDK\Domain\TransactionBase;
use WayForPay\SDK\Handler\ServiceUrlHandler;
use WayForPay\SDK\Wizard\PurchaseWizard;
use WayForPay\SDK\Wizard\RefundWizard;

class WayForPayGateway implements PaymentGatewayInterface
{
    private AccountSecretCredential $credential;

    private string $merchantDomainName;

    private bool $sandboxMode;

    public function __construct()
    {
        $settings = $this->getSettings();

        $this->credential = new AccountSecretCredential(
            $settings['merchant_account'] ?? config('services.wayforpay.merchant_account'),
            $settings['merchant_secret_key'] ?? config('services.wayforpay.merchant_secret_key')
        );

        $this->merchantDomainName = $settings['merchant_domain'] ?? config('services.wayforpay.merchant_domain', config('app.url'));
        $this->sandboxMode = $settings['sandbox'] ?? config('services.wayforpay.sandbox', false);
    }

    private function getSettings(): array
    {
        $gatewaySettings = PaymentGatewaySettings::where('code', 'wayforpay')->first();

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
                ->where('gateway', 'wayforpay')
                ->where('status', 'pending')
                ->first();

            if (! $payment) {
                $payment = Payment::create([
                    'id' => Str::uuid(),
                    'order_id' => $order->id,
                    'gateway' => 'wayforpay',
                    'amount' => $order->total,
                    'currency' => 'UAH',
                    'status' => 'pending',
                    'metadata' => $options,
                ]);
            }

            // Створити колекцію товарів
            $products = new ProductCollection;

            foreach ($order->orderProducts as $orderProduct) {
                $products->add(new Product(
                    $orderProduct->title,
                    $orderProduct->price,
                    $orderProduct->quantity
                ));
            }

            // Якщо немає товарів, додати загальний товар
            if ($products->count() === 0) {
                $products->add(new Product(
                    "Замовлення #{$order->id}",
                    $order->total,
                    1
                ));
            }

            // Створити клієнта
            $client = new Client(
                $order->name,
                $order->name,
                $order->email,
                $order->user->phone ?? '+380000000000',
                'Ukraine'
            );

            // Створити Purchase форму
            $form = PurchaseWizard::get($this->credential)
                ->setOrderReference($payment->id)
                ->setAmount($order->total)
                ->setCurrency('UAH')
                ->setOrderDate(new \DateTime)
                ->setMerchantDomainName($this->merchantDomainName)
                ->setClient($client)
                ->setProducts($products)
                ->setReturnUrl(route('gazu.checkout.success', $order))
                ->setServiceUrl(route('webhooks.wayforpay'));

            // Отримати форму для відправки
            $formData = $form->getForm()->getData();

            // Логування
            PaymentLog::create([
                'payment_id' => $payment->id,
                'action' => 'create_payment',
                'request_data' => array_merge($formData, ['merchantSecretKey' => '***hidden***']),
                'response_data' => ['status' => 'form_created'],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return new PaymentResponse([
                'status' => 'form_redirect',
                'external_id' => $payment->id,
                'form_data' => $formData,
                'form_action' => 'https://secure.wayforpay.com/pay',
                'gateway' => 'wayforpay',
                'metadata' => [
                    'order_id' => $order->id,
                    'amount' => $order->total,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('WayForPay payment creation failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            if (isset($payment)) {
                PaymentLog::create([
                    'payment_id' => $payment->id,
                    'action' => 'create_payment_error',
                    'request_data' => [],
                    'response_data' => ['error' => $e->getMessage()],
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }

            return new PaymentResponse([
                'status' => 'error',
                'gateway' => 'wayforpay',
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

            // WayForPay не надає API для перевірки статусу,
            // статус оновлюється через webhook

            return new PaymentStatus([
                'status' => $payment->status,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'external_id' => $payment->external_id,
                'raw_data' => ['message' => "Статус платежу: {$payment->status}"],
            ]);

        } catch (\Exception $e) {
            Log::error('WayForPay payment verification failed', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);

            return new PaymentStatus([
                'status' => 'error',
                'amount' => 0,
                'currency' => 'UAH',
                'external_id' => null,
                'raw_data' => ['message' => 'Помилка перевірки платежу'],
            ]);
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

            $refund = RefundWizard::get($this->credential)
                ->setOrderReference($payment->external_id)
                ->setAmount($amount)
                ->setCurrency('UAH')
                ->setComment("Повернення для замовлення #{$payment->order_id}");

            $response = $refund->getRequest()->send();

            if ($response->isSuccessful()) {
                // Оновити статус платежу
                $payment->update(['status' => 'reversed']);

                PaymentLog::create([
                    'payment_id' => $payment->id,
                    'action' => 'refund',
                    'request_data' => ['amount' => $amount],
                    'response_data' => $response->toArray(),
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);

                return new RefundResponse([
                    'success' => true,
                    'refund_id' => $response->getTransactionStatus(),
                    'amount' => $amount,
                    'raw_data' => ['currency' => 'UAH', 'message' => 'Кошти успішно повернено'],
                ]);
            } else {
                throw new \Exception($response->getReasonCode().': '.$response->getReason());
            }

        } catch (\Exception $e) {
            Log::error('WayForPay refund failed', [
                'payment_id' => $paymentId,
                'amount' => $amount,
                'error' => $e->getMessage(),
            ]);

            if (isset($payment)) {
                PaymentLog::create([
                    'payment_id' => $payment->id,
                    'action' => 'refund_error',
                    'request_data' => ['amount' => $amount],
                    'response_data' => ['error' => $e->getMessage()],
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }

            return new RefundResponse([
                'success' => false,
                'refund_id' => null,
                'amount' => 0,
                'raw_data' => ['currency' => 'UAH', 'message' => 'Помилка повернення коштів: '.$e->getMessage()],
            ]);
        }
    }

    /**
     * Обробити webhook від WayForPay
     */
    public function handleWebhook(Request $request): WebhookResponse
    {
        try {
            $handler = new ServiceUrlHandler($this->credential);
            $response = $handler->parseRequestFromPostRaw();

            $transactionStatus = $response->getTransaction()->getStatus();
            $orderReference = $response->getTransaction()->getOrderReference();
            $externalId = $response->getTransaction()->getProcessingId();
            $amount = $response->getTransaction()->getAmount();

            // Знайти платіж
            $payment = Payment::findOrFail($orderReference);

            // Логування webhook
            PaymentLog::create([
                'payment_id' => $payment->id,
                'action' => 'webhook_received',
                'request_data' => $request->all(),
                'response_data' => $response->getTransaction()->toArray(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            // Мапінг статусів WayForPay на наші статуси
            $statusMap = [
                TransactionBase::STATUS_APPROVED => 'success',
                TransactionBase::STATUS_PENDING => 'processing',
                TransactionBase::STATUS_DECLINED => 'failed',
                TransactionBase::STATUS_EXPIRED => 'failed',
                TransactionBase::STATUS_REFUNDED => 'reversed',
                TransactionBase::STATUS_VOIDED => 'reversed',
            ];

            $newStatus = $statusMap[$transactionStatus] ?? 'failed';

            // Оновити платіж
            $payment->update([
                'status' => $newStatus,
                'external_id' => $externalId,
                'metadata' => array_merge(
                    $payment->metadata ?? [],
                    [
                        'wayforpay_status' => $transactionStatus,
                        'processing_id' => $externalId,
                        'auth_code' => $response->getTransaction()->getAuthCode(),
                        'card_pan' => $response->getTransaction()->getCardPan(),
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

            // Відправити відповідь WayForPay
            echo $handler->getSuccessResponse($response->getTransaction());

            return new WebhookResponse(
                success: true,
                order_id: $payment->order_id,
                payment_id: $payment->id,
                status: $newStatus,
                amount: $amount,
                message: 'Webhook оброблено успішно'
            );

        } catch (\Exception $e) {
            Log::error('WayForPay webhook processing failed', [
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
            'card' => [
                'name' => 'Банківська картка',
                'icon' => 'credit-card',
                'description' => 'Visa, Mastercard',
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
     * Отримати підтримувані валюти
     */
    public function getSupportedCurrencies(): array
    {
        return ['UAH', 'USD', 'EUR'];
    }

    /**
     * Перевірити чи працює в тестовому режимі
     */
    public function isTestMode(): bool
    {
        return $this->sandboxMode;
    }

    /**
     * Отримати відображувану назву
     */
    public function getDisplayName(): string
    {
        return 'WayForPay';
    }

    /**
     * Отримати опис
     */
    public function getDescription(): string
    {
        return 'Оплата через WayForPay (картки, Google Pay, Apple Pay)';
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

        return $settings['max_amount'] ?? 999999.00;
    }

    /**
     * Перевірити чи підтримується замовлення
     */
    public function supportsOrder(Order $order): bool
    {
        return $order->total >= $this->getMinAmount() &&
               $order->total <= $this->getMaxAmount();
    }
}
