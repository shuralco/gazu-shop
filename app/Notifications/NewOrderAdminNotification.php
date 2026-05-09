<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewOrderAdminNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Order $order) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $customerName = $this->order->first_name.' '.$this->order->last_name;
        $itemsCount = $this->order->orderProducts->count();

        return (new MailMessage)
            ->subject("Нове замовлення #{$this->order->id}")
            ->greeting('Нове замовлення!')
            ->line("Замовлення #{$this->order->id} від {$customerName}")
            ->line("Товарів: {$itemsCount}")
            ->line('Сума: '.number_format($this->order->total, 2).' грн')
            ->line('Оплата: '.($this->order->payment_method ?? 'не вказано'))
            ->action('Відкрити в адмінці', url("/admin/orders/{$this->order->id}/edit"));
    }
}
