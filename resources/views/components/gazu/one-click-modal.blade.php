{{-- Global 1-click order modal — GAZU design system. Opens on `gazu:one-click` event. --}}
<div x-data="{
        open: false,
        productId: null,
        productName: '',
        productPrice: 0,
        phone: '',
        name: '',
        busy: false,
        err: '',
        init() {
            window.addEventListener('gazu:one-click', (e) => {
                this.open = true;
                this.busy = false;
                this.err = '';
                this.phone = '';
                this.name = '';
                this.productId = e.detail?.productId ?? null;
                this.productName = e.detail?.productName ?? '';
                this.productPrice = e.detail?.productPrice ?? 0;
                this.$nextTick(() => this.$refs.phone?.focus());
            });
        },
        money(v) { return new Intl.NumberFormat('uk-UA').format(Number(v) || 0) + ' ₴'; },
        async submit() {
            if (this.busy) return;
            if (!this.phone.trim()) { this.err = 'Введіть номер телефону'; this.$refs.phone?.focus(); return; }
            this.busy = true; this.err = '';
            try {
                const r = await fetch('{{ route('gazu.checkout.one-click') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: new URLSearchParams({
                        product_id: this.productId,
                        phone: this.phone,
                        name: this.name,
                    })
                });
                const d = await r.json();
                if (d.ok) {
                    window.gazuToast && window.gazuToast(d.message || 'Замовлення прийнято · передзвонимо', 'success');
                    this.open = false;
                } else {
                    this.err = d.message || 'Не вдалося оформити';
                }
            } catch(e) {
                this.err = 'Помилка з\'єднання';
            } finally { this.busy = false; }
        }
     }"
     x-show="open"
     x-cloak
     @keydown.escape.window="open = false"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-[80] flex items-center justify-center p-4"
     style="background: rgba(14, 27, 44, 0.5);"
     @click.self="open = false">
    <div x-show="open"
         x-transition:enter="transition transform ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-3 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="relative bg-white w-full max-w-md rounded-xl shadow-2xl overflow-hidden border border-[var(--gazu-line)]">
        {{-- Header --}}
        <div class="flex items-center justify-between px-5 py-4 border-b border-[var(--gazu-line)]">
            <div class="flex items-center gap-2.5 min-w-0">
                <span class="w-9 h-9 rounded-lg bg-[var(--gazu-mist)] text-[var(--gazu-blue)] inline-flex items-center justify-center shrink-0">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2L3 14h7l-1 8 10-12h-7l1-8z"/></svg>
                </span>
                <div class="min-w-0">
                    <div class="gazu-display text-[16px] font-semibold text-[var(--gazu-ink)] leading-tight">Купити в 1 клік</div>
                    <div class="text-[11px] text-[var(--gazu-graphite)]">Передзвонимо за 15 хв · без передоплати</div>
                </div>
            </div>
            <button type="button" @click="open = false"
                    class="w-8 h-8 rounded-md hover:bg-[var(--gazu-mist)] flex items-center justify-center text-[var(--gazu-graphite)] shrink-0 cursor-pointer" aria-label="Закрити">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="p-5">
            {{-- Product summary --}}
            <div class="mb-4 p-3 flex items-start gap-3 bg-[var(--gazu-paper)] border border-[var(--gazu-line)] rounded-lg">
                <div class="flex-1 min-w-0">
                    <div class="gazu-mono text-[10px] tracking-widest uppercase text-[var(--gazu-muted)] mb-1">Товар</div>
                    <div class="text-sm font-medium text-[var(--gazu-ink)] line-clamp-2" x-text="productName">—</div>
                </div>
                <div class="shrink-0 text-right">
                    <div class="gazu-mono text-[10px] tracking-widest uppercase text-[var(--gazu-muted)] mb-1">Ціна</div>
                    <div class="gazu-display text-lg font-bold text-[var(--gazu-ink)]" x-text="money(productPrice)">—</div>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-3">
                <div>
                    <label class="block text-[12px] font-medium text-[var(--gazu-ink)] mb-1.5">Телефон <span class="text-[var(--gazu-danger)]">*</span></label>
                    <input x-ref="phone" x-model="phone" type="tel" required
                           placeholder="+380 67 123 45 67" autocomplete="tel"
                           class="w-full px-3.5 py-2.5 text-sm gazu-mono border border-[var(--gazu-line)] rounded-md bg-white outline-none focus:border-[var(--gazu-ink)] transition-colors">
                </div>
                <div>
                    <label class="block text-[12px] font-medium text-[var(--gazu-ink)] mb-1.5">Ім'я <span class="text-[var(--gazu-muted)] font-normal">(необов'язково)</span></label>
                    <input x-model="name" type="text" placeholder="Як до вас звертатись"
                           class="w-full px-3.5 py-2.5 text-sm border border-[var(--gazu-line)] rounded-md bg-white outline-none focus:border-[var(--gazu-ink)] transition-colors">
                </div>

                <div x-show="err" x-cloak class="px-3 py-2 text-[13px] font-medium rounded-md"
                     style="background: var(--gazu-danger-bg); color: var(--gazu-danger);" x-text="err"></div>

                <button type="submit" :disabled="busy"
                        class="w-full py-3 bg-[var(--gazu-ink)] hover:bg-[var(--gazu-ink-2)] text-white border-0 rounded-md text-sm font-semibold cursor-pointer inline-flex items-center justify-center gap-2 transition-colors"
                        :class="busy ? 'opacity-70 cursor-not-allowed' : ''">
                    <svg x-show="!busy" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2L3 14h7l-1 8 10-12h-7l1-8z"/></svg>
                    <svg x-show="busy" x-cloak class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.25" stroke-width="3"/><path d="M12 2a10 10 0 0 1 10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                    <span x-show="!busy">Оформити замовлення</span>
                    <span x-show="busy" x-cloak>Надсилаю…</span>
                </button>
                <p class="text-[11px] text-[var(--gazu-graphite)] text-center m-0 flex items-center justify-center gap-1.5">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    Менеджер передзвонить протягом 15 хвилин
                </p>
            </form>
        </div>
    </div>
</div>
