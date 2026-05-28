<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['items' => []]));

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

foreach (array_filter((['items' => []]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="text-[11px] sm:text-[13px] text-[var(--gazu-graphite)] py-2.5 sm:py-4 flex items-center gap-1 sm:gap-2 flex-nowrap whitespace-nowrap gazu-scroll-x">
    <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $it): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php $isLast = $i === count($items) - 1; ?>
        <?php if($i > 0): ?>
            <span class="text-[var(--gazu-line-2)] shrink-0">/</span>
        <?php endif; ?>
        <?php if(is_array($it)): ?>
            <a wire:navigate href="<?php echo e($it[1]); ?>" class="no-underline shrink-0 <?php echo e($isLast ? 'text-[var(--gazu-ink)]' : 'text-[var(--gazu-graphite)] hover:text-[var(--gazu-ink)]'); ?>"><?php echo e($it[0]); ?></a>
        <?php else: ?>
            <span class="<?php echo e($isLast ? 'text-[var(--gazu-ink)] truncate max-w-[55vw] sm:max-w-none' : 'text-[var(--gazu-graphite)] shrink-0'); ?>"><?php echo e($it); ?></span>
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php /**PATH /home/lionex/projects/gazu-shop/resources/views/components/gazu/breadcrumbs.blade.php ENDPATH**/ ?>