<?php $__env->startSection('title', 'Мої замовлення — GAZU'); ?>

<?php
    $statusMap = [
        'pending'    => ['label' => 'Очікує', 'color' => 'warn'],
        'paid'       => ['label' => 'Сплачено', 'color' => 'success'],
        'processing' => ['label' => 'У роботі', 'color' => 'warn'],
        'shipped'    => ['label' => 'Відправлено', 'color' => 'success'],
        'delivered'  => ['label' => 'Доставлено', 'color' => 'success'],
        'cancelled'  => ['label' => 'Скасовано', 'color' => 'danger'],
        'completed'  => ['label' => 'Завершено', 'color' => 'success'],
    ];
?>

<?php $__env->startSection('content'); ?>
<div class="gazu-container">
    <?php if (isset($component)) { $__componentOriginaldd75f73904e8d7e4a617b590234b9aa0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldd75f73904e8d7e4a617b590234b9aa0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.breadcrumbs','data' => ['items' => [['Головна', route('gazu.home')], 'Особистий кабінет']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.breadcrumbs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['Головна', route('gazu.home')], 'Особистий кабінет'])]); ?>
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
    <h1 class="gazu-display text-2xl sm:text-3xl md:text-4xl font-semibold text-[var(--gazu-ink)] m-0 mb-7">Особистий кабінет</h1>

    <?php if(session('flash_message')): ?>
        <div class="bg-[var(--gazu-success-bg)] text-[var(--gazu-success)] px-4 py-2 rounded-md mb-4 text-sm">
            <?php echo e(session('flash_message')); ?>

        </div>
    <?php endif; ?>

    <div class="gazu-grid-account">
        <?php echo $__env->make('gazu.partials.account-sidebar', ['active' => 'orders', 'user' => $user], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div>
            <div class="flex items-center justify-between mb-5 flex-wrap gap-2">
                <h2 class="gazu-display text-2xl font-semibold m-0">Замовлення</h2>
                <?php if($orders->count() > 0): ?>
                    <span class="text-sm text-[var(--gazu-graphite)]">Всього: <strong><?php echo e($orders->total()); ?></strong></span>
                <?php endif; ?>
            </div>

            <?php if($orders->isEmpty()): ?>
                <div class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg p-10 text-center">
                    <div class="inline-flex w-16 h-16 bg-[var(--gazu-mist)] rounded-full items-center justify-center mb-4 text-[var(--gazu-blue)]">
                        <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'box','size' => '28']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'box','size' => '28']); ?>
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
                    <div class="gazu-display text-xl font-semibold mb-2">Замовлень поки немає</div>
                    <p class="text-sm text-[var(--gazu-graphite)] mb-4">Як тільки оформите перше замовлення, воно зʼявиться тут.</p>
                    <a wire:navigate href="<?php echo e(route('gazu.catalog')); ?>" class="gazu-btn-primary no-underline">До каталогу</a>
                </div>
            <?php else: ?>
                <div class="flex flex-col gap-3">
                    <?php $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $st = $statusMap[$order->status] ?? ['label' => $order->status, 'color' => 'graphite'];
                            $count = $order->orderProducts->count();
                            $word = plural_uk($count, 'товар', 'товари', 'товарів');
                        ?>
                        <div class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg p-4 gazu-grid-order-row">
                            <div class="min-w-0">
                                <div class="gazu-display font-semibold text-sm text-[var(--gazu-ink)]">#<?php echo e($order->id); ?></div>
                                <div class="text-xs text-[var(--gazu-graphite)] mt-0.5"><?php echo e($count); ?> <?php echo e($word); ?></div>
                            </div>
                            <div class="text-sm text-[var(--gazu-graphite)] gazu-mono"><?php echo e($order->created_at?->format('d.m.Y')); ?></div>
                            <div class="gazu-display font-bold text-[var(--gazu-ink)]"><?php echo e(number_format((float) $order->total, 0, '.', ' ')); ?> ₴</div>
                            <div>
                                <span class="text-xs gazu-mono px-2 py-1 rounded inline-block whitespace-nowrap"
                                      style="background: var(--gazu-<?php echo e($st['color']); ?>-bg, var(--gazu-line)); color: var(--gazu-<?php echo e($st['color']); ?>, var(--gazu-graphite));">
                                    <?php echo e($st['label']); ?>

                                </span>
                            </div>
                            <a wire:navigate href="<?php echo e(route('gazu.account.order', ['order' => $order->id])); ?>"
                               class="gazu-btn-outline text-xs px-3 py-2 no-underline text-right">
                                Деталі →
                            </a>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <?php if($orders->lastPage() > 1): ?>
                    <div class="mt-6"><?php echo e($orders->links("vendor.pagination.gazu")); ?></div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('gazu.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/lionex/projects/gazu-shop/resources/views/gazu/account/orders.blade.php ENDPATH**/ ?>