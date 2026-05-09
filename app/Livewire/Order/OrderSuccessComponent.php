<?php

namespace App\Livewire\Order;

use App\Models\Order;
use Livewire\Component;

class OrderSuccessComponent extends Component
{
    public int $orderId;

    public function mount(Order $order)
    {
        $this->orderId = $order->id;
    }

    public function render()
    {
        $order = Order::with('orderProducts')->findOrFail($this->orderId);

        return view('livewire.order.order-success-component', [
            'order' => $order,
        ])->layout('components.layouts.app');
    }
}
