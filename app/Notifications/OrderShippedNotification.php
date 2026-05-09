<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderShippedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Order $order, public ?string $trackingNumber = null) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject("Замовлення #{$this->order->id} відправлено!")
            ->greeting('Ваше замовлення в дорозі!')
            ->line("Замовлення #{$this->order->id} було відправлено.");

        if ($this->trackingNumber) {
            $message->line("Номер відстеження: **{$this->trackingNumber}**");
        }

        if ($this->order->shipping_provider) {
            $message->line("Служба доставки: {$this->order->shipping_provider}");
        }

        return $message
            ->action('Відстежити замовлення', url("/order-show/{$this->order->id}"))
            ->line('Очікуйте доставку найближчим часом!');
    }
}
