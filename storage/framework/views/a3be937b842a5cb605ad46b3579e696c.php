<?php $__env->startSection('title', 'Порівняння товарів — GAZU'); ?>
<?php $__env->startSection('description', 'Порівняйте характеристики обраних товарів поруч у зручній таблиці.'); ?>

<?php $__env->startSection('content'); ?>
<div class="gazu-container">
    <?php if (isset($component)) { $__componentOriginaldd75f73904e8d7e4a617b590234b9aa0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldd75f73904e8d7e4a617b590234b9aa0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.breadcrumbs','data' => ['items' => [['Головна', route('gazu.home')], 'Порівняння']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.breadcrumbs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['Головна', route('gazu.home')], 'Порівняння'])]); ?>
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
    <h1 class="gazu-display text-2xl sm:text-3xl md:text-4xl font-semibold m-0 mb-2">Порівняння товарів</h1>

    <?php if(session('flash_message')): ?>
        <div class="bg-[var(--gazu-success-bg)] text-[var(--gazu-success)] px-4 py-2 rounded-md mb-4 text-sm">
            <?php echo e(session('flash_message')); ?>

        </div>
    <?php endif; ?>

    <?php if(empty($products) || (is_object($products) && $products->isEmpty())): ?>
        <div class="bg-white border border-[var(--gazu-line)] rounded-lg p-10 text-center">
            <div class="gazu-display text-xl font-semibold mb-2">Список порівняння порожній</div>
            <p class="text-sm text-[var(--gazu-graphite)] mb-5">Натискайте «Порівняти» на картках товарів, щоб додати їх сюди.</p>
            <a wire:navigate href="<?php echo e(route('gazu.catalog')); ?>" class="gazu-btn-primary no-underline">До каталогу</a>
        </div>
    <?php else: ?>
        <p class="text-sm text-[var(--gazu-graphite)] mb-5"><?php echo e(plural_uk_count($products->count(), 'товар', 'товари', 'товарів')); ?> у порівнянні</p>

        <div class="overflow-x-auto border border-[var(--gazu-line)] rounded-lg bg-white">
            <table class="w-full text-sm border-collapse">
                <thead>
                    <tr class="border-b border-[var(--gazu-line)]">
                        <th class="text-left p-3 align-bottom font-medium text-[var(--gazu-graphite)] w-40">Характеристика</th>
                        <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <th class="p-3 align-bottom text-left min-w-[180px]">
                                <a wire:navigate href="<?php echo e(route('gazu.product.show', $product->slug ?? $product->id)); ?>"
                                   class="gazu-display font-semibold no-underline text-[var(--gazu-ink)] hover:text-[var(--gazu-blue)]">
                                    <?php echo e($product->title); ?>

                                </a>
                                <form method="POST" action="<?php echo e(route('gazu.comparison.remove')); ?>" class="mt-2">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="product_id" value="<?php echo e($product->id); ?>">
                                    <button type="submit" class="text-xs text-[var(--gazu-graphite)] hover:text-[var(--gazu-danger,#c0392b)] underline">
                                        Прибрати
                                    </button>
                                </form>
                            </th>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $attributes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="border-b border-[var(--gazu-line)] last:border-0">
                            <td class="p-3 font-medium text-[var(--gazu-graphite)]"><?php echo e($row['name']); ?></td>
                            <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <td class="p-3"><?php echo e($row['values'][$product->id] ?? '—'); ?></td>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>

        <form method="POST" action="<?php echo e(route('gazu.comparison.clear')); ?>" class="mt-5">
            <?php echo csrf_field(); ?>
            <button type="submit" class="text-sm text-[var(--gazu-graphite)] hover:text-[var(--gazu-ink)] underline">
                Очистити порівняння
            </button>
        </form>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('gazu.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/lionex/projects/gazu-shop/modules/comparison/resources/views/index.blade.php ENDPATH**/ ?>