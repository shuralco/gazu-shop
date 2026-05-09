<?php

namespace App\Livewire\User;

use App\Models\Order;
use Livewire\Component;

class OrderShowComponent extends Component
{
    public int $id;

    public function mount($id)
    {
        $this->id = $id;
    }

    public function render()
    {
        $order = Order::query()
            ->where('user_id', '=', auth()->id())
            ->where('id', '=', $this->id)
            ->with(['orderProducts', 'payments', 'shipments.trackingUpdates', 'npShipments', 'coupon'])
            ->firstOrFail();

        // Build customer-friendly timeline
        $timeline = $this->buildTimeline($order);

        return view('livewire.user.order-show-component', [
            'order' => $order,
            'timeline' => $timeline,
            'title' => 'Order',
        ]);
    }

    /**
     * Build a unified status timeline combining order events + NP tracking history.
     */
    protected function buildTimeline(Order $order): array
    {
        $events = [];

        // 1. Order created
        $events[] = [
            'icon' => '🛒',
            'title' => __('general.order_created'),
            'datetime' => $order->created_at,
            'done' => true,
        ];

        // 2. Payment
        if ($order->payment_status === 'success') {
            $events[] = [
                'icon' => '💳',
                'title' => __('general.order_paid'),
                'datetime' => $order->paid_at ?? $order->updated_at,
                'done' => true,
            ];
        } elseif ($order->payment_method && in_array($order->payment_method, ['cash', 'cash_on_delivery'])) {
            $events[] = [
                'icon' => '💵',
                'title' => __('general.order_cod_pending'),
                'datetime' => null,
                'done' => false,
            ];
        }

        // 3. NP tracking history
        $shipment = $order->npShipments->first();
        if ($shipment) {
            if ($shipment->ttn) {
                $events[] = [
                    'icon' => '📦',
                    'title' => __('general.order_ttn_created', ['ttn' => $shipment->ttn]),
                    'datetime' => $shipment->created_at,
                    'done' => true,
                ];
            }
            foreach ($shipment->tracking_history ?? [] as $entry) {
                $events[] = [
                    'icon' => $this->iconForCode($entry['status_code'] ?? null),
                    'title' => $entry['status'] ?? '',
                    'datetime' => isset($entry['date']) ? \Carbon\Carbon::parse($entry['date']) : null,
                    'done' => true,
                ];
            }

            if ($shipment->status !== \App\Models\NpShipment::STATUS_DELIVERED) {
                $events[] = [
                    'icon' => '✅',
                    'title' => __('general.order_to_be_received'),
                    'datetime' => $shipment->estimated_delivery_date,
                    'done' => false,
                ];
            }
        }

        return $events;
    }

    private function iconForCode(?string $code): string
    {
        return match (true) {
            in_array($code, ['1', '2']) => '📦',
            in_array($code, ['4', '5', '6']) => '🚚',
            in_array($code, ['7', '8']) => '🏪',
            in_array($code, ['9', '10', '11']) => '✅',
            in_array($code, ['14', '102', '103', '108']) => '↩️',
            default => '•',
        };
    }
}
