@extends('components.layouts.app')

@section('title', __('general.payment_success_page'))

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap" rel="stylesheet">
<style>
    * { border-radius: 0 !important; }
    body { font-family: 'Inter', sans-serif; }

    .btn-black {
        background: black;
        color: white;
        border: 2px solid black;
        padding: 16px 32px;
        font-weight: 700;
        font-size: 18px;
        transition: all 0.2s ease;
        cursor: pointer;
        display: inline-block;
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
        transition: all 0.2s ease;
        cursor: pointer;
        display: inline-block;
        text-decoration: none;
    }
    .btn-white:hover {
        background: black;
        color: white;
        text-decoration: none;
    }

    .success-icon {
        animation: successPulse 2s ease-in-out infinite;
    }

    @keyframes successPulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }

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

    .progress-step {
        position: relative;
        flex: 1;
        height: 8px;
        background: #e5e5e5;
        border: 2px solid black;
    }

    .progress-step.active {
        background: black;
    }

    .progress-step::after {
        content: '';
        position: absolute;
        top: -6px;
        right: -8px;
        width: 16px;
        height: 16px;
        background: black;
        border: 2px solid white;
        z-index: 2;
    }

    .progress-step.active::after {
        background: black;
    }

    .copy-btn {
        background: white;
        border: 2px solid black;
        color: black;
        padding: 8px 12px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s ease;
        margin-left: 12px;
    }

    .copy-btn:hover {
        background: black;
        color: white;
    }

    .copy-btn.copied {
        background: #22c55e;
        border-color: #22c55e;
        color: white;
    }

    .status-badge {
        background: black;
        color: white;
        padding: 4px 12px;
        font-weight: 700;
        font-size: 12px;
        display: inline-block;
    }

    .info-box {
        background: #f9f9f9;
        border: 2px solid black;
        padding: 24px;
    }
</style>

<main class="pt-32 md:pt-40 pb-16 bg-white">
    <div class="max-w-screen-lg mx-auto px-4 md:px-8">

        <!-- Success Header -->
        <div class="text-center mb-16 slide-in">
            <div class="success-icon w-32 h-32 md:w-48 md:h-48 border-8 border-black mx-auto mb-8 flex items-center justify-center bg-white">
                <span class="text-6xl md:text-8xl">✓</span>
            </div>
            <h1 class="text-4xl md:text-7xl font-black text-black mb-6">{{ __('general.order_success_heading') }}</h1>
            <div class="bg-black text-white p-6 inline-block mb-4">
                <p class="text-xl md:text-2xl font-black">
                    {{ __('general.order_number') }}
                    <span id="orderNumber">#{{ $order->id }}</span>
                    <button id="copyBtn" class="copy-btn ml-4">{{ __('general.copy') }}</button>
                </p>
            </div>
            <p class="text-lg md:text-xl font-medium text-black">
                {{ __('general.confirmation_sent_to', ['email' => $order->email]) }}
            </p>
        </div>

        <!-- Order Status Progress -->
        <div class="mb-16 slide-in slide-in-delay-1">
            <h2 class="text-2xl md:text-4xl font-black text-black mb-8 text-center">{{ __('general.order_status_title') }}</h2>
            <div class="bg-white border-4 border-black p-8">
                <div class="flex items-center justify-between mb-8">
                    <div class="text-center flex-1">
                        <div class="w-8 h-8 {{ $order->status == 'pending' || $order->status == 'paid' || $order->status == 'processing' || $order->status == 'shipped' || $order->status == 'delivered' ? 'bg-black' : 'bg-gray-300' }} border-2 border-white mx-auto mb-2"></div>
                        <p class="text-sm font-bold">{{ __('general.order_step_placed') }}</p>
                    </div>
                    <div class="progress-step {{ $order->status == 'paid' || $order->status == 'processing' || $order->status == 'shipped' || $order->status == 'delivered' ? 'active' : '' }}"></div>
                    <div class="text-center flex-1">
                        <div class="w-8 h-8 {{ $order->status == 'processing' || $order->status == 'shipped' || $order->status == 'delivered' ? 'bg-black' : 'bg-gray-300' }} border-2 border-black mx-auto mb-2"></div>
                        <p class="text-sm font-bold">{{ __('general.order_step_processing') }}</p>
                    </div>
                    <div class="progress-step {{ $order->status == 'shipped' || $order->status == 'delivered' ? 'active' : '' }}"></div>
                    <div class="text-center flex-1">
                        <div class="w-8 h-8 {{ $order->status == 'shipped' || $order->status == 'delivered' ? 'bg-black' : 'bg-gray-300' }} border-2 border-black mx-auto mb-2"></div>
                        <p class="text-sm font-bold">{{ __('general.order_step_shipping') }}</p>
                    </div>
                    <div class="progress-step {{ $order->status == 'delivered' ? 'active' : '' }}"></div>
                    <div class="text-center flex-1">
                        <div class="w-8 h-8 {{ $order->status == 'delivered' ? 'bg-black' : 'bg-gray-300' }} border-2 border-black mx-auto mb-2"></div>
                        <p class="text-sm font-bold">{{ __('general.order_step_received') }}</p>
                    </div>
                </div>
                <div class="text-center">
                    @switch($order->status)
                        @case('pending')
                            <span class="status-badge">{{ __('general.payment_awaiting') }}</span>
                            @break
                        @case('paid')
                            <span class="status-badge">{{ __('general.payment_paid') }}</span>
                            @break
                        @case('processing')
                            <span class="status-badge">{{ __('general.order_status_processing') }}</span>
                            @break
                        @case('shipped')
                            <span class="status-badge">{{ __('general.order_status_shipped') }}</span>
                            @break
                        @case('delivered')
                            <span class="status-badge">{{ __('general.order_status_delivered') }}</span>
                            @break
                        @default
                            <span class="status-badge">{{ strtoupper($order->status) }}</span>
                    @endswitch
                </div>
            </div>
        </div>

        <!-- Order Details -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-16">

            <!-- Order Info -->
            <div class="slide-in slide-in-delay-2">
                <h2 class="text-2xl md:text-3xl font-black text-black mb-8">{{ __('general.order_details_title') }}</h2>
                <div class="info-box">
                    <div class="space-y-4">
                        <div class="flex justify-between">
                            <span class="text-lg font-medium">{{ __('general.buyer_label') }}</span>
                            <span class="text-lg font-bold">{{ $order->name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-lg font-medium">{{ __('general.email_short') }}:</span>
                            <span class="text-lg font-bold">{{ $order->email }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-lg font-medium">{{ __('general.phone_short') }}</span>
                            <span class="text-lg font-bold">{{ $order->phone ?? __('general.not_specified') }}</span>
                        </div>
                        <div class="border-t-2 border-black pt-4">
                            <div class="flex justify-between">
                                <span class="text-2xl font-black">{{ __('general.total_label') }}</span>
                                <span class="text-2xl font-black">{{ formatPrice($order->total) }}</span>
                            </div>
                        </div>

                        @if($order->getTrackingNumber())
                            <div class="border-t-2 border-black pt-4">
                                <div class="flex justify-between items-center">
                                    <span class="text-lg font-medium">{{ __('general.tracking_number') }}</span>
                                    <span class="text-lg font-bold bg-gray-100 px-3 py-1 border border-black">{{ $order->getTrackingNumber() }}</span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Payment Status -->
            <div class="slide-in slide-in-delay-3">
                <h2 class="text-2xl md:text-3xl font-black text-black mb-8">{{ __('general.payment_status_title') }}</h2>

                @if($order->hasSuccessfulPayment())
                    <div class="info-box">
                        <div class="text-center mb-6">
                            <div class="w-16 h-16 bg-green-600 border-4 border-black mx-auto mb-4 flex items-center justify-center">
                                <span class="text-2xl text-white">💳</span>
                            </div>
                            <p class="text-xl font-black text-black">{{ __('general.payment_success_msg') }}</p>
                        </div>
                    </div>
                @else
                    <div class="info-box">
                        <div class="text-center mb-6">
                            <div class="w-16 h-16 bg-orange-500 border-4 border-black mx-auto mb-4 flex items-center justify-center">
                                <span class="text-2xl text-white">⏳</span>
                            </div>
                            <p class="text-xl font-black text-black">{{ __('general.awaiting_payment') }}</p>
                            <p class="text-sm text-gray-600 mt-2">{{ __('general.order_accepted') }}</p>
                        </div>
                    </div>
                @endif
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
                <button class="btn-white w-full" onclick="window.print()">{{ __('general.print_receipt') }}</button>
            </div>
        </div>

        <!-- Support Information -->
        <div class="bg-gray-100 border-4 border-black p-8 md:p-16 text-center slide-in slide-in-delay-4">
            <h2 class="text-3xl md:text-5xl font-black text-black mb-8">{{ __('general.need_help') }}</h2>
            <p class="text-lg md:text-xl font-medium text-black mb-8 max-w-2xl mx-auto">
                {{ __('general.support_ready_247') }}
            </p>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <div class="w-16 h-16 border-4 border-black mx-auto mb-4 flex items-center justify-center">
                        <span class="text-2xl">📞</span>
                    </div>
                    <h3 class="text-xl font-black text-black mb-2">{{ __('general.support_phone') }}</h3>
                    <p class="text-2xl font-black text-black">+380 (44) 123-45-67</p>
                    <p class="text-sm text-gray-600">{{ __('general.free_in_ukraine') }}</p>
                </div>
                <div>
                    <div class="w-16 h-16 border-4 border-black mx-auto mb-4 flex items-center justify-center">
                        <span class="text-2xl">💬</span>
                    </div>
                    <h3 class="text-xl font-black text-black mb-2">{{ __('general.chat_label') }}</h3>
                    <button class="btn-black">{{ __('general.open_chat') }}</button>
                    <p class="text-sm text-gray-600 mt-2">{{ __('general.ai_assistant_online') }}</p>
                </div>
                <div>
                    <div class="w-16 h-16 border-4 border-black mx-auto mb-4 flex items-center justify-center">
                        <span class="text-2xl">📧</span>
                    </div>
                    <h3 class="text-xl font-black text-black mb-2">{{ __('general.support_email') }}</h3>
                    <p class="text-lg font-bold text-black">support@simpleshop.ua</p>
                    <p class="text-sm text-gray-600">{{ __('general.reply_in_1_hour') }}</p>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    // Copy Order Number
    document.getElementById('copyBtn').addEventListener('click', function() {
        const orderNumber = document.getElementById('orderNumber').textContent;
        navigator.clipboard.writeText(orderNumber).then(() => {
            this.textContent = '{{ __('general.copied') }}';
            this.classList.add('copied');

            setTimeout(() => {
                this.textContent = '{{ __('general.copy') }}';
                this.classList.remove('copied');
            }, 2000);
        });
    });

    // Success message notification
    setTimeout(() => {
        const notification = document.createElement('div');
        notification.className = 'fixed top-4 right-4 bg-green-600 text-white px-6 py-3 font-bold z-50 border-2 border-black';
        notification.textContent = '{{ __('general.order_success_notification') }}';
        document.body.appendChild(notification);

        setTimeout(() => {
            notification.style.opacity = '0';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }, 1000);
</script>
@endsection