<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private const STATUS_LABELS = [
        'pending' => 'Нове',
        'processing' => 'В обробці',
        'shipped' => 'Відправлено',
        'delivered' => 'Доставлено',
        'cancelled' => 'Скасовано',
    ];

    public function __construct(public Order $order) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $statusLabel = self::STATUS_LABELS[$this->order->status] ?? $this->order->status;

        return (new MailMessage)
            ->subject("Замовлення #{$this->order->id} — {$statusLabel}")
            ->greeting("Статус замовлення оновлено")
            ->line("Замовлення #{$this->order->id} отримало новий статус: **{$statusLabel}**")
            ->line('Сума: '.number_format($this->order->total, 2).' грн')
            ->action('Деталі замовлення', url("/order-show/{$this->order->id}"))
            ->line('Дякуємо, що обираєте нас!');
    }
}
