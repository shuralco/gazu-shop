<?php

namespace App\DTOs;

class PaymentStatus
{
    public string $status;

    public ?string $external_id = null;

    public float $amount;

    public string $currency;

    public array $raw_data;

    public function __construct(array $data)
    {
        $this->status = $data['status'];
        $this->external_id = $data['external_id'] ?? null;
        $this->amount = $data['amount'] ?? 0.0;
        $this->currency = $data['currency'] ?? 'UAH';
        $this->raw_data = $data['raw_data'] ?? [];
    }

    public function isSuccessful(): bool
    {
        return $this->status === 'success';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isPending(): bool
    {
        return in_array($this->status, ['pending', 'processing']);
    }
}
