<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'variant' => 'link',      // 'link' | 'button' | 'icon'
    'source' => 'header',
    'label' => 'Замовити дзвінок',
    'align' => 'right',       // 'right' | 'left' | 'center'
]));

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

foreach (array_filter(([
    'variant' => 'link',      // 'link' | 'button' | 'icon'
    'source' => 'header',
    'label' => 'Замовити дзвінок',
    'align' => 'right',       // 'right' | 'left' | 'center'
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<?php
    $alignClass = match($align) {
        'left'   => 'left-0',
        'center' => 'left-1/2 -translate-x-1/2',
        default  => 'right-0',
    };
?>
<div x-data="{
        open: false,
        name: '',
        phone: '',
        busy: false,
        sent: false,
        error: '',
        toggle() { this.open = !this.open; if (this.open) this.$nextTick(() => this.$refs.phoneInput?.focus()); },
        close() { this.open = false; setTimeout(() => { if (!this.open) { this.sent=false; this.error=''; } }, 200); },
        async submit() {
            this.error = '';
            if (!this.phone || this.phone.replace(/\D/g,'').length < 7) { this.error = 'Введіть номер'; return; }
            this.busy = true;
            try {
                const r = await fetch('<?php echo e(route('gazu.callback.store')); ?>', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': window.GAZU_CSRF, 'Accept': 'application/json', 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ name: this.name, phone: this.phone, source: '<?php echo e($source); ?>' })
                });
                const d = await r.json();
                if (d.ok) { this.sent = true; this.phone=''; this.name=''; setTimeout(() => this.close(), 3500); }
                else { this.error = d.message || 'Помилка'; }
            } catch (e) { this.error = 'Помилка з\'єднання'; }
            finally { this.busy = false; }
        }
     }"
     @keydown.escape.window="open && close()"
     @click.away="open && close()"
     class="relative <?php echo e($variant === 'button' ? 'block w-full' : 'inline-block'); ?>">

    <?php if($variant === 'link'): ?>
        <button type="button" @click="toggle()"
                class="text-[11px] text-[var(--gazu-blue)] hover:text-[var(--gazu-ink)] cursor-pointer bg-transparent border-0 p-0 inline-flex items-center gap-1 whitespace-nowrap underline underline-offset-2 decoration-dotted transition-colors">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            <?php echo e($label); ?>

        </button>
    <?php elseif($variant === 'button'): ?>
        <button type="button" @click="toggle()"
                class="w-full py-3 bg-[var(--gazu-blue)] text-white rounded-lg text-sm font-medium cursor-pointer hover:bg-[var(--gazu-blue-600)] transition-colors border-0 inline-flex items-center justify-center gap-2">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            <?php echo e($label); ?>

        </button>
    <?php else: ?>
        <button type="button" @click="toggle()" :aria-expanded="open" aria-label="<?php echo e($label); ?>"
                class="w-9 h-9 inline-flex items-center justify-center rounded-md text-[var(--gazu-ink)] bg-white border border-[var(--gazu-line)] hover:border-[var(--gazu-ink)] cursor-pointer transition-colors">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
        </button>
    <?php endif; ?>

    
    <div x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 -translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="absolute z-50 top-full mt-2 <?php echo e($alignClass); ?> w-[290px] bg-white border border-[var(--gazu-line)] rounded-xl shadow-[0_12px_32px_-8px_rgba(14,27,44,0.20)] overflow-hidden">

        
        <div class="px-4 pt-3 pb-1 flex items-start justify-between gap-2">
            <div class="text-[13px] font-semibold text-[var(--gazu-ink)] leading-snug">Передзвонимо за 5 хв</div>
            <button type="button" @click="close()" aria-label="Закрити"
                    class="-mt-0.5 -mr-1 w-6 h-6 rounded inline-flex items-center justify-center text-[var(--gazu-muted)] hover:text-[var(--gazu-ink)] cursor-pointer bg-transparent border-0 shrink-0">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>

        
        <div x-show="sent" x-cloak class="px-4 pb-4 pt-2">
            <div class="flex items-start gap-2.5 text-[12.5px] text-[var(--gazu-success)] bg-[var(--gazu-success-bg)] rounded px-2.5 py-2">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 mt-0.5"><path d="M20 6L9 17l-5-5"/></svg>
                <span class="leading-snug">Дякуємо! Передзвонимо за 5 хв.</span>
            </div>
        </div>

        
        <form x-show="!sent" @submit.prevent="submit()" class="px-4 pb-4 flex flex-col gap-2">
            <input type="text" x-model="name" autocomplete="name" placeholder="Ім'я (необов'язково)"
                   class="w-full px-2.5 py-2 border border-[var(--gazu-line)] rounded text-[13px] outline-none focus:border-[var(--gazu-ink)] transition-colors">
            <input type="tel" x-model="phone" x-ref="phoneInput" required autocomplete="tel" inputmode="tel" placeholder="+38 (0__) ___ __ __"
                   class="w-full px-2.5 py-2 border border-[var(--gazu-line)] rounded text-[14px] gazu-mono outline-none focus:border-[var(--gazu-ink)] transition-colors">

            <div x-show="error" x-cloak class="text-[11px] text-[var(--gazu-danger)]" x-text="error"></div>

            <button type="submit" :disabled="busy"
                    :class="busy ? 'opacity-70 cursor-wait' : 'cursor-pointer hover:bg-[var(--gazu-blue-600)]'"
                    class="w-full py-2 bg-[var(--gazu-blue)] text-white border-0 rounded text-[13px] font-semibold transition-colors inline-flex items-center justify-center gap-1.5">
                <svg x-show="busy" x-cloak class="animate-spin w-3.5 h-3.5" viewBox="0 0 24 24" fill="none">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.25" stroke-width="3"/>
                    <path d="M12 2a10 10 0 0 1 10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                </svg>
                <span x-text="busy ? 'Надсилаю...' : 'Передзвоніть мені'"></span>
            </button>
        </form>
    </div>
</div>
<?php /**PATH /home/lionex/projects/gazu-shop/resources/views/components/gazu/callback-popover.blade.php ENDPATH**/ ?>