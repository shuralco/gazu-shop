<?php
    // Note: the category itself is NOT shown as an "active filter" chip — on a
    // category page (/{slug}) the category is the URL, not a removable filter,
    // so the chip duplicated the H1/breadcrumbs and its × had no useful target.
    $chips = [];
    if (request()->filled('q')) {
        $chips[] = ['label' => 'Пошук: ' . request('q'), 'remove' => 'q'];
    }
    foreach ((array) request('brand', []) as $b) {
        $chips[] = ['label' => $b, 'remove' => 'brand', 'value' => $b];
    }
    if (request()->filled('min')) {
        $chips[] = ['label' => 'від ' . request('min') . ' ₴', 'remove' => 'min'];
    }
    if (request()->filled('max')) {
        $chips[] = ['label' => 'до ' . request('max') . ' ₴', 'remove' => 'max'];
    }
    if (request('stock') === 'in') {
        $chips[] = ['label' => 'В наявності', 'remove' => 'stock'];
    }
?>

<?php if(!empty($chips)): ?>
    <div class="flex flex-wrap gap-2 py-3.5">
        <?php $__currentLoopData = $chips; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $chip): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $params = request()->all();
                if ($chip['remove'] === 'brand' && isset($chip['value'])) {
                    $params['brand'] = array_filter((array) ($params['brand'] ?? []), fn ($x) => $x !== $chip['value']);
                    if (empty($params['brand'])) unset($params['brand']);
                } else {
                    unset($params[$chip['remove']]);
                }
                unset($params['page']);
            ?>
            <a wire:navigate href="<?php echo e(url()->current() . (count($params) ? '?' . http_build_query($params) : '')); ?>"
               class="inline-flex items-center gap-1.5 px-2.5 py-1.5 bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-2xl text-xs text-[var(--gazu-ink)] no-underline hover:border-[var(--gazu-line-2)]">
                <?php echo e($chip['label']); ?> <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'close','size' => '12','stroke' => 'var(--gazu-graphite)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'close','size' => '12','stroke' => 'var(--gazu-graphite)']); ?>
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
            </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <a wire:navigate href="<?php echo e(url()->current()); ?>" class="bg-transparent border-0 text-[var(--gazu-danger)] text-xs cursor-pointer px-2.5 py-1.5 no-underline">Очистити все</a>
    </div>
<?php endif; ?>
<?php /**PATH /home/lionex/projects/gazu-shop/resources/views/gazu/partials/active-filters.blade.php ENDPATH**/ ?>