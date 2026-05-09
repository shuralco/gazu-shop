<?php

namespace App\DTOs;

class RefundResponse
{
    public bool $success;

    public ?string $refund_id = null;

    public float $amount;

    public array $raw_data;

    public function __construct(array $data)
    {
        $this->success = $data['success'];
        $this->refund_id = $data['refund_id'] ?? null;
        $this->amount = $data['amount'] ?? 0.0;
        $this->raw_data = $data['raw_data'] ?? [];
    }
}
