<?php

namespace App\DTOs;

class WebhookResponse
{
    public function __construct(
        public bool $success,
        public ?int $order_id,
        public ?string $payment_id,
        public string $status,
        public float $amount,
        public string $message,
        public ?string $external_id = null,
        public string $currency = 'UAH'
    ) {}
}
