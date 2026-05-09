<?php

namespace App\DTOs;

class PaymentResponse
{
    public string $status;

    public ?string $redirect_url = null;

    public ?array $form_data = null;

    public ?string $form_action = null;

    public ?string $external_id = null;

    public string $gateway;

    public array $metadata;

    public ?string $qr_code = null;

    public function __construct(array $data)
    {
        $this->status = $data['status'];
        $this->redirect_url = $data['redirect_url'] ?? null;
        $this->form_data = $data['form_data'] ?? null;
        $this->form_action = $data['form_action'] ?? null;
        $this->external_id = $data['external_id'] ?? null;
        $this->gateway = $data['gateway'] ?? '';
        $this->metadata = $data['metadata'] ?? [];
        $this->qr_code = $data['qr_code'] ?? null;
    }

    public function isRedirect(): bool
    {
        return $this->status === 'redirect' && ! empty($this->redirect_url);
    }

    public function isFormSubmit(): bool
    {
        return $this->status === 'form_redirect' && ! empty($this->form_data);
    }

    public function hasQrCode(): bool
    {
        return ! empty($this->qr_code);
    }
}
