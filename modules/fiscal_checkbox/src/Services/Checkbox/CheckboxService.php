<?php

namespace App\Services\Checkbox;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class CheckboxService
{
    private string $apiUrl;
    private ?string $token = null;

    public function __construct()
    {
        $this->apiUrl = config('checkbox.api_url');
    }

    public function isEnabled(): bool
    {
        return (bool) config('checkbox.enabled');
    }

    public function authenticate(): bool
    {
        if (!$this->isEnabled()) return false;

        try {
            $response = Http::connectTimeout(5)->timeout(10)
                ->post("{$this->apiUrl}/cashier/signin", [
                    'login' => config('checkbox.login'),
                    'password' => config('checkbox.password'),
                ]);

            if ($response->successful()) {
                $this->token = $response->json('access_token');
                Cache::put('checkbox_token', $this->token, now()->addHours(12));
                return true;
            }

            Log::error('Checkbox auth failed', ['status' => $response->status()]);
            return false;
        } catch (\Throwable $e) {
            Log::error('Checkbox auth error', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function getToken(): ?string
    {
        if ($this->token) return $this->token;

        $cached = Cache::get('checkbox_token');
        if ($cached) {
            $this->token = $cached;
            return $this->token;
        }

        $this->authenticate();
        return $this->token;
    }

    public function createReceipt(Order $order): ?array
    {
        if (!$this->isEnabled()) return null;

        $token = $this->getToken();
        if (!$token) {
            Log::error('Checkbox: no auth token for receipt', ['order_id' => $order->id]);
            return null;
        }

        try {
            $goods = [];
            foreach ($order->orderProducts as $item) {
                $goods[] = [
                    'good' => [
                        'code' => $item->sku ?? "ITEM-{$item->id}",
                        'name' => $item->title,
                        'price' => (int) round($item->price * 100), // kopiyky
                    ],
                    'quantity' => $item->quantity * 1000, // thousandths
                    'is_return' => false,
                ];
            }

            // Delivery as a separate line item
            if ($order->shipping_cost > 0) {
                $goods[] = [
                    'good' => [
                        'code' => 'DELIVERY',
                        'name' => 'Доставка',
                        'price' => (int) round($order->shipping_cost * 100),
                    ],
                    'quantity' => 1000,
                    'is_return' => false,
                ];
            }

            $payments = [];
            if (in_array($order->payment_method, ['liqpay', 'wayforpay', 'monobank'])) {
                $payments[] = [
                    'type' => 'CASHLESS',
                    'value' => (int) round($order->total * 100),
                ];
            } else {
                $payments[] = [
                    'type' => 'CASH',
                    'value' => (int) round($order->total * 100),
                ];
            }

            // Discount
            $discounts = [];
            if ($order->discount_amount > 0) {
                $discounts[] = [
                    'type' => 'DISCOUNT',
                    'mode' => 'VALUE',
                    'value' => (int) round($order->discount_amount * 100),
                ];
            }

            $receiptData = [
                'goods' => $goods,
                'payments' => $payments,
                'discounts' => $discounts,
            ];

            $response = Http::connectTimeout(5)->timeout(15)
                ->withToken($token)
                ->post("{$this->apiUrl}/receipts/sell", $receiptData);

            if ($response->successful()) {
                $receipt = $response->json();
                Log::info('Checkbox receipt created', [
                    'order_id' => $order->id,
                    'receipt_id' => $receipt['id'] ?? null,
                    'fiscal_code' => $receipt['fiscal_code'] ?? null,
                ]);
                return $receipt;
            }

            Log::error('Checkbox receipt creation failed', [
                'order_id' => $order->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return null;
        } catch (\Throwable $e) {
            Log::error('Checkbox receipt error', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    public function createReturnReceipt(Order $order): ?array
    {
        if (!$this->isEnabled()) return null;

        $token = $this->getToken();
        if (!$token) return null;

        try {
            $goods = [];
            foreach ($order->orderProducts as $item) {
                $goods[] = [
                    'good' => [
                        'code' => $item->sku ?? "ITEM-{$item->id}",
                        'name' => $item->title,
                        'price' => (int) round($item->price * 100),
                    ],
                    'quantity' => $item->quantity * 1000,
                    'is_return' => true,
                ];
            }

            $payments = [
                [
                    'type' => 'CASHLESS',
                    'value' => (int) round($order->total * 100),
                ],
            ];

            $response = Http::connectTimeout(5)->timeout(15)
                ->withToken($token)
                ->post("{$this->apiUrl}/receipts/sell", [
                    'goods' => $goods,
                    'payments' => $payments,
                ]);

            if ($response->successful()) {
                $receipt = $response->json();
                Log::info('Checkbox return receipt created', ['order_id' => $order->id]);
                return $receipt;
            }

            return null;
        } catch (\Throwable $e) {
            Log::error('Checkbox return receipt error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    public function openShift(): ?array
    {
        $token = $this->getToken();
        if (!$token) return null;

        try {
            $response = Http::connectTimeout(5)->timeout(10)
                ->withToken($token)
                ->post("{$this->apiUrl}/shifts");

            return $response->successful() ? $response->json() : null;
        } catch (\Throwable $e) {
            Log::error('Checkbox open shift error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    public function closeShift(): ?array
    {
        $token = $this->getToken();
        if (!$token) return null;

        try {
            $response = Http::connectTimeout(5)->timeout(10)
                ->withToken($token)
                ->post("{$this->apiUrl}/shifts/close");

            return $response->successful() ? $response->json() : null;
        } catch (\Throwable $e) {
            Log::error('Checkbox close shift error', ['error' => $e->getMessage()]);
            return null;
        }
    }
}
