<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LowStockAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public \Illuminate\Support\Collection $products)
    {
    }

    public function envelope(): Envelope
    {
        $count = $this->products->count();
        return new Envelope(
            subject: "⚠️ {$count} товарів закінчуються на складі",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin.low-stock',
            with: ['products' => $this->products],
        );
    }
}
