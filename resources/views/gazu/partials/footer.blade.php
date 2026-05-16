@php
    $s = $gazuSettings ?? [];
    $about = $s['gazu_footer_about'] ?? sprintf(
        'Інтернет-магазин автозапчастин. %s, доставка по Україні, гарантія на кожну деталь.',
        $shopStats['products_label'] ?? 'широкий каталог'
    );
    $columns = $s['gazu_footer_columns'] ?? [
        ['title' => 'Каталог', 'items' => ['Двигун', 'Гальмівна система', 'Підвіска', 'Електрика', 'Кузов', 'Салон']],
        ['title' => 'Клієнтам', 'items' => ['Доставка та оплата', 'Гарантія та повернення', 'Питання та відповіді', 'Бонусна програма', 'Гуртовим клієнтам']],
        ['title' => 'Компанія', 'items' => ['Про нас', 'Контакти', 'Вакансії', 'Сертифікати', 'Публічна оферта']],
    ];

    // Map common footer labels to actual routes — kills the 'href="#"' dead-link cluster.
    $linkMap = [
        'Двигун'                => route('gazu.catalog'),
        'Гальмівна система'     => route('gazu.catalog'),
        'Підвіска'              => route('gazu.catalog'),
        'Електрика'             => route('gazu.catalog'),
        'Кузов'                 => route('gazu.catalog'),
        'Салон'                 => route('gazu.catalog'),
        'Доставка та оплата'    => route('gazu.delivery'),
        'Гарантія та повернення'=> route('gazu.warranty'),
        'Питання та відповіді'  => route('gazu.faq'),
        'Бонусна програма'      => route('gazu.loyalty'),
        'Гуртовим клієнтам'     => route('gazu.wholesale'),
        'Про нас'               => route('gazu.about'),
        'Контакти'              => route('gazu.contacts'),
        'Вакансії'              => route('gazu.careers'),
        'Сертифікати'           => route('gazu.certificates'),
        'Публічна оферта'       => route('gazu.offer'),
    ];
    $payments = $s['gazu_footer_payments'] ?? 'Visa, Mastercard, Apple Pay, Google Pay, Нова Пошта';
    $phone = $s['gazu_phone'] ?? '0 800 75 10 24';
    $hours = $s['gazu_topbar_hours'] ?? 'Пн-Нд 8:00–20:00';
    $social = [
        'FB' => $s['gazu_social_facebook'] ?? null,
        'IG' => $s['gazu_social_instagram'] ?? null,
        'TG' => $s['gazu_social_telegram'] ?? null,
        'YT' => $s['gazu_social_youtube'] ?? null,
    ];
@endphp
<footer class="bg-[var(--gazu-ink)] text-[#CDD3DC] mt-16">
    <div class="gazu-container py-14 grid gap-8 sm:gap-10 gazu-footer-grid">
        <div>
            <x-gazu.logo size="28" color="#fff"/>
            <p class="text-sm leading-relaxed mt-4 text-[#9DA5B2]">{{ $about }}</p>
            <div class="flex gap-2 mt-4">
                @foreach($social as $code => $url)
                    @if(! empty($url) && $url !== '#')
                        <a href="{{ $url }}" target="_blank" rel="nofollow noopener"
                           class="w-9 h-9 rounded-lg border border-[#2A3850] flex items-center justify-center text-[11px] text-[#CDD3DC] no-underline hover:bg-[#2A3850]">{{ $code }}</a>
                    @endif
                @endforeach
            </div>
        </div>

        @foreach((array) $columns as $col)
            <div>
                <div class="gazu-display text-sm font-semibold text-white mb-3.5">{{ $col['title'] ?? '' }}</div>
                <ul class="list-none p-0 m-0 flex flex-col gap-2.5">
                    @foreach((array) ($col['items'] ?? []) as $i)
                        @php $href = $linkMap[$i] ?? route('gazu.catalog'); @endphp
                        <li><a wire:navigate href="{{ $href }}" class="text-[13px] text-[#9DA5B2] no-underline hover:text-white">{{ $i }}</a></li>
                    @endforeach
                </ul>
            </div>
        @endforeach

        <div>
            <div class="gazu-display text-sm font-semibold text-white mb-3.5">Зворотний звʼязок</div>
            @if($phone)
                <div class="gazu-display text-[22px] text-white mb-1">{{ $phone }}</div>
            @endif
            @if($hours)
                <div class="text-xs text-[#9DA5B2] mb-4">{{ $hours }}, безкоштовно</div>
            @endif
            <button type="button"
                    @click="$dispatch('gazu:callback-open')"
                    class="w-full py-3 bg-[var(--gazu-blue)] text-white rounded-lg text-sm font-medium cursor-pointer hover:bg-[var(--gazu-blue-600)] transition-colors">
                Замовити дзвінок
            </button>
        </div>
    </div>

    {{-- Callback popup — глобальний, слухає event 'gazu:callback-open'. --}}
    <div x-data="{
            open: false,
            name: '',
            phone: '',
            busy: false,
            sent: false,
            error: '',
            close() { this.open = false; setTimeout(() => { this.sent = false; this.error = ''; this.phone=''; this.name=''; }, 300); },
            async submit() {
                this.error = '';
                if (!this.phone || this.phone.length < 7) { this.error = 'Введіть номер телефону'; return; }
                this.busy = true;
                try {
                    const r = await fetch('{{ route('gazu.callback.store') }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': window.GAZU_CSRF, 'Accept': 'application/json', 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({ name: this.name, phone: this.phone, source: 'footer' })
                    });
                    const d = await r.json();
                    if (d.ok) { this.sent = true; }
                    else { this.error = d.message || 'Помилка. Спробуйте ще раз.'; }
                } catch (e) {
                    this.error = 'Помилка з\'єднання.';
                } finally { this.busy = false; }
            }
         }"
         @gazu:callback-open.window="open = true; $nextTick(() => $refs.phoneInput?.focus())"
         @keydown.escape.window="open && close()"
         x-show="open" x-cloak x-transition.opacity
         class="fixed inset-0 z-[80] flex items-end sm:items-center justify-center p-0 sm:p-4"
         style="background: rgba(14,27,44,0.55);">
        <div @click.away="close()"
             x-show="open"
             x-transition.scale.origin.bottom.duration.250ms
             class="bg-white w-full sm:max-w-md rounded-t-2xl sm:rounded-2xl shadow-[0_25px_50px_-12px_rgba(0,0,0,0.4)] overflow-hidden">

            <div class="px-6 pt-6 pb-2 flex items-start justify-between gap-4">
                <div class="flex-1">
                    <div class="gazu-mono text-[10px] text-[var(--gazu-blue)] tracking-widest uppercase mb-1.5">Замовити дзвінок</div>
                    <h3 class="gazu-display text-[22px] font-semibold text-[var(--gazu-ink)] leading-tight m-0">Передзвонимо за 5 хв</h3>
                </div>
                <button type="button" @click="close()" aria-label="Закрити"
                        class="w-8 h-8 rounded-md text-[var(--gazu-graphite)] hover:bg-[var(--gazu-mist)] cursor-pointer inline-flex items-center justify-center shrink-0 bg-transparent border-0">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="px-6 pb-6">
                {{-- Success state --}}
                <div x-show="sent" x-cloak class="py-8 text-center">
                    <div class="w-12 h-12 rounded-full bg-[var(--gazu-success-bg)] inline-flex items-center justify-center mb-3">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--gazu-success)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>
                    </div>
                    <div class="gazu-display text-[18px] font-semibold text-[var(--gazu-ink)] mb-1">Дякуємо!</div>
                    <p class="text-[14px] text-[var(--gazu-graphite)] leading-relaxed">Менеджер передзвонить протягом 5 хвилин у робочий час (Пн-Нд 8:00–20:00).</p>
                    <button type="button" @click="close()"
                            class="mt-5 px-5 py-2.5 bg-[var(--gazu-ink)] text-white rounded-md text-[14px] font-medium cursor-pointer hover:bg-[var(--gazu-ink-2)] transition-colors border-0">
                        Закрити
                    </button>
                </div>

                {{-- Form state --}}
                <form x-show="!sent" @submit.prevent="submit()" class="flex flex-col gap-3.5 pt-2">
                    <label class="block">
                        <span class="text-[12px] text-[var(--gazu-graphite)] block mb-1.5">Ім'я (необов'язково)</span>
                        <input type="text" x-model="name" autocomplete="name"
                               class="w-full px-3.5 py-2.5 border border-[var(--gazu-line)] rounded-md text-[14px] outline-none focus:border-[var(--gazu-ink)] transition-colors"
                               placeholder="Як до вас звертатись">
                    </label>
                    <label class="block">
                        <span class="text-[12px] text-[var(--gazu-graphite)] block mb-1.5">Телефон <span class="text-[var(--gazu-danger)]">*</span></span>
                        <input type="tel" x-model="phone" x-ref="phoneInput" required autocomplete="tel" inputmode="tel"
                               class="w-full px-3.5 py-2.5 border border-[var(--gazu-line)] rounded-md text-[15px] gazu-mono outline-none focus:border-[var(--gazu-ink)] transition-colors"
                               placeholder="+38 (0__) ___ __ __">
                    </label>

                    <div x-show="error" x-cloak class="text-[12px] text-[var(--gazu-danger)] bg-[var(--gazu-danger-bg)] px-3 py-2 rounded" x-text="error"></div>

                    <button type="submit" :disabled="busy"
                            :class="busy ? 'bg-[var(--gazu-ink)] opacity-70 cursor-wait' : 'bg-[var(--gazu-blue)] hover:bg-[var(--gazu-blue-600)] cursor-pointer'"
                            class="w-full py-3 text-white border-0 rounded-md text-[14px] font-semibold transition-colors inline-flex items-center justify-center gap-2">
                        <svg x-show="busy" x-cloak class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.25" stroke-width="3"/>
                            <path d="M12 2a10 10 0 0 1 10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                        </svg>
                        <span x-text="busy ? 'Надсилаю...' : 'Передзвоніть мені'"></span>
                    </button>

                    <p class="text-[11px] text-[var(--gazu-muted)] text-center leading-relaxed">
                        Натискаючи кнопку, ви погоджуєтесь з <a href="{{ route('gazu.privacy') }}" class="text-[var(--gazu-blue)] no-underline">політикою конфіденційності</a>.
                    </p>
                </form>
            </div>
        </div>
    </div>
    <div class="border-t border-[#1A2740] gazu-container py-5 flex items-center gap-6 text-xs text-[#5A6573] flex-wrap">
        <span>© {{ date('Y') }} GAZU. Всі права захищені.</span>
        <span class="flex-1"></span>
        @foreach(array_filter(array_map('trim', explode(',', $payments))) as $pay)
            <span>{{ $pay }}</span>
        @endforeach
    </div>
</footer>
