<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['p']));

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

foreach (array_filter((['p']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<?php
    $name = is_object($p) ? ($p->name ?? '') : ($p['name'] ?? '');
    $oem = is_object($p) ? ($p->oem ?? $p->sku ?? '') : ($p['oem'] ?? '');
    $brand = is_object($p) ? ($p->brand ?? $p->manufacturer ?? '') : ($p['brand'] ?? '');
    $image = is_object($p) ? ($p->image_kind ?? 'filter') : ($p['image_kind'] ?? 'filter');
    $price = is_object($p) ? (float) ($p->price ?? 0) : (float) ($p['price'] ?? 0);
    $oldPrice = is_object($p) ? ($p->old_price ?? null) : ($p['old_price'] ?? null);
    $oldPrice = ((float) $oldPrice > (float) $price) ? $oldPrice : null; // ignore 0 / ≤ price
    $qty = is_object($p) ? (int) ($p->qty ?? $p->quantity ?? 0) : (int) ($p['qty'] ?? 0);
    $rating = is_object($p) ? (float) ($p->rating ?? 0) : 0;
    $reviews = is_object($p) ? (int) ($p->reviews ?? $p->reviews_count ?? 0) : 0;
    $url = is_object($p) ? ($p->url ?? '#') : ($p['url'] ?? '#');
    $productId = is_object($p) ? ($p->id ?? null) : ($p['id'] ?? null);
    $excerpt = is_object($p) ? ($p->excerpt ?? null) : null;
    if (is_array($excerpt)) $excerpt = $excerpt['uk'] ?? null;
?>
<div class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg p-3 flex gap-3 hover:border-[var(--gazu-line-2)] transition-colors">
    <a wire:navigate href="<?php echo e($url); ?>" class="shrink-0 w-24 h-24 sm:w-32 sm:h-32 bg-[var(--gazu-paper)] rounded-md flex items-center justify-center relative no-underline overflow-hidden p-1.5">
        <?php if (isset($component)) { $__componentOriginale68023f03052ea26bcc9e709ab0711bb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale68023f03052ea26bcc9e709ab0711bb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.part-image','data' => ['kind' => ''.e($image).'','seed' => $productId,'fit' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.part-image'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['kind' => ''.e($image).'','seed' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($productId),'fit' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale68023f03052ea26bcc9e709ab0711bb)): ?>
<?php $attributes = $__attributesOriginale68023f03052ea26bcc9e709ab0711bb; ?>
<?php unset($__attributesOriginale68023f03052ea26bcc9e709ab0711bb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale68023f03052ea26bcc9e709ab0711bb)): ?>
<?php $component = $__componentOriginale68023f03052ea26bcc9e709ab0711bb; ?>
<?php unset($__componentOriginale68023f03052ea26bcc9e709ab0711bb); ?>
<?php endif; ?>
        <?php if($oem): ?>
            <span class="absolute top-1 left-1 px-1.5 py-0.5 gazu-mono text-[9px] text-[var(--gazu-graphite)] bg-[var(--gazu-surface)]/90 border border-[var(--gazu-line)] rounded"><?php echo e($oem); ?></span>
        <?php endif; ?>
    </a>
    <div class="flex-1 min-w-0 flex flex-col gap-1">
        <div class="flex items-center gap-1.5 flex-wrap">
            <?php if (isset($component)) { $__componentOriginal06af58769c6e9847f6077713b9c5b4bf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal06af58769c6e9847f6077713b9c5b4bf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.condition-badge','data' => ['value' => 'Новий']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.condition-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['value' => 'Новий']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal06af58769c6e9847f6077713b9c5b4bf)): ?>
<?php $attributes = $__attributesOriginal06af58769c6e9847f6077713b9c5b4bf; ?>
<?php unset($__attributesOriginal06af58769c6e9847f6077713b9c5b4bf); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal06af58769c6e9847f6077713b9c5b4bf)): ?>
<?php $component = $__componentOriginal06af58769c6e9847f6077713b9c5b4bf; ?>
<?php unset($__componentOriginal06af58769c6e9847f6077713b9c5b4bf); ?>
<?php endif; ?>
            <span class="gazu-mono text-[11px] text-[var(--gazu-graphite)]"><?php echo e($brand); ?></span>
            <?php if($rating > 0 && $reviews > 0): ?>
                <span class="text-[11px] text-[var(--gazu-graphite)]">· <?php echo e(number_format($rating, 1)); ?> (<?php echo e($reviews); ?>)</span>
            <?php endif; ?>
        </div>
        <a wire:navigate href="<?php echo e($url); ?>" class="text-[14px] text-[var(--gazu-ink)] leading-snug font-semibold no-underline line-clamp-2"><?php echo e($name); ?></a>
        <?php if($excerpt): ?>
            <p class="text-[12px] text-[var(--gazu-graphite)] m-0 line-clamp-2 hidden sm:block"><?php echo e($excerpt); ?></p>
        <?php endif; ?>
        <?php if (isset($component)) { $__componentOriginalad88f7cb9026c66df0388f34b883b8a5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalad88f7cb9026c66df0388f34b883b8a5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.stock','data' => ['qty' => ''.e($qty).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.stock'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['qty' => ''.e($qty).'']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalad88f7cb9026c66df0388f34b883b8a5)): ?>
<?php $attributes = $__attributesOriginalad88f7cb9026c66df0388f34b883b8a5; ?>
<?php unset($__attributesOriginalad88f7cb9026c66df0388f34b883b8a5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalad88f7cb9026c66df0388f34b883b8a5)): ?>
<?php $component = $__componentOriginalad88f7cb9026c66df0388f34b883b8a5; ?>
<?php unset($__componentOriginalad88f7cb9026c66df0388f34b883b8a5); ?>
<?php endif; ?>
    </div>
    <div class="shrink-0 flex flex-col items-end gap-2 min-w-[160px]">
        <div class="flex flex-col items-end">
            <?php if($oldPrice): ?>
                <span class="text-xs text-[var(--gazu-muted)] line-through"><?php echo e(number_format((float) $oldPrice, 0, '.', ' ')); ?> ₴</span>
            <?php endif; ?>
            <span class="gazu-display text-[22px] font-bold text-[var(--gazu-ink)] leading-none"><?php echo e(number_format($price, 0, '.', ' ')); ?> <span class="text-sm font-medium text-[var(--gazu-graphite)]">₴</span></span>
        </div>
        <?php if($productId && $qty > 0): ?>
            <button type="button"
                    x-data="{ busy: false, added: false }"
                    @click.prevent="
                        if (busy) return; busy = true;
                        fetch('<?php echo e(route('gazu.cart.add')); ?>', {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': window.GAZU_CSRF, 'Accept': 'application/json' },
                            body: new URLSearchParams({ product_id: '<?php echo e($productId); ?>', quantity: '1' })
                        }).then(r => r.json()).then(d => {
                            if (d.ok) {
                                // Drawer показує результат — toast прибрано.
                                window.dispatchEvent(new CustomEvent('cart-updated', { detail: { count: d.count, qtyTotal: d.qtyTotal, total: d.total } }));
                                added = true;
                                setTimeout(() => added = false, 1500);
                            } else {
                                window.gazuToast && window.gazuToast(d.message || 'Не вдалося додати', 'error');
                            }
                        }).catch(() => window.gazuToast && window.gazuToast('Помилка з\'єднання', 'error'))
                          .finally(() => { busy = false; });
                    "
                    :class="added ? 'bg-[var(--gazu-success)]' : 'bg-[var(--gazu-ink)] hover:bg-[var(--gazu-ink-2)]'"
                    :disabled="busy"
                    class="w-full px-4 py-2 text-[var(--gazu-on-brand)] border-0 rounded-md text-[13px] font-medium cursor-pointer inline-flex items-center justify-center gap-1.5 whitespace-nowrap transition-colors">
                <span x-show="!added"><?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'cart','size' => '14']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'cart','size' => '14']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $attributes = $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $component = $__componentOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?> У кошик</span>
                <span x-show="added" x-cloak>✓ Додано</span>
            </button>
        <?php else: ?>
            <a wire:navigate href="<?php echo e($url); ?>" class="w-full px-4 py-2 bg-[var(--gazu-ink)] text-[var(--gazu-on-brand)] border-0 rounded-md text-[13px] font-medium cursor-pointer inline-flex items-center justify-center gap-1.5 whitespace-nowrap hover:bg-[var(--gazu-ink-2)] no-underline">Деталі</a>
        <?php endif; ?>
    </div>
</div>
<?php /**PATH /home/lionex/projects/gazu-shop/resources/views/components/gazu/product-row.blade.php ENDPATH**/ ?>