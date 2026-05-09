<?php

namespace App\Livewire\Cart;

use App\Services\CouponService;
use Livewire\Component;

class CouponComponent extends Component
{
    public string $couponCode = '';

    public array $appliedCoupon = [];

    public string $message = '';

    public string $messageType = ''; // success, error

    protected $couponService;

    public function boot(CouponService $couponService)
    {
        $this->couponService = $couponService;
    }

    protected $listeners = ['coupon-reset' => 'resetCoupon'];

    public function applyCoupon()
    {
        $this->validate([
            'couponCode' => 'required|string|min:3|max:50',
        ], [
            'couponCode.required' => 'Введіть код купону',
            'couponCode.min' => 'Код купону має містити мінімум 3 символи',
            'couponCode.max' => 'Код купону занадто довгий',
        ]);

        // Отримати дані кошика
        $cartTotal = \App\Helpers\Cart\Cart::getCartTotal();
        $shippingCost = session('shipping_cost', 0);

        // Отримати дані користувача
        $userId = auth()->id();
        $userEmail = auth()->user()?->email ?? session('guest_email');

        // Застосувати купон
        $result = $this->couponService->applyCoupon(
            $this->couponCode,
            $cartTotal,
            $shippingCost,
            $userId,
            $userEmail
        );

        if ($result['success']) {
            $this->appliedCoupon = [
                'code' => $this->couponCode,
                'discount' => $result['discount'],
                'coupon_id' => $result['coupon']->id,
                'type' => $result['coupon']->type,
            ];

            $this->message = $result['message'];
            $this->messageType = 'success';

            // Зберегти купон в сесії
            session(['applied_coupon' => $this->appliedCoupon]);

            // Повідомити інші компоненти про застосування купону
            $this->dispatch('coupon-applied', $this->appliedCoupon);

        } else {
            $this->message = $result['message'];
            $this->messageType = 'error';
            $this->resetCoupon();
        }
    }

    public function removeCoupon()
    {
        $this->resetCoupon();
        session()->forget('applied_coupon');
        $this->dispatch('coupon-removed');

        $this->message = 'Купон видалено';
        $this->messageType = 'success';

        // Очистити повідомлення через 3 секунди
        $this->dispatch('clear-message');
    }

    public function resetCoupon()
    {
        $this->couponCode = '';
        $this->appliedCoupon = [];
        $this->message = '';
        $this->messageType = '';
    }

    public function mount()
    {
        // Відновити купон з сесії якщо є
        $sessionCoupon = session('applied_coupon');
        if ($sessionCoupon && is_array($sessionCoupon)) {
            $this->appliedCoupon = $sessionCoupon;
            $this->couponCode = $sessionCoupon['code'] ?? '';
        }
    }

    public function render()
    {
        return view('livewire.cart.coupon-component');
    }
}
