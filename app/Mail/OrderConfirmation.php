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

class OrderConfirmation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
    ) {}

    public function envelope(): Envelope
    {
        $fromEmail = DisplaySetting::get('email_from_address', config('mail.from.address', 'no-reply@simpleshop.com'));
        $fromName = DisplaySetting::get('email_from_name', config('mail.from.name', shopName()));
        $replyTo = DisplaySetting::get('email_reply_to', $fromEmail);

        return new Envelope(
            from: new Address($fromEmail, $fromName),
            replyTo: [new Address($replyTo)],
            subject: __('general.email_order_confirmation_subject', ['id' => $this->order->id]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order-confirmation',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
