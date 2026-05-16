@extends('gazu.layout')
@section('title', 'Замовлення оформлено — GAZU')

@php
    $methodLabels = ['novaposhta' => 'Нова Пошта', 'ukrposhta' => 'УкрПошта', 'pickup' => 'Самовивіз з магазину'];
    $typeLabels = ['branch' => 'Відділення', 'postomat' => 'Поштомат', 'np_courier' => 'Курʼєр НП'];
    $paymentLabels = ['card' => 'Картою онлайн', 'applepay' => 'Apple Pay / Google Pay', 'cod' => 'Накладений платіж', 'invoice' => 'Рахунок (гуртом)'];
    $shippingData = is_array($order->shipping_data) ? $order->shipping_data : (json_decode($order->shipping_data ?? '[]', true) ?: []);
    $shippingMethod = $methodLabels[$order->shipping_method ?? ''] ?? ucfirst($order->shipping_method ?? '—');
    $shippingType = $typeLabels[$order->shipping_warehouse_type ?? ''] ?? null;
    $items = $order->orderProducts ?? collect();
@endphp

@section('content')
<div class="gazu-container pt-6">
    {{-- Multi-step progress indicator — final step --}}
    <nav aria-label="Прогрес замовлення" class="mb-7 max-w-3xl mx-auto">
        <ol class="flex items-center gap-2 sm:gap-4 text-sm overflow-x-auto">
            <li class="flex items-center gap-2 shrink-0">
                <span class="w-8 h-8 rounded-full bg-[var(--gazu-success)] text-white flex items-center justify-center font-bold">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg>
                </span>
                <span class="text-[var(--gazu-graphite)]">Кошик</span>
            </li>
            <li class="flex-1 h-0.5 bg-[var(--gazu-success)] min-w-[24px]"></li>
            <li class="flex items-center gap-2 shrink-0">
                <span class="w-8 h-8 rounded-full bg-[var(--gazu-success)] text-white flex items-center justify-center font-bold">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg>
                </span>
                <span class="text-[var(--gazu-graphite)]">Оформлення</span>
            </li>
            <li class="flex-1 h-0.5 bg-[var(--gazu-success)] min-w-[24px]"></li>
            <li class="flex items-center gap-2 shrink-0">
                <span class="w-8 h-8 rounded-full bg-[var(--gazu-ink)] text-white flex items-center justify-center font-bold">3</span>
                <span class="text-[var(--gazu-ink)] font-medium">Готово</span>
            </li>
        </ol>
    </nav>
</div>
<div class="gazu-container pb-12">
    <div class="max-w-3xl mx-auto bg-white border border-[var(--gazu-line)] rounded-xl p-8">
        <div class="text-center mb-6">
            <div class="inline-flex w-20 h-20 bg-[var(--gazu-success-bg)] rounded-full items-center justify-center mb-5">
                <x-gazu.icon name="check" size="40" stroke="var(--gazu-success)"/>
            </div>
            <h1 class="gazu-display text-3xl font-semibold text-[var(--gazu-ink)] m-0 mb-2">
                Замовлення №{{ $order->id }} оформлено
            </h1>
            <p class="text-sm text-[var(--gazu-graphite)] max-w-md mx-auto">
                Дякуємо! Менеджер передзвонить за {{ $order->phone ?? 'вказаним номером' }} протягом 30 хвилин для уточнення доставки.
            </p>
        </div>

        <div class="grid md:grid-cols-2 gap-4 mb-6">
            {{-- Контакт --}}
            <div class="bg-[var(--gazu-paper)] rounded-lg p-4">
                <div class="text-xs uppercase text-[var(--gazu-graphite)] tracking-wider mb-3 font-semibold">Контакт</div>
                <div class="space-y-1.5 text-sm">
                    <div><span class="text-[var(--gazu-graphite)]">Покупець:</span> <span class="font-medium">{{ $order->name ?: $order->first_name }}</span></div>
                    <div><span class="text-[var(--gazu-graphite)]">Телефон:</span> <span class="gazu-mono font-medium">{{ $order->phone }}</span></div>
                    @if($order->email)
                        <div><span class="text-[var(--gazu-graphite)]">Email:</span> <span class="gazu-mono">{{ $order->email }}</span></div>
                    @endif
                </div>
            </div>

            {{-- Доставка --}}
            <div class="bg-[var(--gazu-paper)] rounded-lg p-4">
                <div class="text-xs uppercase text-[var(--gazu-graphite)] tracking-wider mb-3 font-semibold">Доставка</div>
                <div class="space-y-1.5 text-sm">
                    <div class="font-medium text-[var(--gazu-ink)]">
                        {{ $shippingMethod }}
                        @if($shippingType) · <span class="text-[var(--gazu-blue)]">{{ $shippingType }}</span>@endif
                    </div>
                    @if($order->shipping_city)
                        <div><span class="text-[var(--gazu-graphite)]">Місто:</span> {{ $order->shipping_city }}</div>
                    @endif
                    @if($order->shipping_warehouse)
                        <div class="text-[13px] text-[var(--gazu-graphite)]">{{ $order->shipping_warehouse }}</div>
                    @elseif($order->shipping_address)
                        <div class="text-[13px] text-[var(--gazu-graphite)]">{{ $order->shipping_address }}</div>
                    @endif
                    @if(! empty($shippingData['preferred_date']) || ! empty($shippingData['preferred_time']))
                        <div class="text-[12px] text-[var(--gazu-graphite)] mt-2 pt-2 border-t border-[var(--gazu-line)]">
                            <x-gazu.icon name="clock" size="11"/>
                            @if(! empty($shippingData['preferred_date'])) {{ \Illuminate\Support\Carbon::parse($shippingData['preferred_date'])->format('d.m.Y') }}@endif
                            @if(! empty($shippingData['preferred_time'])), {{ $shippingData['preferred_time'] }}@endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Товари --}}
        @if($items->count())
            <div class="mb-6">
                <div class="text-xs uppercase text-[var(--gazu-graphite)] tracking-wider mb-3 font-semibold">Товари ({{ $items->sum('quantity') }})</div>
                <div class="space-y-2">
                    @foreach($items as $item)
                        <div class="flex items-center gap-3 py-2 border-b border-[var(--gazu-line)] last:border-b-0">
                            <div class="flex-1 min-w-0">
                                <div class="text-sm text-[var(--gazu-ink)] truncate">{{ is_array($item->title) ? ($item->title['uk'] ?? '') : $item->title }}</div>
                                <div class="text-[11px] text-[var(--gazu-graphite)] gazu-mono">{{ $item->quantity }} × {{ number_format((float) $item->price, 0, '.', ' ') }} ₴</div>
                            </div>
                            <div class="gazu-display font-bold text-sm whitespace-nowrap">{{ number_format((float) $item->price * (int) $item->quantity, 0, '.', ' ') }} ₴</div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Підсумок + оплата --}}
        <div class="bg-[var(--gazu-paper)] rounded-lg p-5 mb-6">
            <div class="flex justify-between mb-2 text-sm">
                <span class="text-[var(--gazu-graphite)]">Спосіб оплати</span>
                <span class="font-medium">{{ $paymentLabels[$order->payment_method ?? ''] ?? ucfirst($order->payment_method ?? '—') }}</span>
            </div>
            @if((float) $order->shipping_cost > 0)
                <div class="flex justify-between mb-2 text-sm">
                    <span class="text-[var(--gazu-graphite)]">Доставка</span>
                    <span>{{ number_format((float) $order->shipping_cost, 0, '.', ' ') }} ₴</span>
                </div>
            @endif
            @if((float) $order->discount_amount > 0)
                <div class="flex justify-between mb-2 text-sm">
                    <span class="text-[var(--gazu-graphite)]">Знижка</span>
                    <span class="text-[var(--gazu-success)]">−{{ number_format((float) $order->discount_amount, 0, '.', ' ') }} ₴</span>
                </div>
            @endif
            <div class="h-px bg-[var(--gazu-line)] my-3"></div>
            <div class="flex justify-between items-baseline">
                <span class="font-medium text-[var(--gazu-ink)]">Усього</span>
                <span class="gazu-display text-3xl font-bold text-[var(--gazu-ink)]">{{ number_format((float) $order->total, 0, '.', ' ') }} ₴</span>
            </div>
        </div>

        @php
            $needsPayment = in_array($order->payment_method, ['card', 'applepay'], true)
                && $order->payment_status !== 'paid';
        @endphp

        <div class="flex gap-2 justify-center flex-wrap">
            @if($needsPayment)
                @auth
                    <a wire:navigate href="{{ route('gazu.order.payment', ['order' => $order->id]) }}"
                       class="gazu-btn-primary no-underline">
                        💳 Перейти до оплати
                    </a>
                @else
                    <a wire:navigate href="{{ route('gazu.auth') }}" class="gazu-btn-primary no-underline">Увійти для оплати</a>
                @endauth
                <a wire:navigate href="{{ route('gazu.catalog') }}" class="gazu-btn-outline no-underline">Продовжити покупки</a>
            @else
                <a wire:navigate href="{{ route('gazu.home') }}" class="gazu-btn-primary no-underline">На головну</a>
                <a wire:navigate href="{{ route('gazu.catalog') }}" class="gazu-btn-outline no-underline">Продовжити покупки</a>
            @endif

            @auth
                <a wire:navigate href="{{ route('gazu.account') }}" class="gazu-btn-outline no-underline">Мої замовлення</a>
            @endauth
        </div>

        @if($needsPayment)
            <div class="mt-4 text-xs text-[var(--gazu-graphite)] max-w-md mx-auto">
                Замовлення оформлено, але не оплачено. Натисніть «Перейти до оплати», щоб завершити платіж через WayForPay/LiqPay/Monobank.
            </div>
        @endif
    </div>
</div>
@endsection
