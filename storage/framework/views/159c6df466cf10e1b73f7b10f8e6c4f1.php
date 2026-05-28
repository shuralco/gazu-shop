<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['qty' => 0]));

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

foreach (array_filter((['qty' => 0]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<?php if($qty > 5): ?>
    <span class="text-xs text-[var(--gazu-success)] inline-flex items-center gap-1 whitespace-nowrap">
        <span class="w-1.5 h-1.5 rounded-full bg-[var(--gazu-success)] shrink-0"></span> В наявності
    </span>
<?php elseif($qty > 0): ?>
    <span class="text-xs text-[var(--gazu-warn)] inline-flex items-center gap-1 whitespace-nowrap">
        <span class="w-1.5 h-1.5 rounded-full bg-[var(--gazu-warn)] shrink-0"></span> Залишилось <?php echo e($qty); ?>

    </span>
<?php else: ?>
    <span class="text-xs text-[var(--gazu-muted)] inline-flex items-center gap-1 whitespace-nowrap">
        <span class="w-1.5 h-1.5 rounded-full bg-[var(--gazu-muted)] shrink-0"></span> Під замовлення
    </span>
<?php endif; ?>
<?php /**PATH /home/lionex/projects/gazu-shop/resources/views/components/gazu/stock.blade.php ENDPATH**/ ?>