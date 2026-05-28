<?php $__env->startSection('title', 'Порожній кошик — GAZU'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $s = $gazuSettings ?? [];
    $title = $s['gazu_cart_empty_title'] ?? 'Кошик порожній';
    $desc = $s['gazu_cart_empty_desc'] ?? 'Додайте товари з каталогу, щоб знайти точні запчастини для свого авто.';
?>
<div class="gazu-container py-20 text-center">
    <div class="inline-flex w-20 h-20 bg-[var(--gazu-mist)] rounded-full items-center justify-center mb-5 text-[var(--gazu-blue)]">
        <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'cart','size' => '36']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'cart','size' => '36']); ?>
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
    <h1 class="gazu-display text-3xl font-semibold text-[var(--gazu-ink)] m-0 mb-2"><?php echo e($title); ?></h1>
    <p class="text-sm text-[var(--gazu-graphite)] max-w-md mx-auto mb-7"><?php echo e($desc); ?></p>
    <div class="flex gap-2 justify-center">
        <a wire:navigate href="<?php echo e(route('gazu.catalog')); ?>" class="gazu-btn-primary no-underline">До каталогу</a>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('gazu.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/lionex/projects/gazu-shop/resources/views/gazu/cart/empty.blade.php ENDPATH**/ ?>