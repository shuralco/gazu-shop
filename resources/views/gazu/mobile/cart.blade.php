@extends('gazu.layout')
@section('title', 'Кошик · mobile')

@section('content')
<div class="max-w-[420px] mx-auto py-4 px-4 pb-32">
    <h1 class="gazu-display text-xl font-semibold mb-3">Кошик · {{ count($cart ?? []) }}</h1>

    @if(empty($cart))
        <div class="bg-white border border-[var(--gazu-line)] rounded-lg p-6 text-center">
            <div class="inline-flex w-14 h-14 bg-[var(--gazu-mist)] rounded-full items-center justify-center mb-3 text-[var(--gazu-blue)]">
                <x-gazu.icon name="cart" size="24"/>
            </div>
            <div class="text-sm font-medium mb-2">{{ $gazuSettings['gazu_cart_empty_title'] ?? 'Кошик порожній' }}</div>
            <p class="text-xs text-[var(--gazu-graphite)] mb-3">{{ $gazuSettings['gazu_cart_empty_desc'] ?? 'Додайте товари з каталогу' }}</p>
            <a href="{{ route('gazu.catalog') }}" class="gazu-btn-primary text-xs no-underline">До каталогу</a>
        </div>
    @else
        <div class="flex flex-col gap-2">
            @foreach($cart as $key => $item)
                @php
                    $productId = is_numeric($key) ? (int) $key : (int) explode('_', (string) $key)[0];
                    $title = is_array($item['title'] ?? null) ? ($item['title']['uk'] ?? '—') : ($item['title'] ?? '—');
                    $price = (float) ($item['price'] ?? 0);
                    $qty = (int) ($item['quantity'] ?? 1);
                    $kinds = ['filter','pad','shock','bulb','oil','spark','bearing','wiper'];
                    $kind = $kinds[$productId % count($kinds)];
                @endphp
                <div class="bg-white border border-[var(--gazu-line)] rounded-lg p-3 flex gap-3 items-center">
                    <div class="w-16 h-16 bg-[var(--gazu-paper)] rounded flex items-center justify-center shrink-0">
                        <x-gazu.part-image kind="{{ $kind }}" size="50"/>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium text-[var(--gazu-ink)] truncate">{{ $title }}</div>
                        <div class="flex items-center gap-2 mt-1.5">
                            <form action="{{ route('gazu.cart.update') }}" method="POST" class="flex items-center border border-[var(--gazu-line)] rounded">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $productId }}">
                                <button type="submit" name="quantity" value="{{ max(1, $qty - 1) }}" class="w-7 h-7 flex items-center justify-center"><x-gazu.icon name="minus" size="12"/></button>
                                <span class="px-2 text-sm gazu-mono font-medium">{{ $qty }}</span>
                                <button type="submit" name="quantity" value="{{ $qty + 1 }}" class="w-7 h-7 flex items-center justify-center"><x-gazu.icon name="plus" size="12"/></button>
                            </form>
                            <span class="flex-1"></span>
                            <span class="gazu-display font-bold text-sm">{{ number_format($price * $qty, 0, '.', ' ') }} ₴</span>
                            <form action="{{ route('gazu.cart.remove') }}" method="POST">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $productId }}">
                                <button type="submit" class="w-7 h-7 flex items-center justify-center text-[var(--gazu-graphite)]"><x-gazu.icon name="trash" size="14"/></button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4 p-4 bg-[var(--gazu-paper)] rounded-lg">
            <div class="flex justify-between text-sm mb-1"><span class="text-[var(--gazu-graphite)]">Сума</span><span>{{ number_format($cartTotal, 0, '.', ' ') }} ₴</span></div>
            <div class="flex justify-between text-base mt-2 pt-2 border-t border-[var(--gazu-line)]"><span class="font-medium">До сплати</span><span class="gazu-display text-xl font-bold">{{ number_format($cartTotal, 0, '.', ' ') }} ₴</span></div>
        </div>
    @endif
</div>

@if(! empty($cart))
<div class="fixed bottom-12 left-0 right-0 max-w-[420px] mx-auto bg-white border-t border-[var(--gazu-line)] p-3 z-20">
    <a href="{{ route('gazu.checkout') }}" class="gazu-btn-primary w-full py-3 no-underline">Оформити замовлення</a>
</div>
@endif

@include('gazu.partials.mobile-nav', ['active' => 'cart'])
@endsection
