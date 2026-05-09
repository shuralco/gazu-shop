<?php

namespace App\Livewire\Payment;

use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class PaymentMethodSelector extends Component
{
    public int $orderId;

    public string $selectedGateway = 'liqpay';

    public array $gatewayOptions = [];

    public bool $loading = false;

    public ?string $error = null;

    public array $availableGateways = [];

    public function mount(Order $order)
    {
        $this->orderId = $order->id;

        // Якщо замовлення вже має обраний метод оплати, використовуємо його
        if ($order->payment_method) {
            $this->selectedGateway = $order->payment_method;
        }

        $this->gatewayOptions = app(PaymentService::class)->getAvailableGateways($order);
        $this->availableGateways = $this->gatewayOptions; // для сумісності з template

        // Якщо метод не встановлений або недоступний, беремо перший доступний
        if (empty($this->selectedGateway) || ! isset($this->gatewayOptions[$this->selectedGateway])) {
            if (! empty($this->gatewayOptions)) {
                $this->selectedGateway = array_key_first($this->gatewayOptions);
            }
        }
    }

    public function selectGateway(string $gateway)
    {
        if (isset($this->gatewayOptions[$gateway])) {
            $this->selectedGateway = $gateway;
            $this->dispatch('gateway-selected', $gateway);
        }
    }

    public function processPayment()
    {
        $this->validate([
            'selectedGateway' => 'required|in:'.implode(',', array_keys($this->gatewayOptions)),
        ]);

        $this->loading = true;
        $this->error = null;

        try {
            $order = Order::findOrFail($this->orderId);

            Log::info('Processing payment', [
                'order_id' => $order->id,
                'gateway' => $this->selectedGateway,
                'amount' => $order->total,
            ]);

            $response = app(PaymentService::class)->createPayment(
                $order,
                $this->selectedGateway
            );

            Log::info('Payment response received', [
                'status' => $response->status,
                'isRedirect' => $response->isRedirect(),
                'isFormSubmit' => $response->isFormSubmit(),
                'form_action' => $response->form_action ?? null,
            ]);

            if ($response->isRedirect()) {
                Log::info('Redirecting to payment gateway', [
                    'url' => $response->redirect_url,
                ]);

                return redirect($response->redirect_url);
            } elseif ($response->isFormSubmit()) {
                // Для форм-based платежів (LiqPay, WayForPay)
                // Відправляємо подію з даними форми для JavaScript
                Log::info('Dispatching submit-payment-form event', [
                    'form_action' => $response->form_action,
                    'form_data_keys' => array_keys($response->form_data),
                ]);

                $this->dispatch('submit-payment-form',
                    formData: $response->form_data,
                    formAction: $response->form_action
                );
                $this->loading = false;
            } else {
                throw new \Exception('Невідомий тип відповіді від платіжного шлюзу');
            }

        } catch (\Exception $e) {
            $order = Order::find($this->orderId);
            Log::error('Payment creation failed', [
                'order_id' => $order?->id ?? $this->orderId,
                'gateway' => $this->selectedGateway,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->error = 'Помилка створення платежу: '.$e->getMessage();
            $this->loading = false;
        }
    }

    public function render()
    {
        $order = Order::findOrFail($this->orderId);

        return view('livewire.payment.payment-method-selector', [
            'order' => $order,
        ]);
    }
}
