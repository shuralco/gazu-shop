<?php $__env->startSection('title', 'Кошик · mobile'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-[420px] mx-auto py-4 px-4 pb-32">
    <h1 class="gazu-display text-xl font-semibold mb-3">Кошик · <?php echo e(count($cart ?? [])); ?></h1>

    <?php if(empty($cart)): ?>
        <div class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg p-6 text-center">
            <div class="inline-flex w-14 h-14 bg-[var(--gazu-mist)] rounded-full items-center justify-center mb-3 text-[var(--gazu-blue)]">
                <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'cart','size' => '24']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'cart','size' => '24']); ?>
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
            <div class="text-sm font-medium mb-2"><?php echo e($gazuSettings['gazu_cart_empty_title'] ?? 'Кошик порожній'); ?></div>
            <p class="text-xs text-[var(--gazu-graphite)] mb-3"><?php echo e($gazuSettings['gazu_cart_empty_desc'] ?? 'Додайте товари з каталогу'); ?></p>
            <a wire:navigate href="<?php echo e(route('gazu.catalog')); ?>" class="gazu-btn-primary text-xs no-underline">До каталогу</a>
        </div>
    <?php else: ?>
        <div class="flex flex-col gap-2">
            <?php $__currentLoopData = $cart; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $productId = is_numeric($key) ? (int) $key : (int) explode('_', (string) $key)[0];
                    $title = is_array($item['title'] ?? null) ? ($item['title']['uk'] ?? '—') : ($item['title'] ?? '—');
                    $price = (float) ($item['price'] ?? 0);
                    $qty = (int) ($item['quantity'] ?? 1);
                    $img = $item['image'] ?? null;
                    $hasReal = $img && ! \Illuminate\Support\Str::contains((string) $img, 'default-product');
                    $imgUrl = $hasReal ? (\Illuminate\Support\Str::startsWith($img, 'http') ? $img : asset('storage/'.ltrim((string) $img, '/storage/'))) : null;
                ?>
                <div class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg p-3 flex gap-3 items-center">
                    <div class="w-16 h-16 bg-[var(--gazu-paper)] rounded flex items-center justify-center shrink-0 overflow-hidden">
                        <?php if($imgUrl): ?>
                            <img src="<?php echo e($imgUrl); ?>" alt="" class="w-16 h-16 object-contain">
                        <?php else: ?>
                            <?php if (isset($component)) { $__componentOriginalb3ce7faecba1472bd9053bf57696fe20 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb3ce7faecba1472bd9053bf57696fe20 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.product-placeholder','data' => ['name' => $title,'seed' => $productId,'class' => 'w-16 h-16']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.product-placeholder'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($title),'seed' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($productId),'class' => 'w-16 h-16']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb3ce7faecba1472bd9053bf57696fe20)): ?>
<?php $attributes = $__attributesOriginalb3ce7faecba1472bd9053bf57696fe20; ?>
<?php unset($__attributesOriginalb3ce7faecba1472bd9053bf57696fe20); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb3ce7faecba1472bd9053bf57696fe20)): ?>
<?php $component = $__componentOriginalb3ce7faecba1472bd9053bf57696fe20; ?>
<?php unset($__componentOriginalb3ce7faecba1472bd9053bf57696fe20); ?>
<?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium text-[var(--gazu-ink)] truncate"><?php echo e($title); ?></div>
                        <div class="flex items-center gap-2 mt-1.5">
                            <form action="<?php echo e(route('gazu.cart.update')); ?>" method="POST" class="flex items-center border border-[var(--gazu-line)] rounded">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="product_id" value="<?php echo e($productId); ?>">
                                <button type="submit" name="quantity" value="<?php echo e(max(1, $qty - 1)); ?>" class="w-7 h-7 flex items-center justify-center"><?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'minus','size' => '12']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'minus','size' => '12']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $attributes = $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $component = $__componentOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?></button>
                                <span class="px-2 text-sm gazu-mono font-medium"><?php echo e($qty); ?></span>
                                <button type="submit" name="quantity" value="<?php echo e($qty + 1); ?>" class="w-7 h-7 flex items-center justify-center"><?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'plus','size' => '12']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'plus','size' => '12']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $attributes = $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $component = $__componentOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?></button>
                            </form>
                            <span class="flex-1"></span>
                            <span class="gazu-display font-bold text-sm"><?php echo e(number_format($price * $qty, 0, '.', ' ')); ?> ₴</span>
                            <form action="<?php echo e(route('gazu.cart.remove')); ?>" method="POST">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="product_id" value="<?php echo e($productId); ?>">
                                <button type="submit" class="w-7 h-7 flex items-center justify-center text-[var(--gazu-graphite)]"><?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'trash','size' => '14']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'trash','size' => '14']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $attributes = $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $component = $__componentOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?></button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <div class="mt-4 p-4 bg-[var(--gazu-paper)] rounded-lg">
            <div class="flex justify-between text-sm mb-1"><span class="text-[var(--gazu-graphite)]">Сума</span><span><?php echo e(number_format($cartTotal, 0, '.', ' ')); ?> ₴</span></div>
            <div class="flex justify-between text-base mt-2 pt-2 border-t border-[var(--gazu-line)]"><span class="font-medium">До сплати</span><span class="gazu-display text-xl font-bold"><?php echo e(number_format($cartTotal, 0, '.', ' ')); ?> ₴</span></div>
        </div>
    <?php endif; ?>
</div>

<?php if(! empty($cart)): ?>
<div class="fixed bottom-12 left-0 right-0 max-w-[420px] mx-auto bg-[var(--gazu-surface)] border-t border-[var(--gazu-line)] p-3 z-20">
    <a wire:navigate href="<?php echo e(route('gazu.checkout')); ?>" class="gazu-btn-primary w-full py-3 no-underline">Оформити замовлення</a>
</div>
<?php endif; ?>

<?php echo $__env->make('gazu.partials.mobile-nav', ['active' => 'cart'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('gazu.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/lionex/projects/gazu-shop/resources/views/gazu/mobile/cart.blade.php ENDPATH**/ ?>