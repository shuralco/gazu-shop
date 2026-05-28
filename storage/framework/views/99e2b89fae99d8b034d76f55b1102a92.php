<?php
    $title = is_array($page->title) ? ($page->title['uk'] ?? 'Стаття') : ($page->title ?? 'Стаття');
    $content = is_array($page->content ?? null) ? ($page->content['uk'] ?? '') : ($page->content ?? '');
    $excerpt = is_array($page->excerpt ?? null) ? ($page->excerpt['uk'] ?? '') : ($page->excerpt ?? '');
    $cat = $page->blogCategory
        ? (is_array($page->blogCategory->name) ? ($page->blogCategory->name['uk'] ?? 'Стаття') : $page->blogCategory->name)
        : ($page->menu_group ?? 'Стаття');
    $catSlug = $page->blogCategory?->slug;
    $date = ($page->published_date)?->format('d.m.Y');
    $img = $page->og_image;
    $readingMin = $page->reading_minutes;
    $author = $page->author;
    $views = $page->views ?? 0;
?>

<?php $__env->startSection('title', ($page->meta_title ? (is_array($page->meta_title) ? ($page->meta_title['uk'] ?? $title) : $page->meta_title) : $title).' — GAZU блог'); ?>

<?php $__env->startSection('content'); ?>
<div class="gazu-container py-6 sm:py-10">
    <?php if (isset($component)) { $__componentOriginaldd75f73904e8d7e4a617b590234b9aa0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldd75f73904e8d7e4a617b590234b9aa0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.breadcrumbs','data' => ['items' => [
        ['Головна', route('gazu.home')],
        ['Блог', route('gazu.blog')],
        $title,
    ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.breadcrumbs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
        ['Головна', route('gazu.home')],
        ['Блог', route('gazu.blog')],
        $title,
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

    <article class="max-w-5xl mx-auto">
        <div class="flex items-center gap-3 mb-5 text-xs flex-wrap">
            <?php if($catSlug): ?>
                <a wire:navigate href="<?php echo e(route('gazu.blog.category', ['categorySlug' => $catSlug])); ?>" class="gazu-mono px-2 py-0.5 bg-[var(--gazu-mist)] text-[var(--gazu-blue)] rounded tracking-wider uppercase no-underline hover:bg-[var(--gazu-line)]"><?php echo e($cat); ?></a>
            <?php else: ?>
                <span class="gazu-mono px-2 py-0.5 bg-[var(--gazu-mist)] text-[var(--gazu-blue)] rounded tracking-wider uppercase"><?php echo e($cat); ?></span>
            <?php endif; ?>
            <?php if($author): ?><span class="text-[var(--gazu-graphite)]">· <?php echo e($author); ?></span><?php endif; ?>
            <?php if($date): ?><span class="text-[var(--gazu-graphite)]">· <?php echo e($date); ?></span><?php endif; ?>
            <?php if($readingMin): ?><span class="text-[var(--gazu-graphite)]">· <?php echo e($readingMin); ?> хв читання</span><?php endif; ?>
            <?php if($views > 0): ?><span class="text-[var(--gazu-graphite)]">· <?php echo e($views); ?> переглядів</span><?php endif; ?>
        </div>

        <h1 class="gazu-display text-[32px] sm:text-[40px] font-bold leading-[1.15] tracking-[-0.01em] m-0 mb-5 text-[var(--gazu-ink)]"><?php echo e($title); ?></h1>

        <?php if($excerpt): ?>
            <p class="text-[18px] text-[var(--gazu-graphite)] leading-relaxed mb-7 max-w-[60ch]"><?php echo e($excerpt); ?></p>
        <?php endif; ?>

        <?php
            $imgSrc = $img
                ? (\Str::startsWith($img, ['http://','https://'])
                    ? $img
                    : ($img[0] === '/' ? asset(ltrim($img, '/')) : asset('storage/'.$img)))
                : null;
        ?>
        <?php if($imgSrc): ?>
            <figure class="aspect-video rounded-xl overflow-hidden mb-8 bg-[var(--gazu-paper)]">
                <img src="<?php echo e($imgSrc); ?>" alt="<?php echo e($title); ?>" loading="eager" class="w-full h-full object-cover">
            </figure>
        <?php endif; ?>

        <div class="gazu-prose">
            <?php echo $content; ?>

        </div>

        <div class="mt-12 pt-6 border-t border-[var(--gazu-line)] flex items-center justify-between flex-wrap gap-3">
            <a wire:navigate href="<?php echo e(route('gazu.blog')); ?>" class="gazu-btn-outline no-underline">← Усі статті</a>
            <div class="flex gap-2">
                <a wire:navigate href="<?php echo e(route('gazu.catalog')); ?>" class="gazu-btn-primary no-underline">Перейти до каталогу</a>
            </div>
        </div>

        
        <?php if(! empty($related) && count($related) > 0): ?>
            <div class="mt-12 pt-8 border-t border-[var(--gazu-line)]">
                <h2 class="gazu-display text-2xl font-semibold m-0 mb-5">Читайте також</h2>
                <div class="grid sm:grid-cols-3 gap-5">
                    <?php $__currentLoopData = $related; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $rTitle = is_array($r->title) ? ($r->title['uk'] ?? '—') : ($r->title ?? '—');
                            $rSlug = $r->getLocalizedSlug('uk') ?: $r->id;
                            $rImg = $r->og_image;
                            $rImgSrc = $rImg
                                ? (\Str::startsWith($rImg, ['http://','https://']) ? $rImg : ($rImg[0] === '/' ? asset(ltrim($rImg, '/')) : asset('storage/'.$rImg)))
                                : null;
                        ?>
                        <a wire:navigate href="<?php echo e(route('gazu.blog.show', ['slug' => $rSlug])); ?>"
                           class="bg-white border border-[var(--gazu-line)] rounded-lg overflow-hidden no-underline text-[var(--gazu-ink)] flex flex-col hover:border-[var(--gazu-line-2)]">
                            <div class="aspect-video bg-[var(--gazu-paper)] flex items-center justify-center">
                                <?php if($rImgSrc): ?><img src="<?php echo e($rImgSrc); ?>" alt="" class="w-full h-full object-cover"><?php else: ?><?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'box','size' => '40','stroke' => 'var(--gazu-line-2)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'box','size' => '40','stroke' => 'var(--gazu-line-2)']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $attributes = $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $component = $__componentOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?><?php endif; ?>
                            </div>
                            <div class="p-4">
                                <h3 class="gazu-display text-[15px] font-semibold m-0 leading-snug line-clamp-2"><?php echo e($rTitle); ?></h3>
                                <div class="text-xs text-[var(--gazu-graphite)] mt-1.5"><?php echo e(($r->published_date)?->format('d.m.Y')); ?> · <?php echo e($r->reading_minutes); ?> хв</div>
                            </div>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endif; ?>
    </article>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('gazu.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/lionex/projects/gazu-shop/resources/views/gazu/blog-show.blade.php ENDPATH**/ ?>