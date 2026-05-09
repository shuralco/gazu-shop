@props(['price' => 184, 'oldPrice' => null, 'qty' => 12, 'discount' => null, 'productId' => null])
@php
    $priceFmt = number_format((float) $price, 0, '.', ' ');
@endphp
<div class="bg-white border border-[var(--gazu-line)] rounded-[10px] p-6 font-text" x-data="{ q: 1, price: {{ (float) $price }} }">
    <div class="flex items-baseline gap-3 mb-1">
        <span class="gazu-display font-bold text-[var(--gazu-ink)] leading-none" style="font-size: 40px;">
            <span x-text="(price * q).toLocaleString('uk-UA').replace(/,/g, ' ')">{{ $priceFmt }}</span> <span class="text-2xl font-medium text-[var(--gazu-graphite)]">₴</span>
        </span>
        @if($oldPrice)
            <div class="flex flex-col gap-0.5">
                <span class="text-sm text-[var(--gazu-muted)] line-through">{{ number_format((float)$oldPrice, 0, '.', ' ') }} ₴</span>
                @if($discount)
                    <span class="text-[11px] gazu-mono px-1.5 py-0.5 bg-[var(--gazu-danger-bg)] text-[var(--gazu-danger)] rounded">−{{ $discount }}%</span>
                @endif
            </div>
        @endif
    </div>
    <div class="text-[11px] text-[var(--gazu-graphite)] mb-2" x-show="q > 1" x-cloak>
        {{ $priceFmt }} ₴ × <span x-text="q"></span> шт.
    </div>
    <div class="mt-1"><x-gazu.stock qty="{{ $qty }}"/></div>

    <div class="h-px bg-[var(--gazu-line)] my-5"></div>

    <form action="{{ route('gazu.cart.add') }}" method="POST">
        @csrf
        <input type="hidden" name="product_id" value="{{ $productId }}">
        <input type="hidden" name="quantity" :value="q">

        <div class="flex items-center gap-3 mb-3.5">
            <span class="text-[13px] text-[var(--gazu-graphite)]">Кількість</span>
            <div class="flex items-center border border-[var(--gazu-line)] rounded-md">
                <button type="button" @click="q = Math.max(1, q-1)" class="w-9 h-9 border-0 bg-transparent cursor-pointer text-[var(--gazu-ink)] inline-flex items-center justify-center"><x-gazu.icon name="minus" size="14"/></button>
                <input x-model.number="q" type="number" min="1" class="w-12 text-center border-0 py-2 text-sm gazu-mono font-medium text-[var(--gazu-ink)] outline-none">
                <button type="button" @click="q = q+1" class="w-9 h-9 border-0 bg-transparent cursor-pointer text-[var(--gazu-ink)] inline-flex items-center justify-center"><x-gazu.icon name="plus" size="14"/></button>
            </div>
            <span class="text-[11px] text-[var(--gazu-muted)] gazu-mono">шт.</span>
        </div>

        @if($qty > 0 && $productId)
            <button type="submit" class="w-full py-4 bg-[var(--gazu-ink)] text-white border-0 rounded-lg text-[15px] font-semibold cursor-pointer inline-flex items-center justify-center gap-2 hover:bg-[var(--gazu-ink-2)]">
                <x-gazu.icon name="cart" size="18"/> Додати в кошик · <span x-text="(price * q).toLocaleString('uk-UA').replace(/,/g, ' ')">{{ $priceFmt }}</span> ₴
            </button>
        @else
            <button type="button" disabled class="w-full py-4 bg-[var(--gazu-line-2)] text-[var(--gazu-graphite)] border-0 rounded-lg text-[15px] font-semibold cursor-not-allowed">
                Під замовлення
            </button>
        @endif

        @php
            $oneClickEnabled = ($gazuSettings['gazu_oneclick_enabled'] ?? true);
            $oneClickLabel = $gazuSettings['gazu_oneclick_label'] ?? 'Купити в один клік';
            $oneClickMessage = $gazuSettings['gazu_oneclick_message'] ?? 'Менеджер передзвонить за 5 хвилин для уточнення доставки';
        @endphp
        @if($oneClickEnabled && $productId)
            <button type="button" @click.prevent="$dispatch('open-oneclick', { productId: {{ $productId }}, qty: q })"
                    class="w-full mt-2 py-3.5 bg-white text-[var(--gazu-ink)] border-[1.5px] border-[var(--gazu-ink)] rounded-lg text-sm font-medium cursor-pointer">
                {{ $oneClickLabel }}
            </button>
        @endif
    </form>

    {{-- 1-клік модалка (Alpine listens for 'open-oneclick' event) --}}
    @if($oneClickEnabled && $productId)
        <div x-data="{ open: false, productId: null, qty: 1 }"
             x-on:open-oneclick.window="open = true; productId = $event.detail.productId; qty = $event.detail.qty || 1"
             x-show="open" x-cloak x-transition.opacity
             class="fixed inset-0 bg-black/45 z-[60] flex items-center justify-center p-4"
             @click.self="open = false">
            <div class="bg-white rounded-xl max-w-md w-full p-6" @click.stop>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="gazu-display text-xl font-semibold m-0">{{ $oneClickLabel }}</h3>
                    <button type="button" @click="open = false" class="bg-transparent border-0 cursor-pointer text-[var(--gazu-graphite)]">
                        <x-gazu.icon name="close" size="20"/>
                    </button>
                </div>
                <p class="text-sm text-[var(--gazu-graphite)] mb-4">{{ $oneClickMessage }}</p>
                <form action="{{ route('gazu.checkout.one-click') }}" method="POST">
                    @csrf
                    <input type="hidden" name="product_id" :value="productId">
                    <input type="hidden" name="quantity" :value="qty">
                    <label class="block mb-3">
                        <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Ваш телефон <span class="text-[var(--gazu-danger)]">*</span></span>
                        <input type="tel" name="phone" required value="{{ auth()->user()?->phone }}" placeholder="+380 67 123 45 67"
                               class="w-full px-3 py-3 border border-[var(--gazu-line)] rounded-md outline-none focus:border-[var(--gazu-ink)] gazu-mono">
                    </label>
                    <div class="flex gap-2">
                        <button type="submit" class="gazu-btn-primary flex-1">Замовити дзвінок</button>
                        <button type="button" @click="open = false" class="gazu-btn-outline">Скасувати</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <div class="mt-4 p-3.5 bg-[var(--gazu-mist)] rounded-lg flex flex-col gap-2.5">
        <div class="flex gap-2.5 items-start">
            <x-gazu.icon name="truck" size="18" stroke="var(--gazu-blue)" class="shrink-0"/>
            <div>
                <div class="text-[13px] font-medium text-[var(--gazu-ink)]">Доставка завтра, {{ now()->addDay()->format('d.m') }}</div>
                <div class="text-[11px] text-[var(--gazu-graphite)]">Замовте сьогодні до 16:00 · Нова Пошта</div>
            </div>
        </div>
        <div class="flex gap-2.5 items-start">
            <x-gazu.icon name="shield" size="18" stroke="var(--gazu-blue)" class="shrink-0"/>
            <div>
                <div class="text-[13px] font-medium text-[var(--gazu-ink)]">Гарантія 12 місяців</div>
                <div class="text-[11px] text-[var(--gazu-graphite)]">Повернення коштів при дефекті</div>
            </div>
        </div>
        <div class="flex gap-2.5 items-start">
            <x-gazu.icon name="return" size="18" stroke="var(--gazu-blue)" class="shrink-0"/>
            <div>
                <div class="text-[13px] font-medium text-[var(--gazu-ink)]">14 днів на повернення</div>
                <div class="text-[11px] text-[var(--gazu-graphite)]">Без пояснення причин</div>
            </div>
        </div>
    </div>

    <div class="mt-4 p-3 border border-dashed border-[var(--gazu-line-2)] rounded-lg flex gap-2.5 items-center">
        <x-gazu.icon name="chat" size="20" stroke="var(--gazu-blue)" class="shrink-0"/>
        <div class="text-xs text-[var(--gazu-graphite)] leading-relaxed">
            Не впевнені в підборі? <span class="text-[var(--gazu-blue)] font-medium">Запитайте менеджера</span> — відповість за 5 хв.
        </div>
    </div>
</div>
