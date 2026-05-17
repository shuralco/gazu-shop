<?php

namespace App\Mail;

use App\Models\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;

class TemplatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $templateKey,
        public array $variables = [],
    ) {}

    public function envelope(): Envelope
    {
        $tpl = EmailTemplate::findByKey($this->templateKey);
        if (! $tpl) {
            return new Envelope(subject: '[GAZU] '.$this->templateKey);
        }
        $rendered = $tpl->render($this->variables);
        $env = new Envelope(subject: $rendered['subject']);
        if (! empty($rendered['from_email'])) {
            $env = new Envelope(
                subject: $rendered['subject'],
                from: new Address($rendered['from_email'], $rendered['from_name'] ?? 'GAZU'),
            );
        }
        return $env;
    }

    public function content(): Content
    {
        $tpl = EmailTemplate::findByKey($this->templateKey);
        $html = $tpl ? $tpl->render($this->variables)['body'] : 'Email template "'.$this->templateKey.'" missing.';
        return new Content(
            view: 'emails.templated',
            with: ['bodyHtml' => $html],
        );
    }
}
