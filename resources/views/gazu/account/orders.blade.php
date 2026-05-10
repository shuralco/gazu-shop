@extends('gazu.layout')
@section('title', 'Мої замовлення — GAZU')

@php
    $statusMap = [
        'pending'    => ['label' => 'Очікує', 'color' => 'warn'],
        'paid'       => ['label' => 'Сплачено', 'color' => 'success'],
        'processing' => ['label' => 'У роботі', 'color' => 'warn'],
        'shipped'    => ['label' => 'Відправлено', 'color' => 'success'],
        'delivered'  => ['label' => 'Доставлено', 'color' => 'success'],
        'cancelled'  => ['label' => 'Скасовано', 'color' => 'danger'],
        'completed'  => ['label' => 'Завершено', 'color' => 'success'],
    ];
@endphp

@section('content')
<div class="gazu-container">
    <x-gazu.breadcrumbs :items="[['Головна', route('gazu.home')], 'Особистий кабінет']"/>
    <h1 class="gazu-display text-4xl font-semibold text-[var(--gazu-ink)] m-0 mb-7">Особистий кабінет</h1>

    @if(session('flash_message'))
        <div class="bg-[var(--gazu-success-bg)] text-[var(--gazu-success)] px-4 py-2 rounded-md mb-4 text-sm">
            {{ session('flash_message') }}
        </div>
    @endif

    <div class="gazu-grid-account">
        @include('gazu.partials.account-sidebar', ['active' => 'orders', 'user' => $user])

        <div>
            <div class="flex items-center justify-between mb-5 flex-wrap gap-2">
                <h2 class="gazu-display text-2xl font-semibold m-0">Замовлення</h2>
                @if($orders->count() > 0)
                    <span class="text-sm text-[var(--gazu-graphite)]">Всього: <strong>{{ $orders->total() }}</strong></span>
                @endif
            </div>

            @if($orders->isEmpty())
                <div class="bg-white border border-[var(--gazu-line)] rounded-lg p-10 text-center">
                    <div class="inline-flex w-16 h-16 bg-[var(--gazu-mist)] rounded-full items-center justify-center mb-4 text-[var(--gazu-blue)]">
                        <x-gazu.icon name="box" size="28"/>
                    </div>
                    <div class="gazu-display text-xl font-semibold mb-2">Замовлень поки немає</div>
                    <p class="text-sm text-[var(--gazu-graphite)] mb-4">Як тільки оформите перше замовлення, воно зʼявиться тут.</p>
                    <a wire:navigate href="{{ route('gazu.catalog') }}" class="gazu-btn-primary no-underline">До каталогу</a>
                </div>
            @else
                <div class="flex flex-col gap-3">
                    @foreach($orders as $order)
                        @php
                            $st = $statusMap[$order->status] ?? ['label' => $order->status, 'color' => 'graphite'];
                            $count = $order->orderProducts->count();
                            $word = plural_uk($count, 'товар', 'товари', 'товарів');
                        @endphp
                        <div class="bg-white border border-[var(--gazu-line)] rounded-lg p-4 gazu-grid-order-row">
                            <div class="min-w-0">
                                <div class="gazu-display font-semibold text-sm text-[var(--gazu-ink)]">#{{ $order->id }}</div>
                                <div class="text-xs text-[var(--gazu-graphite)] mt-0.5">{{ $count }} {{ $word }}</div>
                            </div>
                            <div class="text-sm text-[var(--gazu-graphite)] gazu-mono">{{ $order->created_at?->format('d.m.Y') }}</div>
                            <div class="gazu-display font-bold text-[var(--gazu-ink)]">{{ number_format((float) $order->total, 0, '.', ' ') }} ₴</div>
                            <div>
                                <span class="text-xs gazu-mono px-2 py-1 rounded inline-block whitespace-nowrap"
                                      style="background: var(--gazu-{{ $st['color'] }}-bg, var(--gazu-line)); color: var(--gazu-{{ $st['color'] }}, var(--gazu-graphite));">
                                    {{ $st['label'] }}
                                </span>
                            </div>
                            <a wire:navigate href="{{ route('gazu.account.order', ['order' => $order->id]) }}"
                               class="gazu-btn-outline text-xs px-3 py-2 no-underline text-right">
                                Деталі →
                            </a>
                        </div>
                    @endforeach
                </div>

                @if($orders->lastPage() > 1)
                    <div class="mt-6">{{ $orders->links("vendor.pagination.gazu") }}</div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection
