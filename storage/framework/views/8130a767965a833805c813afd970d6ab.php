<?php $active = $active ?? 'home'; ?>
<nav class="gazu-mobile-nav max-w-[420px] mx-auto">
    <?php $__currentLoopData = [
        ['home', 'Головна', 'home', route('gazu.home')],
        ['catalog', 'Каталог', 'grid', route('gazu.catalog')],
        ['cart', 'Кошик', 'cart', route('gazu.cart')],
        ['account', 'Профіль', 'user', route('gazu.account')],
    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$k, $l, $ic, $url]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a wire:navigate href="<?php echo e($url); ?>" class="flex flex-col items-center justify-center py-2.5 text-[10px] no-underline <?php echo e($active === $k ? 'text-[var(--gazu-ink)]' : 'text-[var(--gazu-graphite)]'); ?>">
            <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => ''.e($ic).'','size' => '20']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => ''.e($ic).'','size' => '20']); ?>
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
            <span class="mt-0.5"><?php echo e($l); ?></span>
        </a>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</nav>
<?php /**PATH /home/lionex/projects/gazu-shop/resources/views/gazu/partials/mobile-nav.blade.php ENDPATH**/ ?>