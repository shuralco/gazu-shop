<?php

namespace App\Mail;

use App\Models\DisplaySetting;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdate extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public string $newStatus,
        public ?string $trackingNumber = null,
    ) {}

    public function envelope(): Envelope
    {
        $fromEmail = DisplaySetting::get('email_from_address', config('mail.from.address', 'no-reply@simpleshop.com'));
        $fromName = DisplaySetting::get('email_from_name', config('mail.from.name', shopName()));
        $replyTo = DisplaySetting::get('email_reply_to', $fromEmail);

        return new Envelope(
            from: new Address($fromEmail, $fromName),
            replyTo: [new Address($replyTo)],
            subject: __('general.email_status_update_subject', ['id' => $this->order->id]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order-status-update',
        );
    }

    public function attachments(): array
    {
        return [];
    }

    public function getStatusColor(): string
    {
        return match ($this->newStatus) {
            'pending', 'new' => '#f59e0b',
            'processing' => '#3b82f6',
            'shipped' => '#8b5cf6',
            'delivered' => '#10b981',
            'cancelled' => '#ef4444',
            default => '#6b7280',
        };
    }

    public function getStatusMessage(): string
    {
        return match ($this->newStatus) {
            'pending', 'new' => __('general.email_status_msg_pending'),
            'processing' => __('general.email_status_msg_processing'),
            'shipped' => __('general.email_status_msg_shipped'),
            'delivered' => __('general.email_status_msg_delivered'),
            'cancelled' => __('general.email_status_msg_cancelled'),
            default => __('general.email_status_msg_default'),
        };
    }

    public function getStatusLabel(): string
    {
        return match ($this->newStatus) {
            'pending' => __('general.order_status_pending'),
            'new' => __('general.order_status_new'),
            'processing' => __('general.order_status_processing'),
            'shipped' => __('general.order_status_shipped'),
            'delivered' => __('general.order_status_delivered'),
            'cancelled' => __('general.order_status_cancelled'),
            default => $this->newStatus,
        };
    }
}
