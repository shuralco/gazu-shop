<div>
    @section('metatags')
        <title>{{ shopName() . ' :: ' . __('general.order_success_title') }}</title>
        <meta name="description" content="{{ __('general.order_success_meta') }}">
    @endsection

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">

    <main style="padding-top: 120px; padding-bottom: 64px;">
    <style>
        @media (min-width: 768px) {
            main {
                padding-top: 95px !important;
            }
        }
    </style>

        <!-- Breadcrumbs -->
        <div class="max-w-screen-2xl mx-auto px-4 md:px-8 py-4">
            <nav class="flex items-center gap-2 text-sm font-medium">
                <a wire:navigate href="{{ locale_route('home') }}" class="hover:underline font-bold">{{ __('general.home') }}</a>
                <span class="text-black font-black">/</span>
                <span class="font-black text-black uppercase">{{ __('general.order_success_breadcrumb') }}</span>
            </nav>
        </div>
        <div class="max-w-screen-xl mx-auto px-4 md:px-8">

            <!-- Success Animation -->
            <div class="text-center mb-16 slide-in">
                <div class="success-icon w-32 h-32 md:w-48 md:h-48 border-8 border-black mx-auto mb-8 flex items-center justify-center bg-white relative">
                    <span class="text-6xl md:text-8xl text-white relative z-10">✓</span>
                </div>
                <h1 class="text-3xl md:text-5xl lg:text-6xl font-black text-black mb-6 uppercase">{{ __('general.order_success_heading') }}</h1>
                <div class="bg-black text-white px-6 py-4 inline-flex items-center gap-4 mb-4">
                    <p class="text-base md:text-lg lg:text-xl font-black">
                        {{ __('general.order_number') }} #SH-{{ date('Y') }}-{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}
                    </p>
                    <button id="copyBtn" class="bg-white text-black px-4 py-2 font-bold text-sm hover:bg-gray-100 transition-colors" onclick="copyOrderNumber()">{{ __('general.copy') }}</button>
                </div>
                @if($order->email)
                <p class="text-base md:text-lg font-medium text-black">
                    {{ __('general.confirmation_sent_to', ['email' => $order->email]) }}
                </p>
                @endif
            </div>

            <!-- Order Status Progress -->
            <div class="mb-12 slide-in slide-in-delay-1">
                <h2 class="text-2xl md:text-3xl font-black text-black mb-6 text-center uppercase">{{ __('general.order_status_title') }}</h2>
                <div class="bg-white border-4 border-black p-6 md:p-8">
                    <div class="flex items-center justify-between mb-6 relative">
                        <!-- Progress Line -->
                        <div class="absolute top-4 left-0 right-0 h-1 bg-gray-300 z-0" style="margin: 0 40px;"></div>
                        <div class="absolute top-4 left-0 h-1 bg-black z-0" style="width: 25%; margin-left: 40px;"></div>

                        <!-- Steps -->
                        <div class="relative z-10 text-center flex-1">
                            <div class="w-8 h-8 bg-black border-2 border-black mx-auto mb-2"></div>
                            <p class="text-xs md:text-sm font-bold uppercase">{{ __('general.order_step_placed') }}</p>
                        </div>
                        <div class="relative z-10 text-center flex-1">
                            <div class="w-8 h-8 @if($order->status >= 1) bg-black @else bg-white @endif border-2 border-black mx-auto mb-2"></div>
                            <p class="text-xs md:text-sm font-bold uppercase">{{ __('general.order_step_processing') }}</p>
                        </div>
                        <div class="relative z-10 text-center flex-1">
                            <div class="w-8 h-8 @if($order->status >= 2) bg-black @else bg-white @endif border-2 border-black mx-auto mb-2"></div>
                            <p class="text-xs md:text-sm font-bold uppercase">{{ __('general.order_step_shipping') }}</p>
                        </div>
                        <div class="relative z-10 text-center flex-1">
                            <div class="w-8 h-8 @if($order->status >= 3) bg-black @else bg-white @endif border-2 border-black mx-auto mb-2"></div>
                            <p class="text-xs md:text-sm font-bold uppercase">{{ __('general.order_step_received') }}</p>
                        </div>
                    </div>
                    <div class="text-center">
                        <button class="bg-black text-white px-6 py-2 font-bold text-sm uppercase">
                            @if($order->status == 0)
                                {{ __('general.order_awaiting_processing') }}
                            @elseif($order->status == 1)
                                {{ __('general.order_in_processing') }}
                            @elseif($order->status == 2)
                                {{ __('general.order_shipping_in_progress') }}
                            @elseif($order->status == 3)
                                {{ __('general.order_delivered') }}
                            @else
                                {{ __('general.order_unknown_status') }}
                            @endif
                        </button>
                    </div>
                </div>
            </div>

            <!-- Order Details -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">

                <!-- Order Items -->
                <div class="slide-in slide-in-delay-2">
                    <h2 class="text-xl md:text-2xl font-black text-black mb-6 uppercase">{{ __('general.your_order_items') }}</h2>
                    <div class="info-box">
                        @foreach($order->orderProducts as $item)
                        <div class="order-item">
                            <div class="flex items-center gap-4">
                                <div class="w-16 h-16 bg-gray-100 border-2 border-black flex items-center justify-center">
                                    @if($item->image)
                                        <img src="{{ asset($item->getImageUrl()) }}" alt="{{ $item->title }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full bg-gray-200"></div>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-lg font-bold text-black">{{ $item->title }}</h3>
                                    <p class="text-gray-600">{{ __('general.quantity_pcs', ['qty' => $item->quantity]) }}</p>
                                </div>
                                <p class="text-xl font-black text-black">{{ formatPrice($item->price * $item->quantity) }}</p>
                            </div>
                        </div>
                        @endforeach

                        <div class="border-t-4 border-black pt-4 mt-4">
                            <div class="flex justify-between mb-2">
                                <span class="text-lg">{{ __('general.items_label') }}</span>
                                <span class="text-lg font-bold">{{ formatPrice($order->total - $order->shipping_cost + ($order->discount_amount ?? 0)) }}</span>
                            </div>
                            @if($order->shipping_cost > 0)
                            <div class="flex justify-between mb-2">
                                <span class="text-lg">{{ __('general.delivery_label') }}</span>
                                <span class="text-lg font-bold">{{ formatPrice($order->shipping_cost) }}</span>
                            </div>
                            @endif
                            @if($order->discount_amount > 0)
                            <div class="flex justify-between mb-2 text-red-600">
                                <span class="text-lg">{{ __('general.discount_label') }}</span>
                                <span class="text-lg font-bold">-{{ formatPrice($order->discount_amount) }}</span>
                            </div>
                            @endif
                            <div class="flex justify-between text-2xl font-black border-t-2 border-black pt-3">
                                <span>{{ __('general.grand_total') }}</span>
                                <span>{{ formatPrice($order->total) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Delivery & Customer Info -->
                <div class="slide-in slide-in-delay-3">
                    <h2 class="text-xl md:text-2xl font-black text-black mb-6 uppercase">{{ __('general.delivery_details') }}</h2>
                    <div class="info-box mb-8">
                        <div class="mb-6">
                            <h3 class="text-lg font-bold text-black mb-3">{{ __('general.delivery_address_label') }}</h3>
                            <div>
                                <p class="text-base font-medium text-black mb-1">{{ $order->name }}</p>
                                @if($order->phone)
                                    <p class="text-gray-600 mb-1">{{ $order->phone }}</p>
                                @endif
                                @if($order->email)
                                    <p class="text-gray-600 mb-1">{{ $order->email }}</p>
                                @endif

                                @if($order->shipping_provider === 'novaposhta')
                                    @php
                                        $npData = is_array($order->shipping_data)
                                            ? $order->shipping_data
                                            : (json_decode($order->shipping_data ?? '{}', true) ?: []);
                                        $npCityDisplay = $order->shipping_city ?: ($npData['city_name'] ?? null);
                                        $npWarehouseDisplay = $order->shipping_warehouse
                                            ?: ($order->shipping_post_office
                                                ?: ($npData['warehouse_number']
                                                    ?? ($npData['warehouse']
                                                        ?? ($npData['postomat']
                                                            ?? ($npData['postomat_number'] ?? null)))));
                                    @endphp
                                    <p class="text-black font-medium mt-3">
                                        {{ __('general.nova_poshta_short') }}
                                        @if($order->shipping_method === 'warehouse')
                                            - {{ __('general.warehouse_short') }}
                                        @elseif($order->shipping_method === 'courier')
                                            - {{ __('general.courier_short') }}
                                        @elseif($order->shipping_method === 'postomat')
                                            - {{ __('general.postomat_short') }}
                                        @endif
                                    </p>
                                    @if($npCityDisplay)
                                        <p class="text-gray-600">{{ $npCityDisplay }}</p>
                                    @endif
                                    @if($npWarehouseDisplay)
                                        <p class="text-gray-600">{{ $npWarehouseDisplay }}</p>
                                    @endif
                                    @if($order->shipping_address)
                                        <p class="text-gray-600">{{ $order->shipping_address }}</p>
                                    @endif
                                @elseif($order->shipping_provider === 'ukrposhta')
                                    @php
                                        $shippingData = json_decode($order->shipping_data, true) ?? [];
                                    @endphp
                                    <p class="text-black font-medium mt-3">
                                        {{ __('general.ukrposhta_short') }}
                                        @if($order->shipping_method === 'branch')
                                            - {{ __('general.warehouse_short') }}
                                        @elseif($order->shipping_method === 'courier')
                                            - {{ __('general.courier_short') }}
                                        @endif
                                    </p>
                                    @if($shippingData['city_name'] ?? $order->shipping_city)
                                        <p class="text-gray-600">{{ $shippingData['city_name'] ?? $order->shipping_city }}</p>
                                    @endif
                                    @if($shippingData['branch_name'] ?? $order->shipping_post_office)
                                        <p class="text-gray-600">{{ $shippingData['branch_name'] ?? $order->shipping_post_office }}</p>
                                    @endif
                                    @if($order->shipping_address)
                                        <p class="text-gray-600">{{ $order->shipping_address }}</p>
                                    @endif
                                @elseif($order->shipping_provider === 'pickup')
                                    <p class="text-black font-medium mt-3">
                                        {{ __('general.pickup_from_store') }}
                                    </p>
                                    <p class="text-gray-600">{{ __('general.shop_address_value') }}</p>
                                    <p class="text-gray-600">{{ __('general.shop_schedule') }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="border-t-2 border-black pt-6">
                            <h3 class="text-lg font-bold text-black mb-3">{{ __('general.payment_method_short') }}</h3>
                            <div>
                                @if($order->payment_method === 'cash')
                                    <p class="text-lg font-medium">{{ __('general.payment_cash_on_delivery') }}</p>
                                @elseif($order->payment_method === 'bank_transfer')
                                    <p class="text-lg font-medium">{{ __('general.payment_bank_transfer') }}</p>
                                @elseif($order->payment_method === 'privat24')
                                    <p class="text-lg font-medium">{{ __('general.privat24') }}</p>
                                @elseif($order->payment_method === 'monobank')
                                    <p class="text-lg font-medium">{{ __('general.monobank') }}</p>
                                @elseif($order->payment_method === 'liqpay')
                                    <p class="text-lg font-medium">LiqPay</p>
                                @elseif($order->payment_method === 'wayforpay')
                                    <p class="text-lg font-medium">WayForPay</p>
                                @else
                                    <p class="text-lg font-medium">{{ $order->payment_method }}</p>
                                @endif

                                @if($order->payment_status === 'paid')
                                    <span class="inline-block mt-2 bg-green-600 text-white px-3 py-1 text-sm font-bold">{{ __('general.payment_paid') }}</span>
                                @elseif($order->payment_status === 'pending')
                                    <span class="inline-block mt-2 bg-yellow-600 text-white px-3 py-1 text-sm font-bold">{{ __('general.payment_awaiting') }}</span>
                                @endif
                            </div>
                        </div>

                        @if($order->note)
                        <div class="border-t-2 border-black pt-6">
                            <h3 class="text-lg font-bold text-black mb-3">{{ __('general.order_comment_short') }}</h3>
                            <div>
                                <p class="text-gray-600">{{ $order->note }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="text-center mb-16 slide-in slide-in-delay-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 max-w-4xl mx-auto">
                    <a href="{{ locale_route('home') }}" class="btn-white w-full">{{ __('general.continue_shopping') }}</a>
                    @auth
                        <a href="{{ locale_route('orders-show', $order->id) }}" class="btn-black w-full">{{ __('general.go_to_cabinet') }}</a>
                    @else
                        <a href="{{ locale_route('login') }}" class="btn-black w-full">{{ __('general.sign_in_cabinet') }}</a>
                    @endauth
                    <button class="btn-white w-full" onclick="window.print()">{{ __('general.download_receipt') }}</button>
                </div>
            </div>

            <!-- Help Section -->
            <div class="border-4 border-black p-8 text-center">
                <h3 class="text-2xl font-black text-black mb-4">{{ __('general.need_help') }}</h3>
                <p class="text-lg mb-6">
                    {{ __('general.order_questions_text') }}
                </p>
                <div class="flex flex-col md:flex-row gap-8 justify-center">
                    <div>
                        <p class="font-bold mb-1">{{ __('general.support_phone') }}</p>
                        <p class="text-lg font-black">+380 (44) 123-45-67</p>
                    </div>
                    <div>
                        <p class="font-bold mb-1">{{ __('general.support_email') }}</p>
                        <p class="text-lg font-black">support@simpleshop.ua</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
/* General Styles */
* {
    border-radius: 0 !important;
}

body {
    font-family: 'Inter', sans-serif;
}

/* Button Styles */
.btn-black {
    background: black;
    color: white;
    border: 2px solid black;
    padding: 16px 32px;
    font-weight: 700;
    font-size: 18px;
    text-transform: uppercase;
    text-align: center;
    transition: all 0.2s ease;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
}
.btn-black:hover {
    background: white;
    color: black;
    text-decoration: none;
}

.btn-white {
    background: white;
    color: black;
    border: 2px solid black;
    padding: 16px 32px;
    font-weight: 700;
    font-size: 18px;
    text-transform: uppercase;
    text-align: center;
    transition: all 0.2s ease;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
}
.btn-white:hover {
    background: black;
    color: white;
    text-decoration: none;
}

/* Success Icon Animation */
.success-icon {
    animation: successPulse 2s ease-in-out infinite;
    position: relative;
}

.success-icon::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    background: black;
    transform: translate(-50%, -50%);
    animation: squareGrow 1s ease-out 0.5s forwards;
}

.success-icon span {
    opacity: 0;
    animation: checkmarkAppear 0.5s ease-out 1.5s forwards;
}

@keyframes successPulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

@keyframes squareGrow {
    0% {
        width: 0;
        height: 0;
    }
    100% {
        width: calc(100% - 16px);
        height: calc(100% - 16px);
    }
}

@keyframes checkmarkAppear {
    0% {
        opacity: 0;
        transform: scale(0.5);
    }
    100% {
        opacity: 1;
        transform: scale(1);
    }
}

/* Slide In Animation */
.slide-in {
    opacity: 0;
    transform: translateY(30px);
    animation: slideIn 0.8s ease-out forwards;
}

.slide-in-delay-1 { animation-delay: 0.2s; }
.slide-in-delay-2 { animation-delay: 0.4s; }
.slide-in-delay-3 { animation-delay: 0.6s; }
.slide-in-delay-4 { animation-delay: 0.8s; }

@keyframes slideIn {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}



/* Order Items */
.order-item {
    border-bottom: 2px solid black;
    padding: 16px 0;
}

.order-item:last-child {
    border-bottom: none;
}

/* Timer */
.delivery-timer {
    font-family: 'Courier New', monospace;
    font-weight: 900;
    font-size: 24px;
    color: white;
}

/* Info Box */
.info-box {
    background: #f9f9f9;
    border: 2px solid black;
    padding: 24px;
}

/* Status Badge */
.status-badge {
    background: black;
    color: white;
    padding: 4px 12px;
    font-weight: 700;
    font-size: 12px;
    display: inline-block;
    text-transform: uppercase;
}
</style>
@endpush

@push('scripts')
<script>
// Copy order number to clipboard
function copyOrderNumber() {
    const orderNumber = '#SH-{{ date('Y') }}-{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}';
    const copyBtn = document.getElementById('copyBtn');

    if (orderNumber && navigator.clipboard) {
        navigator.clipboard.writeText(orderNumber).then(() => {
            const originalText = copyBtn.textContent;
            copyBtn.textContent = '{{ __('general.copied') }}';
            copyBtn.style.background = '#22c55e';
            copyBtn.style.color = 'white';

            setTimeout(() => {
                copyBtn.textContent = originalText;
                copyBtn.style.background = 'white';
                copyBtn.style.color = 'black';
            }, 2000);
        }).catch(() => {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = orderNumber;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);

            const originalText = copyBtn.textContent;
            copyBtn.textContent = '{{ __('general.copied') }}';
            copyBtn.style.background = '#22c55e';
            copyBtn.style.color = 'white';

            setTimeout(() => {
                copyBtn.textContent = originalText;
                copyBtn.style.background = 'white';
                copyBtn.style.color = 'black';
            }, 2000);
        });
    }
}

// Print order
function printOrder() {
    window.print();
}
</script>
@endpush