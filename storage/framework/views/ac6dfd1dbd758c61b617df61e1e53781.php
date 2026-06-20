<?php $__env->startSection('title', $landing->meta_title ?: ($landing->title.' — GAZU')); ?>

<?php $__env->startPush('head'); ?>
    <?php if($landing->meta_description): ?>
        <meta name="description" content="<?php echo e($landing->meta_description); ?>">
    <?php endif; ?>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="gazu-container">
    <?php if (isset($component)) { $__componentOriginaldd75f73904e8d7e4a617b590234b9aa0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldd75f73904e8d7e4a617b590234b9aa0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.breadcrumbs','data' => ['items' => [
        ['Головна', route('gazu.home')],
        ['Каталог', route('gazu.catalog')],
        $landing->title,
    ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.breadcrumbs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
        ['Головна', route('gazu.home')],
        ['Каталог', route('gazu.catalog')],
        $landing->title,
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

    
    <header class="mb-6 sm:mb-8 max-w-3xl">
        <h1 class="gazu-display text-3xl sm:text-4xl font-semibold text-[var(--gazu-ink)] m-0 mb-2 leading-tight">
            <?php echo e($landing->h1 ?: $landing->title); ?>

        </h1>

        <?php if($landing->intro_html): ?>
            <div class="prose prose-sm mt-4 text-[var(--gazu-graphite)] max-w-none">
                <?php echo $landing->intro_html; ?>

            </div>
        <?php endif; ?>
    </header>

    
    <?php
        $hasAnyApplied = ($appliedFilters->count() > 0) || $landing->category || $landing->brand;
    ?>
    <?php if($landing->show_applied_filters && $hasAnyApplied): ?>
        <div class="mb-5 flex flex-wrap items-center gap-2 text-sm">
            <span class="text-[var(--gazu-graphite)]">Фільтри:</span>
            <?php if($landing->category): ?>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-[var(--gazu-ink)]/5 ring-1 ring-[var(--gazu-line)] text-[var(--gazu-ink)]">
                    <span class="text-xs text-[var(--gazu-graphite)]">Категорія:</span>
                    <span class="font-medium"><?php echo e(is_array($landing->category->title) ? ($landing->category->title['uk'] ?? '') : $landing->category->title); ?></span>
                </span>
            <?php endif; ?>
            <?php if($landing->brand): ?>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-[var(--gazu-ink)]/5 ring-1 ring-[var(--gazu-line)] text-[var(--gazu-ink)]">
                    <span class="text-xs text-[var(--gazu-graphite)]">Бренд:</span>
                    <span class="font-medium"><?php echo e(is_array($landing->brand->name) ? ($landing->brand->name['uk'] ?? '') : $landing->brand->name); ?></span>
                </span>
            <?php endif; ?>
            <?php $__currentLoopData = $appliedFilters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $g = $f->filterGroup;
                    $gTitle = $g ? (is_array($g->title) ? ($g->title['uk'] ?? '') : $g->title) : null;
                    $fTitle = is_array($f->title) ? ($f->title['uk'] ?? '') : $f->title;
                ?>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-[var(--gazu-paper)] ring-1 ring-[var(--gazu-line)] text-[var(--gazu-ink)]">
                    <?php if($gTitle): ?>
                        <span class="text-xs text-[var(--gazu-graphite)]"><?php echo e($gTitle); ?>:</span>
                    <?php endif; ?>
                    <span class="font-medium"><?php echo e($fTitle); ?></span>
                </span>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>

    
    <?php if($products->count() === 0): ?>
        <div class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-xl p-10 text-center">
            <p class="text-[var(--gazu-graphite)]">За цими фільтрами товарів не знайдено.</p>
        </div>
    <?php else: ?>
        <p class="text-sm text-[var(--gazu-graphite)] mb-4">Знайдено: <strong><?php echo e($products->total()); ?></strong> товарів</p>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4">
            <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if (isset($component)) { $__componentOriginalb3aad9b8a2236f9a320278758e8a5f6c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb3aad9b8a2236f9a320278758e8a5f6c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.product-card','data' => ['p' => $p]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.product-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['p' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($p)]); ?>
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

        <div class="mt-6">
            <?php echo e($products->withQueryString()->links()); ?>

        </div>
    <?php endif; ?>

    
    <?php if($landing->outro_html): ?>
        <article class="mt-10 pt-6 border-t border-[var(--gazu-line)] prose prose-sm max-w-3xl">
            <?php echo $landing->outro_html; ?>

        </article>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('gazu.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/lionex/projects/gazu-shop/resources/views/gazu/landing/show.blade.php ENDPATH**/ ?>