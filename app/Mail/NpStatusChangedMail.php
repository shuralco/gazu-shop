<?php

namespace App\Mail;

use App\Models\NpShipment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NpStatusChangedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public NpShipment $shipment,
        public string $template, // 'shipped', 'in_warehouse', 'delivered', 'returned'
    ) {
        // Switch locale based on order
        $locale = $this->shipment->order?->locale ?? 'uk';
        $this->locale($locale);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __("emails.np.subject.{$this->template}", ['id' => $this->shipment->order_id, 'ttn' => $this->shipment->ttn ?? '']),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.np.status-changed',
            with: [
                'shipment' => $this->shipment,
                'order' => $this->shipment->order,
                'template' => $this->template,
                'trackingUrl' => $this->shipment->getTrackingUrl(),
            ],
        );
    }
}
