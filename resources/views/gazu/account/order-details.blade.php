@extends('gazu.layout')
@section('title', 'Замовлення №'.$order->id.' — GAZU')

@php
    $statusMap = [
        'pending' => ['label' => 'Очікує', 'color' => 'warn'],
        'paid' => ['label' => 'Сплачено', 'color' => 'success'],
        'processing' => ['label' => 'У роботі', 'color' => 'warn'],
        'shipped' => ['label' => 'Відправлено', 'color' => 'success'],
        'delivered' => ['label' => 'Доставлено', 'color' => 'success'],
        'cancelled' => ['label' => 'Скасовано', 'color' => 'danger'],
        'completed' => ['label' => 'Завершено', 'color' => 'success'],
    ];
    $st = $statusMap[$order->status] ?? ['label' => $order->status, 'color' => 'graphite'];
    $needsPayment = in_array($order->payment_method, ['card', 'applepay'], true) && $order->payment_status !== 'paid';
@endphp

@section('content')
<div class="gazu-container">
    <x-gazu.breadcrumbs :items="[
        ['Головна', route('gazu.home')],
        ['Кабінет', route('gazu.account')],
        'Замовлення №'.$order->id,
    ]"/>

    <div class="gazu-grid-account">
        @include('gazu.partials.account-sidebar', ['active' => 'orders', 'user' => $user])

        <div>
            <div class="flex items-baseline justify-between mb-5 flex-wrap gap-2">
                <h1 class="gazu-display text-3xl font-semibold m-0">Замовлення №{{ $order->id }}</h1>
                <span class="text-xs gazu-mono px-3 py-1.5 rounded inline-block whitespace-nowrap"
                      style="background: var(--gazu-{{ $st['color'] }}-bg, var(--gazu-line)); color: var(--gazu-{{ $st['color'] }}, var(--gazu-graphite));">
                    {{ $st['label'] }}
                </span>
            </div>

            {{-- Order info --}}
            <div class="bg-white border border-[var(--gazu-line)] rounded-lg p-5 mb-4 grid md:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                <div>
                    <div class="text-xs text-[var(--gazu-graphite)] mb-0.5">Дата</div>
                    <div class="text-[var(--gazu-ink)]">{{ $order->created_at?->format('d.m.Y H:i') }}</div>
                </div>
                <div>
                    <div class="text-xs text-[var(--gazu-graphite)] mb-0.5">Сума</div>
                    <div class="gazu-display text-lg font-bold text-[var(--gazu-ink)]">{{ number_format((float) $order->total, 0, '.', ' ') }} ₴</div>
                </div>
                <div>
                    <div class="text-xs text-[var(--gazu-graphite)] mb-0.5">Покупець</div>
                    <div class="text-[var(--gazu-ink)]">{{ trim(($order->first_name ?? '').' '.($order->last_name ?? '')) ?: ($order->name ?? '—') }}</div>
                </div>
                <div>
                    <div class="text-xs text-[var(--gazu-graphite)] mb-0.5">Телефон</div>
                    <div class="text-[var(--gazu-ink)] gazu-mono">{{ $order->phone ?? '—' }}</div>
                </div>
                @if($order->email)
                    <div>
                        <div class="text-xs text-[var(--gazu-graphite)] mb-0.5">Email</div>
                        <div class="text-[var(--gazu-ink)]">{{ $order->email }}</div>
                    </div>
                @endif
                <div>
                    <div class="text-xs text-[var(--gazu-graphite)] mb-0.5">Доставка</div>
                    <div class="text-[var(--gazu-ink)]">{{ ucfirst($order->shipping_method ?? '—') }}</div>
                    @if($order->shipping_city || $order->shipping_warehouse)
                        <div class="text-xs text-[var(--gazu-graphite)]">{{ trim(($order->shipping_city ?? '').' · '.($order->shipping_warehouse ?? '')) }}</div>
                    @endif
                </div>
                <div>
                    <div class="text-xs text-[var(--gazu-graphite)] mb-0.5">Оплата</div>
                    <div class="text-[var(--gazu-ink)]">{{ ucfirst($order->payment_method ?? '—') }}</div>
                    <div class="text-xs gazu-mono inline-block px-2 py-0.5 rounded mt-0.5"
                         style="background: var(--gazu-{{ $order->payment_status === 'paid' ? 'success' : 'warn' }}-bg); color: var(--gazu-{{ $order->payment_status === 'paid' ? 'success' : 'warn' }})">
                        {{ $order->payment_status === 'paid' ? 'Сплачено' : 'Очікує оплати' }}
                    </div>
                </div>
                @if($order->note)
                    <div class="md:col-span-2">
                        <div class="text-xs text-[var(--gazu-graphite)] mb-0.5">Коментар</div>
                        <div class="text-[var(--gazu-ink)]">{{ $order->note }}</div>
                    </div>
                @endif
            </div>

            {{-- Items --}}
            <h2 class="gazu-display text-xl font-semibold mb-3">{{ plural_uk_count($order->orderProducts->count(), 'Товар', 'Товари', 'Товарів') }}</h2>
            <div class="bg-white border border-[var(--gazu-line)] rounded-lg overflow-hidden mb-4">
                @php
                    // Group order products by warehouse_id so users see "Зі складу X" sections.
                    $orderProducts = $order->orderProducts->load('warehouse');
                    $byWarehouse = $orderProducts->groupBy(fn ($op) => $op->warehouse_id ?? 0);
                    $isMulti = $byWarehouse->count() > 1;
                @endphp
                @foreach($byWarehouse as $whId => $items)
                    @php
                        $wh = $whId ? \App\Models\MerchantWarehouse::find($whId) : null;
                    @endphp
                    @if($wh && ($isMulti || $wh->delivery_eta))
                        <div class="bg-[var(--gazu-mist)] px-4 py-2 border-b border-[var(--gazu-line)] flex items-center gap-2 text-xs">
                            <x-gazu.icon name="location" size="14" stroke="var(--gazu-blue)"/>
                            <span class="font-medium text-[var(--gazu-ink)]">{{ $wh->city ?: $wh->name }}</span>
                            @if($wh->delivery_eta)
                                <span class="text-[var(--gazu-muted)]">·</span>
                                <span class="text-[var(--gazu-graphite)]">{{ $wh->delivery_eta }}</span>
                            @endif
                        </div>
                    @endif
                    @foreach($items as $i => $op)
                        @php
                            $title = is_array($op->title) ? ($op->title['uk'] ?? '—') : ($op->title ?? '—');
                            $kinds = ['filter','pad','shock','bulb','oil','spark','bearing','wiper'];
                            $kind = $kinds[($op->product_id ?? 0) % count($kinds)];
                            $line = (float) $op->price * (int) $op->quantity;
                        @endphp
                        <div class="flex items-center gap-3 p-4 {{ ($i || ! $loop->parent->first) ? 'border-t border-[var(--gazu-line)]' : '' }}">
                            <div class="w-14 h-14 bg-[var(--gazu-paper)] rounded flex items-center justify-center shrink-0">
                                <x-gazu.part-image kind="{{ $kind }}" size="48"/>
                            </div>
                            <div class="flex-1 min-w-0">
                                @if($op->slug)
                                    <a href="{{ route('gazu.product.show', ['slug' => $op->slug]) }}" class="text-[var(--gazu-ink)] no-underline font-medium leading-snug hover:text-[var(--gazu-blue)]">{{ $title }}</a>
                                @else
                                    <span class="text-[var(--gazu-ink)] font-medium leading-snug">{{ $title }}</span>
                                @endif
                                <div class="text-xs text-[var(--gazu-graphite)] gazu-mono mt-0.5">{{ $op->quantity }} × {{ number_format((float) $op->price, 0, '.', ' ') }} ₴</div>
                            </div>
                            <div class="gazu-display font-bold text-[var(--gazu-ink)] whitespace-nowrap">{{ number_format($line, 0, '.', ' ') }} ₴</div>
                        </div>
                    @endforeach
                @endforeach
                <div class="bg-[var(--gazu-paper)] p-4 flex justify-between items-baseline border-t border-[var(--gazu-line)]">
                    <span class="font-medium text-[var(--gazu-ink)]">Усього</span>
                    <span class="gazu-display text-2xl font-bold text-[var(--gazu-ink)]">{{ number_format((float) $order->total, 0, '.', ' ') }} ₴</span>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex gap-2 flex-wrap">
                <a href="{{ route('gazu.account') }}" class="gazu-btn-outline no-underline">← Усі замовлення</a>
                @if($needsPayment)
                    <a href="{{ url('/'.app()->getLocale().'/orders/'.$order->id.'/payment') }}" class="gazu-btn-primary no-underline">💳 Перейти до оплати</a>
                @endif
                <a href="{{ route('gazu.catalog') }}" class="gazu-btn-outline no-underline">Замовити ще</a>
            </div>
        </div>
    </div>
</div>
@endsection
