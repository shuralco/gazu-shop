<?php $__env->startSection('title', 'СТО та послуги — GAZU'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $s = $gazuSettings ?? [];
    $heroTitle = $s['gazu_sto_intro_title'] ?? 'СТО та послуги';
    $heroDesc = $s['gazu_sto_intro_desc'] ?? 'Ми не лише продаємо запчастини — у нас власна мережа партнерських СТО з гарантією на роботи та фіксованими цінами.';
    $services = $s['gazu_sto_services'] ?? [];
    $partners = $s['gazu_sto_partners'] ?? [];
?>
<div class="gazu-container">
    <?php if (isset($component)) { $__componentOriginaldd75f73904e8d7e4a617b590234b9aa0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldd75f73904e8d7e4a617b590234b9aa0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.breadcrumbs','data' => ['items' => [['Головна', route('gazu.home')], 'СТО та послуги']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.breadcrumbs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['Головна', route('gazu.home')], 'СТО та послуги'])]); ?>
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
    <section class="bg-[var(--gazu-ink)] text-[var(--gazu-on-brand)] rounded-xl p-10 mb-7">
        <h1 class="gazu-display text-4xl font-semibold m-0 mb-2"><?php echo e($heroTitle); ?></h1>
        <p class="text-base text-[#9DA5B2] m-0 max-w-xl"><?php echo e($heroDesc); ?></p>
    </section>

    <?php if(! empty($services)): ?>
        <div class="grid md:grid-cols-3 gap-4 mb-7">
            <?php $__currentLoopData = $services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $svc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg p-5">
                    <div class="w-12 h-12 bg-[var(--gazu-mist)] rounded-md flex items-center justify-center text-[var(--gazu-blue)] mb-4">
                        <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => ''.e($svc['icon'] ?? 'wrench').'','size' => '24']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => ''.e($svc['icon'] ?? 'wrench').'','size' => '24']); ?>
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
                    <h3 class="gazu-display text-lg font-semibold m-0 mb-1"><?php echo e($svc['title'] ?? ''); ?></h3>
                    <div class="gazu-mono text-sm text-[var(--gazu-blue)] mb-2"><?php echo e($svc['price'] ?? ''); ?></div>
                    <p class="text-sm text-[var(--gazu-graphite)] m-0"><?php echo e($svc['desc'] ?? ''); ?></p>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>

    <?php if(! empty($partners)): ?>
        <section class="bg-[var(--gazu-mist)] rounded-xl p-7">
            <h2 class="gazu-display text-2xl font-semibold m-0 mb-4">Наші партнери СТО</h2>
            <div class="grid md:grid-cols-2 gap-3">
                <?php $__currentLoopData = $partners; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg p-4 flex items-start gap-3">
                        <div class="w-10 h-10 bg-[var(--gazu-paper)] rounded-md flex items-center justify-center text-[var(--gazu-blue)]">
                            <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'location','size' => '20']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'location','size' => '20']); ?>
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
                        <div class="flex-1">
                            <div class="font-medium text-[var(--gazu-ink)]"><?php echo e($p['name'] ?? ''); ?></div>
                            <div class="text-xs text-[var(--gazu-graphite)]"><?php echo e($p['addr'] ?? ''); ?></div>
                            <?php if(! empty($p['rating'])): ?>
                                <div class="text-xs text-[var(--gazu-warn)] mt-1">★ <?php echo e($p['rating']); ?></div>
                            <?php endif; ?>
                        </div>
                        <button type="button" class="gazu-btn-outline text-xs py-2 px-3">Записатись</button>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </section>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('gazu.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/lionex/projects/gazu-shop/resources/views/gazu/sto.blade.php ENDPATH**/ ?>