<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'p',               // Product model (with inventory.warehouse eager-loaded)
    'basePrice' => 0,  // fallback price when an inventory row has no own price
    'groupActive' => false, // персональна гуртова ціна → склад не перебиває ціну
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
    'p',               // Product model (with inventory.warehouse eager-loaded)
    'basePrice' => 0,  // fallback price when an inventory row has no own price
    'groupActive' => false, // персональна гуртова ціна → склад не перебиває ціну
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<?php
    // Pull inventory rows that have a warehouse + at least one unit on hand.
    $stocks = collect();
    if (is_object($p) && method_exists($p, 'inventory') && $p->relationLoaded('inventory')) {
        $stocks = $p->inventory
            ->filter(fn ($i) => $i->warehouse !== null)
            ->sortBy('warehouse_id')
            ->values();
    }
    $defaultStock = $stocks->firstWhere(fn ($s) => max(0, $s->quantity - $s->reserved_quantity) > 0)
        ?? $stocks->first();
    $defaultWh = $defaultStock?->warehouse_id;
?>
<?php if($stocks->isNotEmpty()): ?>
    
    <div x-data="{ open: false, sel: <?php echo e($defaultWh ? (int) $defaultWh : 'null'); ?> }"
         @mouseenter.window="open = false"
         class="absolute left-0 right-0 top-full z-30 pointer-events-none">
        
        <div class="pointer-events-auto opacity-0 group-hover:opacity-100 transition-all duration-150
                    translate-y-1 group-hover:translate-y-0"
             :class="open ? '!opacity-100 !translate-y-0 pointer-events-auto' : ''">
            <div class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg shadow-[0_12px_32px_-12px_rgba(14,27,44,0.35)] p-3 mt-1">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] uppercase tracking-wider font-semibold text-[var(--gazu-graphite)]">
                        Доставка зі складу
                    </span>
                    <button type="button" @click.stop="open = false"
                            x-show="open" x-cloak
                            class="text-[var(--gazu-graphite)] hover:text-[var(--gazu-ink)] bg-transparent border-0 p-0 cursor-pointer leading-none text-base">
                        ×
                    </button>
                </div>
                <div class="flex flex-col gap-1">
                    <?php $__currentLoopData = $stocks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $available = max(0, $s->quantity - $s->reserved_quantity);
                            // Ціна складу в грн (display_price конвертує за валютою рядка).
                            // Для гурт-клієнта гуртова ціна головніша за склад → basePrice.
                            $sPrice    = $groupActive
                                ? (float) $basePrice
                                : ($s->price !== null ? (float) ($s->display_price ?? $s->price) : (float) $basePrice);
                            $whCity    = $s->warehouse->city ?: $s->warehouse->name;
                            $whEta     = $s->warehouse->delivery_eta ?: '1-3 дні';
                            $disabled  = $available <= 0;
                        ?>
                        <button type="button"
                                @click.stop.prevent="
                                    sel = <?php echo e((int) $s->warehouse_id); ?>;
                                    $dispatch('gazu:card-warehouse', { productId: <?php echo e((int) ($p->id ?? 0)); ?>, warehouseId: <?php echo e((int) $s->warehouse_id); ?>, price: <?php echo e((float) $sPrice); ?>, qty: <?php echo e((int) $available); ?> });
                                "
                                <?php if($disabled): echo 'disabled'; endif; ?>
                                :class="sel === <?php echo e((int) $s->warehouse_id); ?> ? 'border-[var(--gazu-ink)] bg-[var(--gazu-mist)]' : 'border-transparent hover:bg-[var(--gazu-mist)]'"
                                class="w-full flex items-center justify-between gap-3 px-2 py-1.5 border rounded-md text-left transition-colors min-h-[36px]
                                    <?php if($disabled): ?> opacity-50 cursor-not-allowed <?php endif; ?>">
                            <div class="flex items-center gap-2 min-w-0">
                                <div class="w-5 h-5 rounded flex items-center justify-center flex-shrink-0"
                                     :class="sel === <?php echo e((int) $s->warehouse_id); ?> ? 'bg-[var(--gazu-ink)] text-[var(--gazu-on-brand)]' : 'bg-[var(--gazu-mist)] text-[var(--gazu-blue)]'">
                                    <svg x-show="sel !== <?php echo e((int) $s->warehouse_id); ?>" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                                    <svg x-show="sel === <?php echo e((int) $s->warehouse_id); ?>" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                </div>
                                <div class="min-w-0">
                                    <div class="font-medium text-[12px] truncate text-[var(--gazu-ink)] leading-tight"><?php echo e($whCity); ?></div>
                                    <div class="text-[10px] text-[var(--gazu-graphite)] truncate leading-tight">
                                        <?php echo e($whEta); ?>

                                        <?php if($available > 0): ?> · <?php echo e($available); ?> шт <?php else: ?> · немає <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <div class="font-semibold text-[12px] gazu-mono text-[var(--gazu-ink)]">
                                    <?php echo e(number_format($sPrice, 0, '.', ' ')); ?> ₴
                                </div>
                            </div>
                        </button>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>
        
        <button type="button"
                @click.stop.prevent="open = !open"
                class="md:hidden pointer-events-auto absolute -top-[95px] right-2 z-30
                       text-[10px] gazu-mono uppercase tracking-wider px-2 py-1
                       bg-[var(--gazu-surface)] shadow-[0_1px_4px_-1px_rgba(14,27,44,0.20)]
                       rounded-md text-[var(--gazu-blue)] font-semibold
                       hover:text-[var(--gazu-ink)] cursor-pointer
                       inline-flex items-center gap-1">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
            <span x-show="!open">Склади</span>
            <span x-show="open" x-cloak>×</span>
        </button>
    </div>
<?php endif; ?>
<?php /**PATH /home/lionex/projects/gazu-shop/resources/views/components/gazu/product-card-stocks.blade.php ENDPATH**/ ?>