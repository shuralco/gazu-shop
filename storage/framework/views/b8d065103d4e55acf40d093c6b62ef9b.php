<?php $__env->startSection('title', 'GAZU — для майстрів і водіїв'); ?>

<?php
    $s = $gazuSettings ?? [];
    $left = [
        'kicker' => $s['gazu_hero_v3_left_kicker'] ?? 'Для майстрів СТО',
        'title' => $s['gazu_hero_v3_left_title'] ?? "Швидкий пошук\nза артикулом",
        'desc'  => $s['gazu_hero_v3_left_description'] ?? sprintf(
            'Прямий доступ до %s. Аналоги і замінники в один клік.',
            $shopStats['products_label'] ?? 'каталогу'
        ),
        'perks' => $s['gazu_hero_v3_left_perks'] ?? ['Пакетний пошук', 'Гуртові ціни', 'Швидка доставка'],
    ];
    $right = [
        'kicker' => $s['gazu_hero_v3_right_kicker'] ?? 'Для водіїв',
        'title' => $s['gazu_hero_v3_right_title'] ?? "Підбір за вашим\nавто",
        'desc'  => $s['gazu_hero_v3_right_description'] ?? 'Марка, модель, рік — і ви побачите тільки сумісні запчастини.',
    ];
?>

<?php $__env->startSection('content'); ?>
    <section class="gazu-container py-10">
        <div class="grid lg:grid-cols-2 gap-4">
            <div class="bg-[var(--gazu-ink)] text-[var(--gazu-on-brand)] rounded-xl p-9 relative overflow-hidden min-h-[380px]">
                <div class="gazu-mono text-[11px] text-[var(--gazu-azure)] tracking-widest uppercase mb-3.5"><?php echo e($left['kicker']); ?></div>
                <h2 class="gazu-display text-4xl font-semibold leading-tight m-0"><?php echo nl2br(e($left['title'])); ?></h2>
                <p class="text-sm text-[#9DA5B2] leading-relaxed mt-3.5"><?php echo e($left['desc']); ?></p>
                <form action="<?php echo e(route('gazu.search')); ?>" method="GET" class="mt-7 flex gap-2">
                    <input name="q" placeholder="06A 115 561 B" class="flex-1 px-4 py-3.5 gazu-mono text-sm border-0 rounded-md outline-none bg-[var(--gazu-surface)] text-[var(--gazu-ink)]">
                    <button type="submit" class="px-5 bg-[var(--gazu-blue)] text-[var(--gazu-on-brand)] border-0 rounded-md font-medium text-sm cursor-pointer">Знайти</button>
                </form>
                <div class="mt-4 flex gap-4 text-xs text-[#9DA5B2] flex-wrap">
                    <?php $__currentLoopData = (array) $left['perks']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $perk): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <span>· <?php echo e($perk); ?></span>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <div class="absolute -right-7 -bottom-7 opacity-10">
                    <?php if (isset($component)) { $__componentOriginale68023f03052ea26bcc9e709ab0711bb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale68023f03052ea26bcc9e709ab0711bb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.part-image','data' => ['kind' => 'bearing','size' => '240']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.part-image'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['kind' => 'bearing','size' => '240']); ?>
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
                </div>
            </div>

            <div class="bg-[var(--gazu-mist)] rounded-xl p-9 relative overflow-hidden min-h-[380px]">
                <div class="gazu-mono text-[11px] text-[var(--gazu-blue)] tracking-widest uppercase mb-3.5"><?php echo e($right['kicker']); ?></div>
                <h2 class="gazu-display text-4xl font-semibold leading-tight m-0 text-[var(--gazu-ink)]"><?php echo nl2br(e($right['title'])); ?></h2>
                <p class="text-sm text-[var(--gazu-graphite)] leading-relaxed mt-3.5"><?php echo e($right['desc']); ?></p>
                <div class="mt-7 grid grid-cols-3 gap-2">
                    <?php $__currentLoopData = ['Марка', 'Модель', 'Рік']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="px-3 py-3.5 bg-[var(--gazu-surface)] rounded-md border border-[var(--gazu-line)]">
                            <div class="text-[11px] text-[var(--gazu-graphite)] mb-0.5"><?php echo e($p); ?></div>
                            <div class="text-[13px] text-[var(--gazu-muted)] flex items-center justify-between">Обрати <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'chevron','size' => '14']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'chevron','size' => '14']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $attributes = $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $component = $__componentOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?></div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <a wire:navigate href="<?php echo e(route('gazu.catalog')); ?>" class="mt-3 w-full py-3.5 bg-[var(--gazu-ink)] text-[var(--gazu-on-brand)] border-0 rounded-md font-medium text-sm cursor-pointer text-center no-underline block">
                    Підібрати запчастини
                </a>
                <div class="absolute -right-5 -bottom-7 opacity-15">
                    <?php if (isset($component)) { $__componentOriginale68023f03052ea26bcc9e709ab0711bb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale68023f03052ea26bcc9e709ab0711bb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.part-image','data' => ['kind' => 'pad','size' => '220']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.part-image'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['kind' => 'pad','size' => '220']); ?>
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
                </div>
            </div>
        </div>
    </section>

    <?php if (isset($component)) { $__componentOriginal06194beef9aa81f35c8be7c9b7b51aa1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal06194beef9aa81f35c8be7c9b7b51aa1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.trust-strip','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.trust-strip'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal06194beef9aa81f35c8be7c9b7b51aa1)): ?>
<?php $attributes = $__attributesOriginal06194beef9aa81f35c8be7c9b7b51aa1; ?>
<?php unset($__attributesOriginal06194beef9aa81f35c8be7c9b7b51aa1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal06194beef9aa81f35c8be7c9b7b51aa1)): ?>
<?php $component = $__componentOriginal06194beef9aa81f35c8be7c9b7b51aa1; ?>
<?php unset($__componentOriginal06194beef9aa81f35c8be7c9b7b51aa1); ?>
<?php endif; ?>
    <?php if (isset($component)) { $__componentOriginal475b96e12d3e966b8e9129a84d649a77 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal475b96e12d3e966b8e9129a84d649a77 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.category-tiles','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.category-tiles'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal475b96e12d3e966b8e9129a84d649a77)): ?>
<?php $attributes = $__attributesOriginal475b96e12d3e966b8e9129a84d649a77; ?>
<?php unset($__attributesOriginal475b96e12d3e966b8e9129a84d649a77); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal475b96e12d3e966b8e9129a84d649a77)): ?>
<?php $component = $__componentOriginal475b96e12d3e966b8e9129a84d649a77; ?>
<?php unset($__componentOriginal475b96e12d3e966b8e9129a84d649a77); ?>
<?php endif; ?>
    <?php if (isset($component)) { $__componentOriginal84e34a75febd89fe14c65c1c82086628 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal84e34a75febd89fe14c65c1c82086628 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.featured-row','data' => ['title' => $gazuSettings['gazu_section_specials'] ?? 'Новинки каталогу','items' => $featured]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.featured-row'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($gazuSettings['gazu_section_specials'] ?? 'Новинки каталогу'),'items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($featured)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal84e34a75febd89fe14c65c1c82086628)): ?>
<?php $attributes = $__attributesOriginal84e34a75febd89fe14c65c1c82086628; ?>
<?php unset($__attributesOriginal84e34a75febd89fe14c65c1c82086628); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal84e34a75febd89fe14c65c1c82086628)): ?>
<?php $component = $__componentOriginal84e34a75febd89fe14c65c1c82086628; ?>
<?php unset($__componentOriginal84e34a75febd89fe14c65c1c82086628); ?>
<?php endif; ?>
    <?php if (isset($component)) { $__componentOriginale1be49c9ed6481a1f18dd814509ce9e2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale1be49c9ed6481a1f18dd814509ce9e2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.brand-strip','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.brand-strip'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale1be49c9ed6481a1f18dd814509ce9e2)): ?>
<?php $attributes = $__attributesOriginale1be49c9ed6481a1f18dd814509ce9e2; ?>
<?php unset($__attributesOriginale1be49c9ed6481a1f18dd814509ce9e2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale1be49c9ed6481a1f18dd814509ce9e2)): ?>
<?php $component = $__componentOriginale1be49c9ed6481a1f18dd814509ce9e2; ?>
<?php unset($__componentOriginale1be49c9ed6481a1f18dd814509ce9e2); ?>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('gazu.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/lionex/projects/gazu-shop/resources/views/gazu/home/v3.blade.php ENDPATH**/ ?>