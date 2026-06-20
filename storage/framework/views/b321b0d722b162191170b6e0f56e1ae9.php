<?php $__env->startSection('title', 'GAZU — підбір за маркою-моделлю-роком'); ?>

<?php $__env->startSection('content'); ?>
    <section class="py-15 relative overflow-hidden bg-[var(--gazu-ink)] text-[var(--gazu-on-brand)]" style="padding-top:60px;padding-bottom:60px">
        <div class="absolute inset-0 gazu-grid-pattern-dark"></div>
        <div class="gazu-container relative">
            <div class="gazu-grid-hero-picker">
                <?php
                    $s = $gazuSettings ?? [];
                    $kicker = $s['gazu_hero_v2_kicker'] ?? 'Підбір за вашим авто';
                    $title = $s['gazu_hero_v2_title'] ?? "Запчастини, які\nточно підійдуть.";
                    $desc = $s['gazu_hero_v2_description'] ?? 'Оберіть марку, модель та рік випуску — побачите тільки сумісні деталі. Без помилок і повернень.';
                    $brands = $s['gazu_hero_v2_brands'] ?? ['VW', 'Audi', 'BMW', 'Skoda', 'Toyota', 'Renault', 'Ford', 'Hyundai'];
                    $brandsTotal = $s['gazu_hero_v2_brands_total'] ?? 240;
                ?>
                <div>
                    <div class="gazu-mono text-[11px] text-[var(--gazu-azure)] tracking-widest uppercase mb-3.5"><?php echo e($kicker); ?></div>
                    <h1 class="gazu-display font-semibold m-0" style="font-size: 60px; line-height: 1.0; letter-spacing: -0.04em;">
                        <?php echo nl2br(e($title)); ?>

                    </h1>
                    <p class="text-base text-[#9DA5B2] leading-relaxed mt-5 max-w-md"><?php echo e($desc); ?></p>
                </div>
                <div class="bg-[var(--gazu-surface)] text-[var(--gazu-ink)] rounded-xl p-6">
                    <div class="flex items-center gap-1.5 mb-4.5 text-[11px] gazu-mono tracking-widest uppercase text-[var(--gazu-graphite)]">
                        <span class="text-[var(--gazu-blue)]">Крок 1 з 4</span>
                        <span class="flex-1 h-0.5 bg-[var(--gazu-line)] rounded relative">
                            <span class="absolute left-0 h-full bg-[var(--gazu-blue)] rounded" style="width: 25%"></span>
                        </span>
                    </div>

                    <div class="mb-5">
                        <label class="text-xs text-[var(--gazu-graphite)] mb-2 block">Оберіть марку</label>
                        <div class="grid grid-cols-4 gap-2">
                            <?php $__currentLoopData = (array) $brands; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <button type="button"
                                        class="py-3 px-2 gazu-display font-semibold text-[13px] border-[1.5px] rounded-md cursor-pointer <?php echo e($i === 0 ? 'bg-[var(--gazu-ink)] text-[var(--gazu-on-brand)] border-[var(--gazu-ink)]' : 'bg-[var(--gazu-surface)] text-[var(--gazu-ink)] border-[var(--gazu-line)]'); ?>"><?php echo e($b); ?></button>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <a wire:navigate href="<?php echo e(route('gazu.brand')); ?>" class="inline-block bg-transparent border-0 text-[var(--gazu-blue)] text-xs pt-2.5 cursor-pointer no-underline">Усі <?php echo e($brandsTotal); ?> марок →</a>
                    </div>

                    <div class="grid grid-cols-2 gap-2.5">
                        <div class="px-3.5 py-3 border border-[var(--gazu-line)] rounded-md bg-[var(--gazu-paper)]">
                            <div class="text-[11px] text-[var(--gazu-graphite)]">Модель</div>
                            <div class="text-sm text-[var(--gazu-muted)] mt-0.5">Спочатку марка</div>
                        </div>
                        <div class="px-3.5 py-3 border border-[var(--gazu-line)] rounded-md bg-[var(--gazu-paper)]">
                            <div class="text-[11px] text-[var(--gazu-graphite)]">Рік</div>
                            <div class="text-sm text-[var(--gazu-muted)] mt-0.5">—</div>
                        </div>
                    </div>

                    <div class="flex gap-3 mt-4 px-3.5 py-3 bg-[var(--gazu-mist)] rounded-lg text-xs text-[var(--gazu-graphite)]">
                        <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'shield','size' => '16','stroke' => 'var(--gazu-blue)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'shield','size' => '16','stroke' => 'var(--gazu-blue)']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.featured-row','data' => ['title' => 'Популярні товари','items' => $featured]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.featured-row'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Популярні товари','items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($featured)]); ?>
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
    <?php if (isset($component)) { $__componentOriginal84e34a75febd89fe14c65c1c82086628 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal84e34a75febd89fe14c65c1c82086628 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.featured-row','data' => ['title' => 'Хіти продажів','items' => $popular]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.featured-row'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Хіти продажів','items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($popular)]); ?>
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

<?php echo $__env->make('gazu.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/lionex/projects/gazu-shop/resources/views/gazu/home/v2.blade.php ENDPATH**/ ?>