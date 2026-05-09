<?php

namespace App\Mail;

use App\Models\DisplaySetting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public ?string $promoCode = null,
    ) {}

    public function envelope(): Envelope
    {
        $fromEmail = DisplaySetting::get('email_from_address', config('mail.from.address', 'no-reply@simpleshop.com'));
        $fromName = DisplaySetting::get('email_from_name', config('mail.from.name', shopName()));
        $replyTo = DisplaySetting::get('email_reply_to', $fromEmail);

        return new Envelope(
            from: new Address($fromEmail, $fromName),
            replyTo: [new Address($replyTo)],
            subject: __('general.email_welcome_subject', ['shop' => shopName()]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.welcome',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
