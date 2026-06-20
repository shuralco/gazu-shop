<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['productId', 'variant' => 'card']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['productId', 'variant' => 'card']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div x-data="{
        open: false,
        email: '',
        phone: '',
        busy: false,
        sent: false,
        error: '',
        toggle() { this.open = !this.open; if (this.open) this.$nextTick(() => this.$refs.emailInput?.focus()); },
        close() { this.open = false; },
        async submit() {
            this.error = '';
            if (!this.email || !this.email.includes('@')) { this.error = 'Введіть email'; return; }
            this.busy = true;
            try {
                const r = await fetch('<?php echo e(route('gazu.stock.notify')); ?>', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': window.GAZU_CSRF, 'Accept': 'application/json', 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ product_id: '<?php echo e($productId); ?>', email: this.email, phone: this.phone })
                });
                const d = await r.json();
                if (d.ok) { this.sent = true; setTimeout(() => this.close(), 3500); }
                else { this.error = d.message || 'Помилка'; }
            } catch (e) { this.error = 'Помилка з\'єднання'; }
            finally { this.busy = false; }
        }
     }"
     @keydown.escape.window="open && close()"
     @click.away="open && close()"
     class="relative inline-block w-full">

    <?php if($variant === 'full'): ?>
        <button type="button" @click="toggle()"
                class="w-full py-2.5 bg-[var(--gazu-ink)] hover:bg-[var(--gazu-ink-2)] text-[var(--gazu-on-brand)] border-0 rounded-md text-[13px] font-medium cursor-pointer inline-flex items-center justify-center gap-1.5 transition-colors">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            Сповістити коли з'явиться
        </button>
    <?php else: ?>
        <button type="button" @click="toggle()"
                class="flex-1 min-w-0 py-2.5 bg-[var(--gazu-surface)] text-[var(--gazu-ink)] border border-[var(--gazu-line)] hover:border-[var(--gazu-ink)] rounded-md text-[13px] font-medium cursor-pointer inline-flex items-center justify-center gap-1.5 whitespace-nowrap transition-colors">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            Сповістити
        </button>
    <?php endif; ?>

    <div x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 -translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="absolute z-50 bottom-full left-0 right-0 mb-2 bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-xl shadow-[0_12px_32px_-8px_rgba(14,27,44,0.20)] overflow-hidden">

        <div class="px-3.5 pt-3 pb-1 flex items-start justify-between gap-2">
            <div class="text-[13px] font-semibold text-[var(--gazu-ink)] leading-snug">Повідомимо коли з'явиться</div>
            <button type="button" @click="close()" aria-label="Закрити"
                    class="-mt-0.5 -mr-1 w-6 h-6 rounded inline-flex items-center justify-center text-[var(--gazu-muted)] hover:text-[var(--gazu-ink)] cursor-pointer bg-transparent border-0 shrink-0">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>

        <div x-show="sent" x-cloak class="px-3.5 pb-3.5 pt-1">
            <div class="flex items-start gap-2 text-[12.5px] text-[var(--gazu-success)] bg-[var(--gazu-success-bg)] rounded px-2.5 py-2">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 mt-0.5"><path d="M20 6L9 17l-5-5"/></svg>
                <span class="leading-snug">Дякуємо! Сповістимо вас одразу як товар з'явиться.</span>
            </div>
        </div>

        <form x-show="!sent" @submit.prevent="submit()" class="px-3.5 pb-3.5 flex flex-col gap-2">
            <input type="email" x-model="email" x-ref="emailInput" required placeholder="Email *"
                   class="w-full px-2.5 py-2 border border-[var(--gazu-line)] rounded text-[13px] outline-none focus:border-[var(--gazu-ink)] transition-colors">
            <input type="tel" x-model="phone" placeholder="Телефон (опційно)" inputmode="tel"
                   class="w-full px-2.5 py-2 border border-[var(--gazu-line)] rounded text-[13px] gazu-mono outline-none focus:border-[var(--gazu-ink)] transition-colors">
            <div x-show="error" x-cloak class="text-[11px] text-[var(--gazu-danger)]" x-text="error"></div>
            <button type="submit" :disabled="busy"
                    class="w-full py-2 bg-[var(--gazu-blue)] text-[var(--gazu-on-brand)] border-0 rounded text-[13px] font-semibold transition-colors inline-flex items-center justify-center gap-1.5 disabled:opacity-70 disabled:cursor-wait hover:bg-[var(--gazu-blue-600)]">
                <svg x-show="busy" x-cloak class="animate-spin w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.25" stroke-width="3"/><path d="M12 2a10 10 0 0 1 10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                <span x-text="busy ? 'Зберігаю...' : 'Підписатись'"></span>
            </button>
        </form>
    </div>
</div>
<?php /**PATH /home/lionex/projects/gazu-shop/resources/views/components/gazu/stock-notify.blade.php ENDPATH**/ ?>