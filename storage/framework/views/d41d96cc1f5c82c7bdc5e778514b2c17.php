<?php $__env->startSection('title', 'Блог — GAZU'); ?>

<?php
    $imgKinds = ['oil','wiper','pad','bearing','spark','filter','bulb','shock'];
?>

<?php $__env->startSection('content'); ?>
<div class="gazu-container">
    <?php if (isset($component)) { $__componentOriginaldd75f73904e8d7e4a617b590234b9aa0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldd75f73904e8d7e4a617b590234b9aa0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.breadcrumbs','data' => ['items' => [['Головна', route('gazu.home')], 'Блог']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.breadcrumbs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['Головна', route('gazu.home')], 'Блог'])]); ?>
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
    <h1 class="gazu-display text-4xl font-semibold m-0 mb-2"><?php echo e($activeCategory ? (is_array($activeCategory->name) ? ($activeCategory->name['uk'] ?? 'Блог') : $activeCategory->name) : 'Блог'); ?></h1>
    <p class="text-sm text-[var(--gazu-graphite)] mb-5">Гайди по обслуговуванню, новини та поради від наших майстрів</p>

    
    <?php if(! empty($categories) && count($categories) > 0): ?>
        <div class="flex flex-wrap gap-2 mb-7">
            <a wire:navigate href="<?php echo e(route('gazu.blog')); ?>"
               class="px-3.5 py-1.5 rounded-full text-[13px] no-underline transition-colors <?php echo e(! $activeCategory ? 'bg-[var(--gazu-ink)] text-white' : 'bg-white border border-[var(--gazu-line)] text-[var(--gazu-graphite)] hover:border-[var(--gazu-line-2)]'); ?>">
                Усі статті
            </a>
            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $cName = is_array($c->name) ? ($c->name['uk'] ?? '') : $c->name; ?>
                <a wire:navigate href="<?php echo e(route('gazu.blog.category', ['categorySlug' => $c->slug])); ?>"
                   class="px-3.5 py-1.5 rounded-full text-[13px] no-underline transition-colors <?php echo e($activeCategory && $activeCategory->id === $c->id ? 'bg-[var(--gazu-ink)] text-white' : 'bg-white border border-[var(--gazu-line)] text-[var(--gazu-graphite)] hover:border-[var(--gazu-line-2)]'); ?>">
                    <?php echo e($cName); ?><span class="opacity-60 ml-1"><?php echo e($c->posts_count); ?></span>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>

    <?php if(isset($posts) && $posts->isNotEmpty()): ?>
        <div class="grid md:grid-cols-3 gap-5">
            <?php $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $kind = $imgKinds[$i % count($imgKinds)];
                    $title = is_array($post->title) ? ($post->title['uk'] ?? '—') : ($post->title ?? '—');
                    $excerpt = is_array($post->excerpt ?? null) ? ($post->excerpt['uk'] ?? '') : ($post->excerpt ?? '');
                    $slug = $post->getLocalizedSlug('uk') ?: $post->id;
                    $catName = $post->blogCategory
                        ? (is_array($post->blogCategory->name) ? ($post->blogCategory->name['uk'] ?? 'Стаття') : $post->blogCategory->name)
                        : ($post->menu_group ?? 'Стаття');
                    $date = ($post->published_date)?->format('d.m.Y') ?? '';
                    $readMin = $post->reading_minutes;
                ?>
                <a wire:navigate href="<?php echo e(route('gazu.blog.show', ['slug' => $slug])); ?>"
                   class="bg-white border border-[var(--gazu-line)] rounded-lg overflow-hidden no-underline text-[var(--gazu-ink)] flex flex-col hover:border-[var(--gazu-line-2)]">
                    <?php
                        $img = $post->og_image;
                        // Handle three forms: full URL, absolute path "/img/...", relative "img/..." stored in /storage
                        $imgSrc = $img
                            ? (\Str::startsWith($img, ['http://','https://'])
                                ? $img
                                : ($img[0] === '/' ? asset(ltrim($img, '/')) : asset('storage/'.$img)))
                            : null;
                    ?>
                    <div class="aspect-video bg-[var(--gazu-paper)] flex items-center justify-center">
                        <?php if($imgSrc): ?>
                            <img src="<?php echo e($imgSrc); ?>" alt="" class="w-full h-full object-cover">
                        <?php else: ?>
                            <?php if (isset($component)) { $__componentOriginale68023f03052ea26bcc9e709ab0711bb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale68023f03052ea26bcc9e709ab0711bb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.part-image','data' => ['kind' => ''.e($kind).'','size' => '120']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.part-image'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['kind' => ''.e($kind).'','size' => '120']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale68023f03052ea26bcc9e709ab0711bb)): ?>
<?php $attributes = $__attributesOriginale68023f03052ea26bcc9e709ab0711bb; ?>
<?php unset($__attributesOriginale68023f03052ea26bcc9e709ab0711bb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale68023f03052ea26bcc9e709ab0711bb)): ?>
<?php $component = $__componentOriginale68023f03052ea26bcc9e709ab0711bb; ?>
<?php unset($__componentOriginale68023f03052ea26bcc9e709ab0711bb); ?>
<?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <div class="p-5 flex flex-col gap-2 flex-1">
                        <div class="flex items-center gap-2 text-xs">
                            <span class="gazu-mono px-2 py-0.5 bg-[var(--gazu-mist)] text-[var(--gazu-blue)] rounded tracking-wider uppercase"><?php echo e($catName); ?></span>
                        </div>
                        <h3 class="gazu-display text-lg font-semibold m-0 leading-tight"><?php echo e($title); ?></h3>
                        <?php if($excerpt): ?>
                            <p class="text-xs text-[var(--gazu-graphite)] m-0 line-clamp-3"><?php echo e($excerpt); ?></p>
                        <?php endif; ?>
                        <span class="flex-1"></span>
                        <div class="flex items-center gap-2 text-xs text-[var(--gazu-graphite)]">
                            <?php if($post->author): ?><span><?php echo e($post->author); ?></span><span class="opacity-40">·</span><?php endif; ?>
                            <span><?php echo e($date); ?></span>
                            <span class="opacity-40">·</span>
                            <span><?php echo e($readMin); ?> хв</span>
                        </div>
                    </div>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <?php if(method_exists($posts, 'lastPage') && $posts->lastPage() > 1): ?>
            <div class="mt-6"><?php echo e($posts->links("vendor.pagination.gazu")); ?></div>
        <?php endif; ?>
    <?php else: ?>
        <div class="bg-white border border-[var(--gazu-line)] rounded-lg p-12 text-center">
            <div class="inline-flex w-14 h-14 bg-[var(--gazu-mist)] rounded-full items-center justify-center mb-4">
                <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'search','size' => '24','stroke' => 'var(--gazu-graphite)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'search','size' => '24','stroke' => 'var(--gazu-graphite)']); ?>
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
            </div>
            <h2 class="gazu-display text-xl font-semibold m-0 mb-2">Скоро тут будуть статті</h2>
            <p class="text-sm text-[var(--gazu-graphite)] max-w-md mx-auto mb-5">Готуємо гайди з підбору запчастин, сезонного обслуговування та інших корисних тем.</p>
            <a wire:navigate href="<?php echo e(route('gazu.catalog')); ?>" class="gazu-btn-outline no-underline">Поки що — каталог</a>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('gazu.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/lionex/projects/gazu-shop/resources/views/gazu/blog.blade.php ENDPATH**/ ?>