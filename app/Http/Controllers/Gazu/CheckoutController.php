<?php

namespace App\Http\Controllers\Gazu;

use App\Helpers\Cart\Cart;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Простий MVP-чекаут для GAZU. Створює Order з товарами з сесії.
 * Без оплати — статус pending. Платіжний flow підключається окремо.
 */
class CheckoutController extends Controller
{
    public function index()
    {
        $cart = Cart::getCart();
        if (empty($cart)) {
            return redirect()->route('gazu.cart.empty');
        }

        return view('gazu.checkout', [
            'cart' => $cart,
            'cartTotal' => Cart::getCartTotal(),
            'activeNav' => null,
            'cartCount' => Cart::getCartQuantityItems(),
        ]);
    }

    public function store(Request $request)
    {
        $cart = Cart::getCart();
        if (empty($cart)) {
            return redirect()->route('gazu.cart.empty');
        }

        $data = $request->validate([
            'first_name' => 'required|string|max:80',
            'last_name'  => 'nullable|string|max:80',
            'phone'      => 'required|string|max:30',
            'email'      => 'nullable|email|max:120',
            'shipping_method' => 'nullable|string|max:60',
            'shipping_city' => 'nullable|string|max:120',
            'shipping_city_ref' => 'nullable|string|max:60',
            // НП — відділення/поштомат
            'shipping_warehouse' => 'nullable|string|max:200',
            'shipping_warehouse_ref' => 'nullable|string|max:60',
            'shipping_warehouse_type' => 'nullable|in:branch,postomat,np_courier',
            // НП — Курʼєр НП (street + опційні поля)
            'shipping_street' => 'nullable|string|max:200',
            'shipping_street_ref' => 'nullable|string|max:60',
            'shipping_house' => 'nullable|string|max:30',
            'shipping_apartment' => 'nullable|string|max:30',
            'shipping_floor' => 'nullable|integer|min:1|max:50',
            'shipping_has_elevator' => 'nullable|boolean',
            'shipping_preferred_date' => 'nullable|date|after_or_equal:today',
            'shipping_preferred_time' => 'nullable|string|max:30',
            // УкрПошта
            'shipping_postcode' => 'nullable|string|max:10',
            'shipping_address' => 'nullable|string|max:300',
            'shipping_up_city' => 'nullable|string|max:120',
            'shipping_up_city_id' => 'nullable|string|max:30',
            'shipping_up_office' => 'nullable|string|max:200',
            'shipping_up_office_id' => 'nullable|string|max:30',
            'payment_method' => 'nullable|string|max:60',
            'note'       => 'nullable|string|max:1000',
        ]);

        // Compose full address depending on method + НП-кур'єр subtype
        $method = $data['shipping_method'] ?? 'novaposhta';
        $type = $data['shipping_warehouse_type'] ?? null;

        if ($method === 'novaposhta' && $type === 'np_courier') {
            $parts = array_filter([
                $data['shipping_street'] ?? null,
                ! empty($data['shipping_house']) ? 'буд. '.$data['shipping_house'] : null,
                ! empty($data['shipping_apartment']) ? 'кв. '.$data['shipping_apartment'] : null,
                ! empty($data['shipping_floor']) ? 'пов. '.$data['shipping_floor'] : null,
            ]);
            $data['shipping_address'] = implode(', ', $parts) ?: null;
            $extras = array_filter([
                ! empty($data['shipping_has_elevator']) ? 'є ліфт' : null,
                ! empty($data['shipping_preferred_date']) ? 'дата: '.$data['shipping_preferred_date'] : null,
                ! empty($data['shipping_preferred_time']) ? 'час: '.$data['shipping_preferred_time'] : null,
            ]);
            if (! empty($extras)) {
                $data['note'] = trim(($data['note'] ?? '')."\nКурʼєр НП — ".implode(', ', $extras));
            }
        } elseif ($method === 'ukrposhta') {
            // Якщо обрано міста+відділення з autocomplete — використовуємо їх
            if (! empty($data['shipping_up_city'])) {
                $data['shipping_city'] = $data['shipping_up_city'];
            }
            if (! empty($data['shipping_up_office'])) {
                $data['shipping_warehouse'] = $data['shipping_up_office'];
            }
            $data['shipping_address'] = trim(($data['shipping_postcode'] ?? '').' '.($data['shipping_address'] ?? '')) ?: ($data['shipping_up_office'] ?? null);
        }

        $shippingBreakdown = app(\App\Services\Cart\ShippingCalculator::class)->breakdown($cart);
        $subtotal = (float) $shippingBreakdown['subtotal'];
        $shippingCost = (float) $shippingBreakdown['shipping_total'];
        $total = (float) $shippingBreakdown['grand_total'];

        try {
            $order = DB::transaction(function () use ($cart, $data, $subtotal, $shippingCost, $total) {
            $orderData = [
                'user_id'    => auth()->id(),
                'first_name' => $data['first_name'],
                'last_name'  => $data['last_name'] ?? null,
                'phone'      => $data['phone'],
                'email'      => $data['email'] ?? null,
                'locale'     => app()->getLocale() ?: 'uk',
                'status'     => 'pending',
                'subtotal'   => $subtotal,
                'total'      => $total,
                'shipping_method' => $data['shipping_method'] ?? 'novaposhta',
                'shipping_city'   => $data['shipping_city'] ?? null,
                'shipping_city_ref' => $data['shipping_city_ref'] ?? null,
                'shipping_warehouse' => $data['shipping_warehouse'] ?? null,
                'shipping_warehouse_ref' => $data['shipping_warehouse_ref'] ?? null,
                'shipping_warehouse_type' => $data['shipping_warehouse_type'] ?? null,
                'shipping_address' => $data['shipping_address'] ?? null,
                'shipping_postcode' => $data['shipping_postcode'] ?? null,
                // Додаткові поля для НП Кур'єра і УП у JSON
                'shipping_data' => array_filter([
                    'street' => $data['shipping_street'] ?? null,
                    'street_ref' => $data['shipping_street_ref'] ?? null,
                    'house' => $data['shipping_house'] ?? null,
                    'apartment' => $data['shipping_apartment'] ?? null,
                    'floor' => $data['shipping_floor'] ?? null,
                    'has_elevator' => isset($data['shipping_has_elevator']) ? (bool) $data['shipping_has_elevator'] : null,
                    'preferred_date' => $data['shipping_preferred_date'] ?? null,
                    'preferred_time' => $data['shipping_preferred_time'] ?? null,
                ], fn ($v) => $v !== null && $v !== ''),
                'payment_method'  => $data['payment_method'] ?? 'card',
                'payment_status'  => 'pending',
                'note'       => $data['note'] ?? null,
                'discount_amount' => 0,
                'shipping_cost'   => $shippingCost,
                'fulfillment_status' => 'pending',
            ];

            // Тільки колонки, які реально існують у БД (унікальна для legacy/новий схема).
            $orderData = array_filter(
                $orderData,
                fn ($_, $col) => \Schema::hasColumn('orders', $col),
                ARRAY_FILTER_USE_BOTH
            );

            $order = Order::create($orderData);

            $invService = app(\App\Services\Warehouse\InventoryService::class);

            foreach ($cart as $key => $item) {
                $productId = is_numeric($key) ? (int) $key : (int) explode('_', $key)[0];
                $whId = isset($item['warehouse_id']) ? (int) $item['warehouse_id'] : null;
                $qty = (int) ($item['quantity'] ?? 1);

                OrderProduct::create([
                    'order_id'     => $order->id,
                    'product_id'   => $productId,
                    'warehouse_id' => $whId,
                    'title'        => is_array($item['title'] ?? null) ? ($item['title']['uk'] ?? json_encode($item['title'])) : ($item['title'] ?? 'Товар'),
                    'price'        => (float) ($item['price'] ?? 0),
                    'quantity'     => $qty,
                    'image'        => $item['image'] ?? null,
                    'slug'         => is_array($item['slug'] ?? null) ? ($item['slug']['uk'] ?? null) : ($item['slug'] ?? null),
                ]);

                // Reserve inventory inside the same transaction; throws on out-of-stock.
                if ($whId) {
                    $product = \App\Models\Product::find($productId);
                    $warehouse = \App\Models\MerchantWarehouse::find($whId);
                    if ($product && $warehouse) {
                        $invService->reserve(
                            $product, $warehouse, $qty,
                            $order, auth()->id(), 'Reserve on order #'.$order->id,
                        );
                    }
                }
            }

            return $order;
            });
        } catch (\RuntimeException $e) {
            // InventoryService::reserve() throws when stock is insufficient.
            // Surface it as a friendly cart-back redirect with the line at fault.
            if (str_contains($e->getMessage(), 'Cannot reserve') || str_contains($e->getMessage(), 'available')) {
                $line = $this->stockFailureLine($e->getMessage(), $cart);
                return redirect()->route('gazu.cart')
                    ->withInput()
                    ->withErrors([
                        'stock' => $line
                            ? 'На складі «'.$line.'» вже не вистачає тієї кількості. Будь ласка, оновіть кошик.'
                            : 'Деяких товарів на обраному складі не вистачає. Оновіть кошик і спробуйте знову.',
                    ]);
            }
            throw $e;
        }

        Cart::clearCart();
        $this->sendOrderEmails($order);

        return redirect()->route('gazu.checkout.success', ['order' => $order->id])
            ->with('order_message', 'Замовлення створено. Менеджер передзвонить.');
    }

    /**
     * Best-effort: extract a human warehouse-city label from a reserve()
     * RuntimeException message of the form "...at warehouse {id}...".
     */
    private function stockFailureLine(string $message, array $cart): ?string
    {
        if (preg_match('/warehouse\s+(\d+)/', $message, $m)) {
            $wh = \App\Models\MerchantWarehouse::find((int) $m[1]);
            return $wh?->city ?: $wh?->name;
        }
        return null;
    }

    private function sendOrderEmails(Order $order): void
    {
        // Захищено від помилок — email не повинен ламати checkout flow.
        try {
            // Клієнту
            if (! empty($order->email)) {
                \Mail::to($order->email)->queue(new \App\Mail\OrderConfirmation($order));
            }
            // Адміну (якщо є client/manager mailable)
            $adminEmail = \App\Models\DisplaySetting::get('email_admin_address')
                ?? config('mail.from.address');
            if (! empty($adminEmail) && class_exists(\App\Mail\OrderManager::class)) {
                \Mail::to($adminEmail)->queue(new \App\Mail\OrderManager($order));
            }
        } catch (\Throwable $e) {
            \Log::warning('Order email failed for #'.$order->id.': '.$e->getMessage());
        }
    }

    public function success(int $order)
    {
        $orderModel = Order::with('orderProducts')->find($order);
        if (! $orderModel) abort(404);

        return view('gazu.checkout-success', [
            'order' => $orderModel,
            'activeNav' => null,
        ]);
    }

    /**
     * 1-клік замовлення: тільки телефон + продукт. Решту менеджер уточнить дзвінком.
     */
    public function oneClick(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'phone'      => 'required|string|max:30',
            'quantity'   => 'integer|min:1',
        ]);

        $product = \App\Models\Product::findOrFail($data['product_id']);
        $qty = (int) ($data['quantity'] ?? 1);
        $price = (float) $product->price;
        $total = $price * $qty;

        $orderData = [
            'user_id'    => auth()->id(),
            'first_name' => auth()->user()?->name ?: 'Гість 1-клік',
            'phone'      => $data['phone'],
            'email'      => auth()->user()?->email,
            'locale'     => app()->getLocale() ?: 'uk',
            'status'     => 'pending',
            'total'      => $total,
            'shipping_method' => 'manager_call',
            'payment_method'  => 'manager_call',
            'payment_status'  => 'pending',
            'note'       => '1-клік: менеджер передзвонить для уточнення',
            'discount_amount' => 0,
            'shipping_cost'   => 0,
            'fulfillment_status' => 'pending',
        ];
        $orderData = array_filter($orderData, fn ($_, $col) => \Schema::hasColumn('orders', $col), ARRAY_FILTER_USE_BOTH);

        $order = DB::transaction(function () use ($orderData, $product, $qty, $price) {
            $o = Order::create($orderData);
            \App\Models\OrderProduct::create([
                'order_id'   => $o->id,
                'product_id' => $product->id,
                'title'      => is_array($product->title) ? ($product->title['uk'] ?? $product->name) : ($product->title ?? $product->name),
                'price'      => $price,
                'quantity'   => $qty,
                'image'      => $product->image,
                'slug'       => $product->getLocalizedSlug('uk') ?? null,
            ]);
            return $o;
        });

        $this->sendOrderEmails($order);

        return redirect()->route('gazu.checkout.success', ['order' => $order->id])
            ->with('order_message', 'Замовлення №'.$order->id.' прийнято. Менеджер передзвонить за '.$data['phone']);
    }
}
