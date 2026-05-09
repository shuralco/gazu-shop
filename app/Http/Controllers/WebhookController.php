<?php

namespace App\Http\Controllers;

use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __construct(
        private PaymentService $paymentService
    ) {}

    public function liqpay(Request $request): Response
    {
        try {
            $this->paymentService->handleWebhook('liqpay', $request);

            return response('OK');
        } catch (\Throwable $e) {
            Log::error('LiqPay webhook error', ['error' => $e->getMessage()]);

            return response('ERROR', $e->getCode() === 429 ? 429 : 500);
        }
    }

    public function wayforpay(Request $request): Response
    {
        try {
            $this->paymentService->handleWebhook('wayforpay', $request);

            return response('OK');
        } catch (\Throwable $e) {
            Log::error('WayForPay webhook error', ['error' => $e->getMessage()]);

            return response('ERROR', $e->getCode() === 429 ? 429 : 500);
        }
    }

    public function monobank(Request $request): Response
    {
        try {
            $this->paymentService->handleWebhook('monobank', $request);

            return response('OK');
        } catch (\Throwable $e) {
            Log::error('Monobank webhook error', ['error' => $e->getMessage()]);

            return response('ERROR', $e->getCode() === 429 ? 429 : 500);
        }
    }
}
