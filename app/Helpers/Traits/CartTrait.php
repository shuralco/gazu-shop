<?php

namespace App\Helpers\Traits;

use App\Helpers\Cart\Cart;

trait CartTrait
{
    public $quantity = 1;

    public function add2Cart(int $productId, $quantity = false)
    {
        $quantity = $quantity ? (int) $this->quantity : 1;
        if ($quantity < 1) {
            $quantity = 1;
        }

        $variantId = property_exists($this, 'selectedVariantId') ? $this->selectedVariantId : null;

        // Optimistic UI update для миттєвого відгуку
        $tempId = uniqid('temp_');
        $this->dispatch('cart-add-optimistic', [
            'productId' => $productId,
            'quantity' => $quantity,
            'tempId' => $tempId,
        ]);

        try {
            if (Cart::add2Cart($productId, $quantity, $variantId)) {
                $this->dispatch('cart-updated');
                $this->dispatch('cart-add-confirmed', tempId: $tempId);
                $this->dispatch('notify', message: 'Товар додано в кошик');
            } else {
                $this->dispatch('cart-add-failed', tempId: $tempId);
                $this->dispatch('show-notification',
                    type: 'error',
                    title: 'ПОМИЛКА',
                    message: 'Не вдалося додати товар до кошика'
                );
            }
        } catch (\Exception $e) {
            $this->dispatch('cart-add-failed', tempId: $tempId);
            $this->dispatch('show-notification',
                type: 'error',
                title: 'ПОМИЛКА',
                message: 'Виникла помилка при додаванні товару'
            );
        }
    }

    private function shouldShowNotification(string $action, int $productId): bool
    {
        $key = "notification_debounce_{$action}_{$productId}";
        $lastTime = session($key);

        // Allow notification if none exists or if 100ms have passed
        return ! $lastTime || now()->diffInMilliseconds($lastTime) >= 100;
    }

    private function recordNotification(string $action, int $productId): void
    {
        $key = "notification_debounce_{$action}_{$productId}";
        session([$key => now()]);
    }

    public function removeFromCart(int $productId): void
    {
        if (Cart::removeProductFromCart($productId)) {
            $this->dispatch('show-notification',
                type: 'warning',
                title: 'ТОВАР ВИДАЛЕНО',
                message: 'Товар видалено з кошика'
            );
            $this->dispatch('cart-updated');
        } else {
            $this->dispatch('show-notification',
                type: 'error',
                title: 'ПОМИЛКА',
                message: 'Не вдалося видалити товар з кошика'
            );
        }
    }

    public function updateItemQuantity(int $productId, int $quantity)
    {
        if ($quantity <= 0) {
            $quantity = 1;
        }

        if (Cart::updateItemQuantity($productId, $quantity)) {
            $this->dispatch('cart-updated');
            $this->dispatch('show-notification',
                type: 'info',
                title: 'КІЛЬКІСТЬ ОНОВЛЕНО',
                message: "Кількість товару: {$quantity} шт."
            );
        } else {
            $this->dispatch('show-notification',
                type: 'error',
                title: 'ПОМИЛКА',
                message: 'Не вдалося оновити кількість товару'
            );
        }
    }

    public function clearCart()
    {
        Cart::clearCart();
        $this->dispatch('cart-updated');
        $this->dispatch('show-notification',
            type: 'info',
            title: 'КОШИК ОЧИЩЕНО',
            message: 'Всі товари видалено з кошика'
        );
    }
}
