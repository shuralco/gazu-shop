<?php $__env->startSection('title', 'Каталог · mobile'); ?>

<?php
    // Brand pills from category brands; "Усі" is always first.
    // Each pill = [slug, label]; first is sentinel for "all".
    $brandPills = collect($availableBrands ?? [])
        ->take(6)
        ->map(function ($b) {
            $slug = is_object($b) ? ($b->manufacturer ?? $b->slug ?? null) : (is_array($b) ? ($b['manufacturer'] ?? $b['slug'] ?? null) : (string) $b);
            $label = is_object($b) ? ($b->label ?? $b->name ?? $slug) : (is_array($b) ? ($b['label'] ?? $b['name'] ?? $slug) : (string) $b);
            return $slug ? [(string) $slug, (string) $label] : null;
        })
        ->filter()
        ->values()
        ->all();
    $pills = array_merge([['', 'Усі']], $brandPills);
    $selectedBrand = request('brand') ?? null;
    if (is_array($selectedBrand)) $selectedBrand = $selectedBrand[0] ?? null;
?>
<?php $__env->startSection('content'); ?>
<div class="max-w-[420px] mx-auto py-4 px-4 pb-20">
    <h1 class="gazu-display text-xl font-semibold mb-2"><?php echo e($category->title ?? 'Каталог'); ?></h1>
    <div class="text-xs text-[var(--gazu-graphite)] mb-3"><?php echo e(plural_uk_count((int) ($totalCount ?? $products->count()), 'товар', 'товари', 'товарів')); ?></div>
    <div class="flex gap-2 mb-3 overflow-x-auto whitespace-nowrap">
        <?php $__currentLoopData = $pills; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => [$pillSlug, $pillLabel]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $isAll = $i === 0;
                $isActive = $isAll ? ! $selectedBrand : $selectedBrand === $pillSlug;
                $url = $isAll
                    ? request()->fullUrlWithQuery(['brand' => null])
                    : request()->fullUrlWithQuery(['brand' => [$pillSlug]]);
            ?>
            <a wire:navigate href="<?php echo e($url); ?>" class="px-3 py-1.5 rounded-full text-xs whitespace-nowrap no-underline <?php echo e($isActive ? 'bg-[var(--gazu-ink)] text-[var(--gazu-on-brand)]' : 'bg-[var(--gazu-surface)] border border-[var(--gazu-line)] text-[var(--gazu-graphite)]'); ?>">
                <?php echo e($pillLabel); ?>

            </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <div class="flex justify-between items-center mb-3">
        <button type="button" class="gazu-btn-outline text-xs py-1.5 px-3"><?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'filter','size' => '14']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'filter','size' => '14']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $attributes = $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $component = $__componentOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?> Фільтри</button>
        <select class="text-xs border border-[var(--gazu-line)] bg-[var(--gazu-surface)] rounded px-2 py-1.5">
            <option>За популярністю</option>
            <option>За ціною</option>
        </select>
    </div>
    <div class="grid grid-cols-2 gap-2.5">
        <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if (isset($component)) { $__componentOriginalb3aad9b8a2236f9a320278758e8a5f6c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb3aad9b8a2236f9a320278758e8a5f6c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.product-card','data' => ['p' => $p,'compact' => true,'eager' => $loop->index < 4]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.product-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['p' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($p),'compact' => true,'eager' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($loop->index < 4)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb3aad9b8a2236f9a320278758e8a5f6c)): ?>
<?php $attributes = $__attributesOriginalb3aad9b8a2236f9a320278758e8a5f6c; ?>
<?php unset($__attributesOriginalb3aad9b8a2236f9a320278758e8a5f6c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb3aad9b8a2236f9a320278758e8a5f6c)): ?>
<?php $component = $__componentOriginalb3aad9b8a2236f9a320278758e8a5f6c; ?>
<?php unset($__componentOriginalb3aad9b8a2236f9a320278758e8a5f6c); ?>
<?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>
<?php echo $__env->make('gazu.partials.mobile-nav', ['active' => 'catalog'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('gazu.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/lionex/projects/gazu-shop/resources/views/gazu/mobile/catalog.blade.php ENDPATH**/ ?>