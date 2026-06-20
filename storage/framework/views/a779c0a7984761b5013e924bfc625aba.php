<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'warehouseStocks' => null,    // Collection of Inventory rows with .warehouse loaded
    'closestWarehouseId' => null, // geo-detected warehouse ID
    'price' => 0,                 // base product price (fallback when a row has no own price)
    'brand' => null,              // brand name — shown at the top of this column
    'brandUrl' => null,           // optional catalog-filter link for the brand
    'article' => null,            // SKU / OEM article number
    'condition' => 'Новий',       // condition badge — shown at the top of this column
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
    'warehouseStocks' => null,    // Collection of Inventory rows with .warehouse loaded
    'closestWarehouseId' => null, // geo-detected warehouse ID
    'price' => 0,                 // base product price (fallback when a row has no own price)
    'brand' => null,              // brand name — shown at the top of this column
    'brandUrl' => null,           // optional catalog-filter link for the brand
    'article' => null,            // SKU / OEM article number
    'condition' => 'Новий',       // condition badge — shown at the top of this column
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<?php
    $stocks = $warehouseStocks instanceof \Illuminate\Support\Collection ? $warehouseStocks : collect();
    $defaultStock = $closestWarehouseId
        ? $stocks->first(fn ($s) => $s->warehouse_id === $closestWarehouseId && $s->quantity > 0)
        : null;
    $defaultStock ??= $stocks->firstWhere(fn ($s) => $s->quantity > 0);
    $defaultWh = $defaultStock?->warehouse_id;
    $visible = 4;
    $hasMore = $stocks->count() > $visible;
    // warehouse_id => available qty — lets the availability line react to `sel`.
    $stocksJs = $stocks->mapWithKeys(fn ($s) => [
        $s->warehouse_id => max(0, $s->quantity - $s->reserved_quantity),
    ])->all();
?>
<?php if($stocks->isNotEmpty()): ?>
    
    <div class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-[10px] p-5 font-text"
         x-data="{
            sel: <?php echo e($defaultWh ? (int) $defaultWh : 'null'); ?>,
            expanded: false,
            stocks: <?php echo e(\Illuminate\Support\Js::from($stocksJs)); ?>,
            get available() { return this.sel != null && this.stocks[this.sel] != null ? this.stocks[this.sel] : 0; }
         }"
         role="radiogroup" aria-label="Вибір складу для доставки">

        
        <div class="pb-4 mb-4 border-b border-[var(--gazu-line)]">
            <?php if($condition): ?>
                <div class="mb-3"><?php if (isset($component)) { $__componentOriginal06af58769c6e9847f6077713b9c5b4bf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal06af58769c6e9847f6077713b9c5b4bf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.condition-badge','data' => ['value' => ''.e($condition).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.condition-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => ''.e($condition).'']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal06af58769c6e9847f6077713b9c5b4bf)): ?>
<?php $attributes = $__attributesOriginal06af58769c6e9847f6077713b9c5b4bf; ?>
<?php unset($__attributesOriginal06af58769c6e9847f6077713b9c5b4bf); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal06af58769c6e9847f6077713b9c5b4bf)): ?>
<?php $component = $__componentOriginal06af58769c6e9847f6077713b9c5b4bf; ?>
<?php unset($__componentOriginal06af58769c6e9847f6077713b9c5b4bf); ?>
<?php endif; ?></div>
            <?php endif; ?>
            <dl class="flex flex-col gap-2 m-0">
                <?php if($brand): ?>
                    <div class="flex items-baseline gap-3">
                        <dt class="w-20 shrink-0 text-[11px] uppercase tracking-wide font-semibold text-[var(--gazu-graphite)]">Бренд</dt>
                        <dd class="m-0 text-[13px] font-medium text-[var(--gazu-ink)]">
                            <?php if($brandUrl): ?>
                                <a wire:navigate href="<?php echo e($brandUrl); ?>" class="text-[var(--gazu-ink)] no-underline hover:text-[var(--gazu-blue)] transition-colors"><?php echo e($brand); ?></a>
                            <?php else: ?>
                                <?php echo e($brand); ?>

                            <?php endif; ?>
                        </dd>
                    </div>
                <?php endif; ?>
                <?php if($article): ?>
                    <div class="flex items-baseline gap-3">
                        <dt class="w-20 shrink-0 text-[11px] uppercase tracking-wide font-semibold text-[var(--gazu-graphite)]">Артикул</dt>
                        <dd class="m-0">
                            
                            <button type="button"
                                    x-data="{ copied: false }"
                                    @click="navigator.clipboard.writeText(<?php echo \Illuminate\Support\Js::from($article)->toHtml() ?>).then(() => {
                                        copied = true;
                                        window.gazuToast && window.gazuToast('Артикул скопійовано', 'success');
                                        setTimeout(() => copied = false, 1500);
                                    }).catch(() => window.gazuToast && window.gazuToast('Не вдалося скопіювати', 'error'))"
                                    title="Скопіювати артикул"
                                    class="text-[13px] font-medium gazu-mono text-[var(--gazu-ink)] inline-flex items-center gap-1.5 cursor-pointer bg-transparent border-0 p-0 hover:text-[var(--gazu-blue)] transition-colors">
                                <span><?php echo e($article); ?></span>
                                <svg x-show="!copied" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-55 shrink-0"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                                <svg x-show="copied" x-cloak width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="var(--gazu-success)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M20 6 9 17l-5-5"/></svg>
                            </button>
                        </dd>
                    </div>
                <?php endif; ?>
                <div class="flex items-baseline gap-3">
                    <dt class="w-20 shrink-0 text-[11px] uppercase tracking-wide font-semibold text-[var(--gazu-graphite)]">Наявність</dt>
                    <dd class="m-0">
                        <span x-text="available > 0 ? (available + ' шт') : 'Немає'"
                              :class="available > 0 ? 'text-[var(--gazu-success)]' : 'text-[var(--gazu-danger)]'"
                              class="text-[13px] font-medium">—</span>
                    </dd>
                </div>
            </dl>
        </div>

        <div class="text-[11px] uppercase tracking-wide font-semibold text-[var(--gazu-graphite)] mb-3">Доставка зі складу</div>
        <div class="flex flex-col gap-1.5">
            <?php $__currentLoopData = $stocks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $available = max(0, $s->quantity - $s->reserved_quantity);
                    // Ціна складу в грн (конверсія за валютою рядка через Currency::toBase).
                    $sPrice = $s->price !== null ? (float) ($s->display_price ?? $s->price) : (float) $price;
                    $sCompare = $s->compare_at_price !== null ? (float) ($s->display_compare_at_price ?? $s->compare_at_price) : null;
                    $whCity = $s->warehouse->city ?: $s->warehouse->name;
                    $whEta = $s->warehouse->delivery_eta ?: '1-3 дні';
                    $ariaLabel = sprintf(
                        '%s, %s, %s, %s ₴',
                        $whCity, $whEta,
                        $available > 0 ? $available.' шт у наявності' : 'немає в наявності',
                        number_format($sPrice, 0, '.', ' ')
                    );
                ?>
                <button type="button"
                    role="radio"
                    :aria-checked="sel === <?php echo e((int) $s->warehouse_id); ?>"
                    aria-label="<?php echo e($ariaLabel); ?>"
                    @click="sel = <?php echo e((int) $s->warehouse_id); ?>; $dispatch('warehouse-selected', { id: <?php echo e((int) $s->warehouse_id); ?> })"
                    <?php if($idx >= $visible): ?> x-show="expanded" x-transition.opacity.duration.150ms <?php endif; ?>
                    <?php if($available <= 0): echo 'disabled'; endif; ?>
                    :class="sel === <?php echo e((int) $s->warehouse_id); ?> ? 'border-[var(--gazu-ink)] bg-[var(--gazu-ink)] text-[var(--gazu-on-brand)]' : 'border-[var(--gazu-line)] bg-[var(--gazu-surface)] text-[var(--gazu-ink)] hover:border-[var(--gazu-graphite)]'"
                    class="w-full flex items-center justify-between gap-3 px-3 py-2.5 border rounded-md transition-colors text-left min-h-[44px]
                        <?php if($available <= 0): ?> opacity-50 cursor-not-allowed <?php endif; ?>">
                    <div class="flex items-center gap-2.5 min-w-0">
                        
                        <div class="w-6 h-6 rounded-md flex items-center justify-center flex-shrink-0"
                             :class="sel === <?php echo e((int) $s->warehouse_id); ?> ? 'bg-[var(--gazu-surface)] text-[var(--gazu-ink)]' : 'bg-[var(--gazu-mist)] text-[var(--gazu-blue)]'">
                            <svg x-show="sel !== <?php echo e((int) $s->warehouse_id); ?>" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                            <svg x-show="sel === <?php echo e((int) $s->warehouse_id); ?>" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                        </div>
                        <div class="min-w-0">
                            <div class="font-medium text-[13px] truncate inline-flex items-center gap-1.5">
                                <span><?php echo e($whCity); ?></span>
                                <?php if($closestWarehouseId && $s->warehouse_id === $closestWarehouseId): ?>
                                    <span class="text-[9px] gazu-mono px-1 py-0.5 rounded uppercase tracking-wider"
                                          :class="sel === <?php echo e((int) $s->warehouse_id); ?> ? 'bg-[var(--gazu-surface)]/15 text-[var(--gazu-on-brand)]' : 'bg-[var(--gazu-blue-bg,#E0EBFF)] text-[var(--gazu-blue)]'">
                                        <?php echo e(($gazuSettings ?? [])['gazu_warehouse_closest_label'] ?? 'найшвидша відправка'); ?>

                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="text-[11px] opacity-70 truncate">
                                <?php echo e($whEta); ?>

                                <?php if($available > 0): ?> · <?php echo e($available); ?> шт <?php else: ?> · немає <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <?php if($sCompare && $sCompare > $sPrice): ?>
                            <div class="text-[10px] line-through opacity-60"><?php echo e(number_format($sCompare, 0, '.', ' ')); ?> ₴</div>
                        <?php endif; ?>
                        <div class="font-semibold text-[13px] gazu-mono"><?php echo e(number_format($sPrice, 0, '.', ' ')); ?> ₴</div>
                    </div>
                </button>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <?php if($hasMore): ?>
            <button type="button" @click="expanded = !expanded"
                :aria-expanded="expanded"
                aria-label="Показати більше складів"
                class="w-full mt-2 py-2.5 text-[13px] font-medium text-[var(--gazu-ink)] bg-[var(--gazu-mist)] border border-[var(--gazu-line)] rounded-md cursor-pointer hover:bg-[var(--gazu-line-2)] inline-flex items-center justify-center gap-2 transition-colors min-h-[44px]">
                <span x-show="!expanded" class="inline-flex items-center gap-1.5">
                    <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'plus','size' => '14']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'plus','size' => '14']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $attributes = $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $component = $__componentOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?>
                    Показати ще <?php echo e($stocks->count() - $visible); ?> <?php echo e($stocks->count() - $visible === 1 ? 'склад' : 'склади'); ?>

                </span>
                <span x-show="expanded" x-cloak class="inline-flex items-center gap-1.5">
                    <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'minus','size' => '14']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'minus','size' => '14']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $attributes = $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $component = $__componentOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?>
                    Сховати
                </span>
            </button>
        <?php endif; ?>
    </div>
<?php endif; ?>
<?php /**PATH /home/lionex/projects/gazu-shop/resources/views/components/gazu/warehouse-selector.blade.php ENDPATH**/ ?>