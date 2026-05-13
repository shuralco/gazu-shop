{{-- Global one-click order modal — brutal style. Opens on `gazu:one-click` event. --}}
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
     class="fixed inset-0 z-[80] flex items-center justify-center p-4"
     style="background: rgba(14, 27, 44, 0.55);"
     @click.self="open = false">
    <div x-show="open"
         x-transition:enter="transition transform ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-3 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="gz-brutal-modal relative bg-white w-full max-w-md p-6 sm:p-7"
         style="border: 2.5px solid #0e1b2c; box-shadow: 8px 8px 0 0 #0e1b2c;">
        {{-- Yellow ribbon header --}}
        <div class="-mx-6 sm:-mx-7 -mt-6 sm:-mt-7 mb-5 px-6 sm:px-7 py-3 flex items-center justify-between"
             style="background:#FFD840; border-bottom: 2.5px solid #0e1b2c;">
            <div class="flex items-center gap-2">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="#0e1b2c" stroke="#0e1b2c" stroke-width="2" stroke-linejoin="round" stroke-linecap="round"><path d="M13 2L3 14h7l-1 8 10-12h-7l1-8z"/></svg>
                <span class="gazu-display text-[18px] font-black tracking-tight" style="color:#0e1b2c; letter-spacing:-0.02em;">КУПИТИ В 1 КЛІК</span>
            </div>
            <button type="button" @click="open = false"
                    class="w-8 h-8 inline-flex items-center justify-center cursor-pointer"
                    style="background:#0e1b2c; color:#FFD840; border:0;" aria-label="Закрити">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Product summary --}}
        <div class="mb-5 p-3 flex items-start gap-3" style="background:#f5f2ec; border:1.5px solid #0e1b2c;">
            <div class="flex-1 min-w-0">
                <div class="gazu-mono text-[10px] tracking-widest uppercase text-[var(--gazu-graphite)] mb-1">ТОВАР</div>
                <div class="text-sm font-semibold text-[var(--gazu-ink)] line-clamp-2" x-text="productName">—</div>
            </div>
            <div class="shrink-0 text-right">
                <div class="gazu-mono text-[10px] tracking-widest uppercase text-[var(--gazu-graphite)] mb-1">ЦІНА</div>
                <div class="gazu-display text-lg font-black text-[var(--gazu-ink)]" x-text="money(productPrice)">—</div>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-3.5">
            <div>
                <label class="block gazu-mono text-[10px] tracking-widest uppercase text-[var(--gazu-graphite)] mb-1.5">ТЕЛЕФОН *</label>
                <input x-ref="phone" x-model="phone" type="tel" required
                       placeholder="+380 67 123 45 67" autocomplete="tel"
                       class="w-full px-3.5 py-2.5 text-sm gazu-mono outline-none"
                       style="border:2px solid #0e1b2c; background:#fff;">
            </div>
            <div>
                <label class="block gazu-mono text-[10px] tracking-widest uppercase text-[var(--gazu-graphite)] mb-1.5">ІМ'Я (необов'язково)</label>
                <input x-model="name" type="text" placeholder="Як до вас звертатись"
                       class="w-full px-3.5 py-2.5 text-sm outline-none"
                       style="border:2px solid #0e1b2c; background:#fff;">
            </div>

            <div x-show="err" x-cloak class="px-3 py-2 text-sm font-medium"
                 style="background:#fde0e0; color:#b83232; border:1.5px solid #b83232;" x-text="err"></div>

            <div class="flex flex-col gap-2 pt-1">
                <button type="submit" :disabled="busy"
                        class="w-full py-3 text-white text-sm font-bold tracking-wide cursor-pointer inline-flex items-center justify-center gap-2 transition-all"
                        :class="busy ? 'opacity-70' : 'hover:translate-x-[-2px] hover:translate-y-[-2px] hover:shadow-[6px_6px_0_0_#FFD840]'"
                        style="background:#0e1b2c; border:2px solid #0e1b2c; box-shadow:4px 4px 0 0 #FFD840;">
                    <svg x-show="!busy" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2L3 14h7l-1 8 10-12h-7l1-8z"/></svg>
                    <svg x-show="busy" x-cloak class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.25" stroke-width="3"/><path d="M12 2a10 10 0 0 1 10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                    <span x-show="!busy">ОФОРМИТИ ЗАМОВЛЕННЯ</span>
                    <span x-show="busy" x-cloak>Надсилаю…</span>
                </button>
                <p class="text-[11px] text-[var(--gazu-graphite)] text-center m-0">Передзвонимо протягом 15 хв. Без передоплати.</p>
            </div>
        </form>
    </div>
</div>
