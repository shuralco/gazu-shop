<?php
    $active = $active ?? 'spec';
    $tabs = $tabs ?? [
        ['spec', 'Характеристики', null],
        ['compat', 'Сумісність', null],
        ['analogs', 'Аналоги', null],
        ['reviews', 'Відгуки', null],
        ['delivery', 'Доставка та оплата', null],
    ];
?>
<div class="border-b border-[var(--gazu-line)] flex gap-1 font-text mt-3 overflow-x-auto whitespace-nowrap">
    <?php $__currentLoopData = $tabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$k, $l, $c]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <button type="button"
                class="px-4.5 py-3.5 bg-transparent border-0 text-sm cursor-pointer inline-flex items-center gap-1.5 <?php echo e($active === $k ? 'text-[var(--gazu-ink)] font-semibold' : 'text-[var(--gazu-graphite)]'); ?>"
                style="border-bottom: 2px solid <?php echo e($active === $k ? 'var(--gazu-ink)' : 'transparent'); ?>;">
            <?php echo e($l); ?>

            <?php if($c !== null && $c > 0): ?><span class="text-[11px] text-[var(--gazu-muted)] gazu-mono"><?php echo e($c); ?></span><?php endif; ?>
        </button>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php /**PATH /home/lionex/projects/gazu-shop/resources/views/gazu/partials/product-tabs.blade.php ENDPATH**/ ?>