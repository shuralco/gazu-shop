<?php

namespace App\Livewire\Cart;

use Livewire\Component;

class CartNotificationHelper extends Component
{
    public function notifyCartUpdated(string $productTitle, int $quantity = 1): void
    {
        $this->dispatch('show-notification',
            type: 'success',
            title: 'ТОВАР ДОДАНО',
            message: "Товар '{$productTitle}' ({$quantity} шт.) додано до кошика",
            action: 'ПЕРЕГЛЯНУТИ КОШИК',
            actionUrl: route('cart')
        );
    }

    public function notifyCartRemoved(string $productTitle): void
    {
        $this->dispatch('show-notification',
            type: 'warning',
            title: 'ТОВАР ВИДАЛЕНО',
            message: "Товар '{$productTitle}' видалено з кошика",
            action: 'ПОВЕРНУТИ',
            actionUrl: '#'
        );
    }

    public function notifyCartCleared(): void
    {
        $this->dispatch('show-notification',
            type: 'info',
            title: 'КОШИК ОЧИЩЕНО',
            message: 'Всі товари видалено з кошика'
        );
    }

    public function notifyStockIssue(string $productTitle): void
    {
        $this->dispatch('show-notification',
            type: 'error',
            title: 'НЕДОСТАТНЬО ТОВАРУ',
            message: "Товар '{$productTitle}' закінчився на складі",
            action: 'СПОВІСТИТИ ПРО НАДХОДЖЕННЯ'
        );
    }

    public function notifyPaymentIssue(string $errorMessage): void
    {
        $this->dispatch('show-notification',
            type: 'error',
            title: 'ПОМИЛКА ОПЛАТИ',
            message: $errorMessage,
            action: 'СПРОБУВАТИ ЗНОВУ'
        );
    }

    public function notifyShippingUpdate(string $orderId, string $status): void
    {
        $statusText = match ($status) {
            'shipped' => 'ВІДПРАВЛЕНО',
            'in_transit' => 'В ДОРОЗІ',
            'delivered' => 'ДОСТАВЛЕНО',
            'delayed' => 'ЗАТРИМУЄТЬСЯ',
            default => 'ОНОВЛЕНО'
        };

        $this->dispatch('show-notification',
            type: $status === 'delayed' ? 'warning' : 'info',
            title: 'СТАТУС ДОСТАВКИ',
            message: "Замовлення #{$orderId}: {$statusText}",
            action: 'ВІДСТЕЖИТИ',
            actionUrl: route('orders.tracking', ['order' => $orderId])
        );
    }

    public function notifyPromotion(string $title, string $description, ?string $promoUrl = null): void
    {
        $this->dispatch('show-notification',
            type: 'purple',
            title: $title,
            message: $description,
            action: $promoUrl ? 'ПЕРЕГЛЯНУТИ' : null,
            actionUrl: $promoUrl
        );
    }

    public function render(): mixed
    {
        return view('livewire.cart.cart-notification-helper');
    }
}
