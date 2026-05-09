<?php

namespace App\Livewire\User;

use App\Models\Order;
use Livewire\Component;
use Livewire\WithPagination;

class OrderComponent extends Component
{
    use WithPagination;

    public string $statusFilter = '';

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function reorder(int $orderId): void
    {
        $order = Order::where('user_id', auth()->id())
            ->with('orderProducts')
            ->findOrFail($orderId);

        $cart = session()->get('cart', []);

        foreach ($order->orderProducts as $item) {
            $productId = $item->product_id;
            if (isset($cart[$productId])) {
                $cart[$productId]['quantity'] += $item->quantity;
            } else {
                $cart[$productId] = [
                    'product_id' => $productId,
                    'title' => $item->title,
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                    'image' => $item->image ?? null,
                ];
            }
        }

        session()->put('cart', $cart);
        $this->dispatch('cart-updated');
        $this->js("toastr.success('Товари додано до кошика')");
    }

    public function render()
    {
        $query = Order::query()
            ->where('user_id', auth()->id())
            ->with(['orderProducts'])
            ->orderBy('created_at', 'desc');

        if ($this->statusFilter !== '') {
            $query->where('status', $this->statusFilter);
        }

        return view('livewire.user.order-component', [
            'orders' => $query->paginate(10),
            'title' => 'Мої замовлення',
        ]);
    }
}
