<?php $__env->startSection('title', \App\Support\SeoTemplates::title('brands')); ?>
<?php $__env->startSection('description', \App\Support\SeoTemplates::description('brands')); ?>

<?php $__env->startSection('content'); ?>
<div class="gazu-container">
    <?php if (isset($component)) { $__componentOriginaldd75f73904e8d7e4a617b590234b9aa0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldd75f73904e8d7e4a617b590234b9aa0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.breadcrumbs','data' => ['items' => [['Головна', route('gazu.home')], 'Усі бренди']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.breadcrumbs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['Головна', route('gazu.home')], 'Усі бренди'])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldd75f73904e8d7e4a617b590234b9aa0)): ?>
<?php $attributes = $__attributesOriginaldd75f73904e8d7e4a617b590234b9aa0; ?>
<?php unset($__attributesOriginaldd75f73904e8d7e4a617b590234b9aa0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldd75f73904e8d7e4a617b590234b9aa0)): ?>
<?php $component = $__componentOriginaldd75f73904e8d7e4a617b590234b9aa0; ?>
<?php unset($__componentOriginaldd75f73904e8d7e4a617b590234b9aa0); ?>
<?php endif; ?>
    <h1 class="gazu-display text-4xl font-semibold m-0 mb-2">Бренди</h1>
    <p class="text-sm text-[var(--gazu-graphite)] mb-7"><?php echo e(plural_uk_count($brands->count(), 'бренд', 'бренди', 'брендів')); ?> у каталозі</p>

    <?php if($brands->isEmpty()): ?>
        <div class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg p-10 text-center">
            <div class="gazu-display text-xl font-semibold mb-2">Брендів поки немає</div>
            <p class="text-sm text-[var(--gazu-graphite)]">Адміністратор може додати бренди у Filament адмінці.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
            <?php $__currentLoopData = $brands; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a wire:navigate href="<?php echo e(route('gazu.brand', ['slug' => $b->slug ?: \Str::slug($b->name)])); ?>"
                   class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg p-5 no-underline text-[var(--gazu-ink)] hover:border-[var(--gazu-line-2)] flex flex-col items-center justify-center gap-2 aspect-[5/3]">
                    <div class="gazu-display font-bold text-lg text-center"><?php echo e($b->name); ?></div>
                    <?php if(($b->products_count ?? 0) > 0): ?>
                        <div class="text-xs text-[var(--gazu-graphite)] gazu-mono"><?php echo e(plural_uk_count((int) $b->products_count, 'товар', 'товари', 'товарів')); ?></div>
                    <?php endif; ?>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('gazu.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/lionex/projects/gazu-shop/resources/views/gazu/brand-list.blade.php ENDPATH**/ ?>