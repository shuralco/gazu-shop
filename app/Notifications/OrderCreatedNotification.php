<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Order $order) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject("Замовлення #{$this->order->id} створено")
            ->greeting('Дякуємо за замовлення!')
            ->line("Ваше замовлення #{$this->order->id} прийнято в обробку.")
            ->line('Сума: '.number_format($this->order->total, 2).' грн');

        foreach ($this->order->orderProducts->take(5) as $item) {
            $message->line("- {$item->title} x {$item->quantity} — ".number_format($item->price * $item->quantity, 2).' грн');
        }

        return $message
            ->action('Переглянути замовлення', url("/order-show/{$this->order->id}"))
            ->line('Ми повідомимо вас про зміну статусу.');
    }
}
