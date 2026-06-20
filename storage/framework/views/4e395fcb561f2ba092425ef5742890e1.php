<?php
    $title = is_array($page->title) ? ($page->title['uk'] ?? 'Сторінка') : (string) ($page->title ?? 'Сторінка');
    $content = is_array($page->content ?? null) ? ($page->content['uk'] ?? '') : (string) ($page->content ?? '');
    $excerpt = is_array($page->excerpt ?? null) ? ($page->excerpt['uk'] ?? '') : (string) ($page->excerpt ?? '');
    $pageMetaTitle = $page->meta_title ? (is_array($page->meta_title) ? ($page->meta_title['uk'] ?? '') : (string) $page->meta_title) : '';
    $pageMetaDescription = $page->meta_description ? (is_array($page->meta_description) ? ($page->meta_description['uk'] ?? '') : (string) $page->meta_description) : '';
    $slugUk = is_array($page->slug) ? ($page->slug['uk'] ?? '') : (string) $page->slug;
    $isNarrow = ($page->layout ?? 'full') !== 'full';
?>

<?php $__env->startSection('title', $pageMetaTitle !== '' ? $pageMetaTitle : \App\Support\SeoTemplates::title('page', ['name' => $title])); ?>
<?php $__env->startSection('description', $pageMetaDescription !== '' ? $pageMetaDescription : ($excerpt ?: \App\Support\SeoTemplates::description('page', ['name' => $title]))); ?>

<?php if(! ($page->is_indexable ?? true)): ?>
    <?php $__env->startSection('robots', 'noindex,' . (($page->is_followable ?? true) ? 'follow' : 'nofollow')); ?>
<?php endif; ?>

<?php $__env->startSection('content'); ?>

<?php echo \App\Support\Hooks::render('layout.page.top', $slugUk); ?>

<div class="gazu-container py-8 <?php echo e($isNarrow ? 'max-w-3xl' : ''); ?>">
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

    <?php if($excerpt !== ''): ?>
        <p class="text-base text-[var(--gazu-graphite)] leading-relaxed mb-7 max-w-2xl"><?php echo e($excerpt); ?></p>
    <?php endif; ?>

    <?php if($content !== ''): ?>
        <article class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-xl p-8 text-[15px] leading-relaxed text-[var(--gazu-ink)] lb-html-content">
            <?php echo $content; ?>

        </article>
    <?php endif; ?>
</div>


<?php echo \App\Support\Hooks::render('layout.page.bottom', $slugUk); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('gazu.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/lionex/projects/gazu-shop/resources/views/gazu/page.blade.php ENDPATH**/ ?>