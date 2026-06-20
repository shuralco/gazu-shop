<?php
    $brandSeoVars = [
        'name' => $brand->name ?? 'Бренд',
        'count' => plural_uk_count($productsCount ?? 0, 'товар', 'товари', 'товарів'),
    ];
?>
<?php $__env->startSection('title', \App\Support\SeoTemplates::title('brand', $brandSeoVars)); ?>
<?php $__env->startSection('description', \App\Support\SeoTemplates::description('brand', $brandSeoVars)); ?>

<?php $__env->startSection('content'); ?>
<div class="gazu-container">
    <?php if (isset($component)) { $__componentOriginaldd75f73904e8d7e4a617b590234b9aa0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldd75f73904e8d7e4a617b590234b9aa0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.breadcrumbs','data' => ['items' => [
        ['Головна', route('gazu.home')],
        ['Бренди', route('gazu.brand')],
        $brand->name,
    ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.breadcrumbs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
        ['Головна', route('gazu.home')],
        ['Бренди', route('gazu.brand')],
        $brand->name,
    ])]); ?>
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

    <section class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-xl p-5 sm:p-8 mb-7 gazu-grid-brand-hero">
        <div class="w-24 h-24 sm:w-32 sm:h-32 bg-[var(--gazu-paper)] rounded-lg flex items-center justify-center gazu-display text-2xl sm:text-3xl font-bold text-[var(--gazu-ink)]">
            <?php if($brand->logo): ?>
                <img src="<?php echo e(Str::startsWith($brand->logo, 'http') ? $brand->logo : asset('storage/'.$brand->logo)); ?>"
                     alt="<?php echo e($brand->name); ?>" class="max-w-full max-h-full object-contain">
            <?php else: ?>
                <?php echo e($brand->name); ?>

            <?php endif; ?>
        </div>
        <div>
            <h1 class="gazu-display text-3xl font-semibold m-0 mb-2"><?php echo e($brand->name); ?></h1>
            <?php if(! empty($brand->description)): ?>
                <p class="text-sm text-[var(--gazu-graphite)] leading-relaxed m-0"><?php echo e($brand->description); ?></p>
            <?php else: ?>
                <p class="text-sm text-[var(--gazu-graphite)] leading-relaxed m-0">
                    <?php echo e($brand->name); ?> — <?php echo e($gazuSettings['gazu_brand_fallback_description'] ?? 'один з виробників, представлених у каталозі GAZU. Перейдіть до повного списку товарів цієї марки нижче.'); ?>

                </p>
            <?php endif; ?>
        </div>
        <div class="flex flex-col gap-2 text-center">
            <div class="gazu-display text-3xl font-bold text-[var(--gazu-ink)]"><?php echo e(number_format($productsCount, 0, '.', ' ')); ?></div>
            <div class="text-xs text-[var(--gazu-graphite)]"><?php echo e(plural_uk($productsCount, 'артикул', 'артикули', 'артикулів')); ?> у каталозі</div>
            <a wire:navigate href="<?php echo e(route('gazu.catalog', ['brand' => [$brand->name]])); ?>" class="gazu-btn-primary mt-2 no-underline">Дивитись каталог</a>
        </div>
    </section>

    <?php if($brandCategories->isNotEmpty()): ?>
        <h2 class="gazu-display text-2xl font-semibold m-0 mb-4"><?php echo e($brand->name); ?> за категоріями</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-7">
            <?php $__currentLoopData = $brandCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $catSlug = $cat->slug ?: $cat->id;
                    $catName = $cat->title ?? $cat->name ?? 'Категорія';
                ?>
                <a wire:navigate href="<?php echo e(url('/'.$catSlug).'?brand[]='.urlencode($brand->name)); ?>"
                   class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg p-4 no-underline text-[var(--gazu-ink)] hover:border-[var(--gazu-line-2)]">
                    <div class="font-medium"><?php echo e($catName); ?></div>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>

    <h2 class="gazu-display text-2xl font-semibold m-0 mb-4">
        <?php if($products->count() > 0): ?>
            Топ товари <?php echo e($brand->name); ?>

        <?php else: ?>
            Каталог <?php echo e($brand->name); ?> порожній
        <?php endif; ?>
    </h2>

    <?php if($products->isEmpty()): ?>
        <div class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg p-10 text-center">
            <div class="gazu-display text-xl font-semibold mb-2">Зараз немає товарів</div>
            <p class="text-sm text-[var(--gazu-graphite)] mb-4">Скоро тут зʼявляться оновлення асортименту.</p>
            <a wire:navigate href="<?php echo e(route('gazu.catalog')); ?>" class="gazu-btn-outline no-underline">Усі товари</a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3.5">
            <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if (isset($component)) { $__componentOriginalb3aad9b8a2236f9a320278758e8a5f6c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb3aad9b8a2236f9a320278758e8a5f6c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.product-card','data' => ['p' => $p,'compact' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.product-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['p' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($p),'compact' => true]); ?>
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
        <?php if($productsCount > $products->count()): ?>
            <div class="text-center mt-6">
                <a wire:navigate href="<?php echo e(route('gazu.catalog', ['brand' => [$brand->name]])); ?>" class="gazu-btn-outline no-underline">
                    Усі <?php echo e(number_format($productsCount, 0, '.', ' ')); ?> <?php echo e(plural_uk($productsCount, 'товар', 'товари', 'товарів')); ?> <?php echo e($brand->name); ?> →
                </a>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('gazu.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/lionex/projects/gazu-shop/resources/views/gazu/brand.blade.php ENDPATH**/ ?>