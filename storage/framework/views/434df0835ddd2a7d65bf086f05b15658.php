<?php $__env->startSection('title', $title . ' — GAZU'); ?>
<?php $__env->startSection('description', $intro ?? $title); ?>

<?php $__env->startSection('content'); ?>
<div class="gazu-container py-8 max-w-3xl">
    <?php if (isset($component)) { $__componentOriginaldd75f73904e8d7e4a617b590234b9aa0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldd75f73904e8d7e4a617b590234b9aa0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.breadcrumbs','data' => ['items' => [['Головна', route('gazu.home')], $title]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.breadcrumbs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['Головна', route('gazu.home')], $title])]); ?>
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
    <h1 class="gazu-display text-4xl font-semibold text-[var(--gazu-ink)] m-0 mb-3"><?php echo e($title); ?></h1>
    <?php if($intro ?? false): ?>
        <p class="text-base text-[var(--gazu-graphite)] leading-relaxed mb-7 max-w-2xl"><?php echo e($intro); ?></p>
    <?php endif; ?>

    <article class="bg-white border border-[var(--gazu-line)] rounded-xl p-8 space-y-5 text-[15px] leading-relaxed text-[var(--gazu-ink)]">
        <?php if(! empty($content_html ?? null)): ?>
            <div class="gazu-prose"><?php echo $content_html; ?></div>
        <?php endif; ?>
        <?php $__currentLoopData = $sections ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if(isset($sec['title'])): ?>
                <h2 class="gazu-display text-xl font-semibold m-0 mt-2"><?php echo e($sec['title']); ?></h2>
            <?php endif; ?>
            <?php if(isset($sec['body'])): ?>
                <div class="text-[var(--gazu-graphite)]"><?php echo nl2br(e($sec['body'])); ?></div>
            <?php endif; ?>
            <?php if(isset($sec['list']) && is_array($sec['list'])): ?>
                <ul class="list-disc list-inside text-[var(--gazu-graphite)] space-y-1.5">
                    <?php $__currentLoopData = $sec['list']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($item); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </article>

    <div class="mt-6 text-sm text-[var(--gazu-muted)]">
        Оновлено: <?php echo e(\Carbon\Carbon::parse($updated ?? now())->translatedFormat('d F Y')); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('gazu.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/lionex/projects/gazu-shop/resources/views/gazu/info/page.blade.php ENDPATH**/ ?>