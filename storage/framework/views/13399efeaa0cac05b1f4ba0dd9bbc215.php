<?php $__env->startSection('title', 'Замовлення №'.$order->id.' — GAZU'); ?>

<?php
    $statusMap = [
        'pending' => ['label' => 'Очікує', 'color' => 'warn'],
        'paid' => ['label' => 'Сплачено', 'color' => 'success'],
        'processing' => ['label' => 'У роботі', 'color' => 'warn'],
        'shipped' => ['label' => 'Відправлено', 'color' => 'success'],
        'delivered' => ['label' => 'Доставлено', 'color' => 'success'],
        'cancelled' => ['label' => 'Скасовано', 'color' => 'danger'],
        'completed' => ['label' => 'Завершено', 'color' => 'success'],
    ];
    $st = $statusMap[$order->status] ?? ['label' => $order->status, 'color' => 'graphite'];
    $needsPayment = in_array($order->payment_method, ['card', 'applepay'], true) && $order->payment_status !== 'paid';
?>

<?php $__env->startSection('content'); ?>
<div class="gazu-container">
    <?php if (isset($component)) { $__componentOriginaldd75f73904e8d7e4a617b590234b9aa0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldd75f73904e8d7e4a617b590234b9aa0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.breadcrumbs','data' => ['items' => [
        ['Головна', route('gazu.home')],
        ['Кабінет', route('gazu.account')],
        'Замовлення №'.$order->id,
    ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.breadcrumbs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
        ['Головна', route('gazu.home')],
        ['Кабінет', route('gazu.account')],
        'Замовлення №'.$order->id,
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

    <div class="gazu-grid-account">
        <?php echo $__env->make('gazu.partials.account-sidebar', ['active' => 'orders', 'user' => $user], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div>
            <div class="flex items-baseline justify-between mb-5 flex-wrap gap-2">
                <h1 class="gazu-display text-3xl font-semibold m-0">Замовлення №<?php echo e($order->id); ?></h1>
                <span class="text-xs gazu-mono px-3 py-1.5 rounded inline-block whitespace-nowrap"
                      style="background: var(--gazu-<?php echo e($st['color']); ?>-bg, var(--gazu-line)); color: var(--gazu-<?php echo e($st['color']); ?>, var(--gazu-graphite));">
                    <?php echo e($st['label']); ?>

                </span>
            </div>

            
            <div class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg p-5 mb-4 grid md:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                <div>
                    <div class="text-xs text-[var(--gazu-graphite)] mb-0.5">Дата</div>
                    <div class="text-[var(--gazu-ink)]"><?php echo e($order->created_at?->format('d.m.Y H:i')); ?></div>
                </div>
                <div>
                    <div class="text-xs text-[var(--gazu-graphite)] mb-0.5">Сума</div>
                    <div class="gazu-display text-lg font-bold text-[var(--gazu-ink)]"><?php echo e(number_format((float) $order->total, 0, '.', ' ')); ?> ₴</div>
                </div>
                <div>
                    <div class="text-xs text-[var(--gazu-graphite)] mb-0.5">Покупець</div>
                    <div class="text-[var(--gazu-ink)]"><?php echo e(trim(($order->first_name ?? '').' '.($order->last_name ?? '')) ?: ($order->name ?? '—')); ?></div>
                </div>
                <div>
                    <div class="text-xs text-[var(--gazu-graphite)] mb-0.5">Телефон</div>
                    <div class="text-[var(--gazu-ink)] gazu-mono"><?php echo e($order->phone ?? '—'); ?></div>
                </div>
                <?php if($order->email): ?>
                    <div>
                        <div class="text-xs text-[var(--gazu-graphite)] mb-0.5">Email</div>
                        <div class="text-[var(--gazu-ink)]"><?php echo e($order->email); ?></div>
                    </div>
                <?php endif; ?>
                <div>
                    <div class="text-xs text-[var(--gazu-graphite)] mb-0.5">Доставка</div>
                    <div class="text-[var(--gazu-ink)]"><?php echo e(\App\Support\OrderLabels::shipping($order->shipping_method)); ?><?php if($t = \App\Support\OrderLabels::shippingType($order->shipping_warehouse_type)): ?> · <?php echo e($t); ?><?php endif; ?></div>
                    <?php if($order->shipping_city || $order->shipping_warehouse): ?>
                        <div class="text-xs text-[var(--gazu-graphite)]"><?php echo e(trim(($order->shipping_city ?? '').' · '.($order->shipping_warehouse ?? ''))); ?></div>
                    <?php endif; ?>
                </div>
                <div>
                    <div class="text-xs text-[var(--gazu-graphite)] mb-0.5">Оплата</div>
                    <div class="text-[var(--gazu-ink)]"><?php echo e(\App\Support\OrderLabels::payment($order->payment_method)); ?></div>
                    <div class="text-xs gazu-mono inline-block px-2 py-0.5 rounded mt-0.5"
                         style="background: var(--gazu-<?php echo e($order->payment_status === 'paid' ? 'success' : 'warn'); ?>-bg); color: var(--gazu-<?php echo e($order->payment_status === 'paid' ? 'success' : 'warn'); ?>)">
                        <?php echo e(\App\Support\OrderLabels::paymentStatus($order->payment_status)); ?>

                    </div>
                </div>
                <?php if($order->note): ?>
                    <div class="md:col-span-2">
                        <div class="text-xs text-[var(--gazu-graphite)] mb-0.5">Коментар</div>
                        <div class="text-[var(--gazu-ink)]"><?php echo e($order->note); ?></div>
                    </div>
                <?php endif; ?>
            </div>

            
            <h2 class="gazu-display text-xl font-semibold mb-3"><?php echo e(plural_uk_count($order->orderProducts->count(), 'Товар', 'Товари', 'Товарів')); ?></h2>
            <div class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg overflow-hidden mb-4">
                <?php
                    // Group order products by warehouse_id so users see "Зі складу X" sections.
                    $orderProducts = $order->orderProducts->load('warehouse');
                    $byWarehouse = $orderProducts->groupBy(fn ($op) => $op->warehouse_id ?? 0);
                    $isMulti = $byWarehouse->count() > 1;
                ?>
                <?php $__currentLoopData = $byWarehouse; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $whId => $items): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        // Warehouse already eager-loaded via $orderProducts->load('warehouse'),
                        // grab from the first item instead of refetching from DB.
                        $wh = $whId ? ($items->first()->warehouse ?? null) : null;
                    ?>
                    <?php if($wh && ($isMulti || $wh->delivery_eta)): ?>
                        <div class="bg-[var(--gazu-mist)] px-4 py-2 border-b border-[var(--gazu-line)] flex items-center gap-2 text-xs">
                            <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'location','size' => '14','stroke' => 'var(--gazu-blue)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'location','size' => '14','stroke' => 'var(--gazu-blue)']); ?>
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
                            <span class="font-medium text-[var(--gazu-ink)]"><?php echo e($wh->city ?: $wh->name); ?></span>
                            <?php if($wh->delivery_eta): ?>
                                <span class="text-[var(--gazu-muted)]">·</span>
                                <span class="text-[var(--gazu-graphite)]"><?php echo e($wh->delivery_eta); ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $op): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $title = is_array($op->title) ? ($op->title['uk'] ?? '—') : ($op->title ?? '—');
                            $kinds = ['filter','pad','shock','bulb','oil','spark','bearing','wiper'];
                            $kind = $kinds[($op->product_id ?? 0) % count($kinds)];
                            $line = (float) $op->price * (int) $op->quantity;
                        ?>
                        <div class="flex items-center gap-3 p-4 <?php echo e(($i || ! $loop->parent->first) ? 'border-t border-[var(--gazu-line)]' : ''); ?>">
                            <div class="w-14 h-14 bg-[var(--gazu-paper)] rounded flex items-center justify-center shrink-0">
                                <?php if (isset($component)) { $__componentOriginale68023f03052ea26bcc9e709ab0711bb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale68023f03052ea26bcc9e709ab0711bb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.part-image','data' => ['kind' => ''.e($kind).'','size' => '48']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.part-image'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['kind' => ''.e($kind).'','size' => '48']); ?>
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
                            <div class="flex-1 min-w-0">
                                <?php if($op->slug): ?>
                                    <a wire:navigate href="<?php echo e(route('gazu.product.show', ['slug' => $op->slug])); ?>" class="text-[var(--gazu-ink)] no-underline font-medium leading-snug hover:text-[var(--gazu-blue)]"><?php echo e($title); ?></a>
                                <?php else: ?>
                                    <span class="text-[var(--gazu-ink)] font-medium leading-snug"><?php echo e($title); ?></span>
                                <?php endif; ?>
                                <div class="text-xs text-[var(--gazu-graphite)] gazu-mono mt-0.5"><?php echo e($op->quantity); ?> × <?php echo e(number_format((float) $op->price, 0, '.', ' ')); ?> ₴</div>
                            </div>
                            <div class="gazu-display font-bold text-[var(--gazu-ink)] whitespace-nowrap"><?php echo e(number_format($line, 0, '.', ' ')); ?> ₴</div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <div class="bg-[var(--gazu-paper)] p-4 flex justify-between items-baseline border-t border-[var(--gazu-line)]">
                    <span class="font-medium text-[var(--gazu-ink)]">Усього</span>
                    <span class="gazu-display text-2xl font-bold text-[var(--gazu-ink)]"><?php echo e(number_format((float) $order->total, 0, '.', ' ')); ?> ₴</span>
                </div>
            </div>

            
            <div class="flex gap-2 flex-wrap">
                <a wire:navigate href="<?php echo e(route('gazu.account')); ?>" class="gazu-btn-outline no-underline">← Усі замовлення</a>
                
                <?php if($needsPayment && \App\Models\DisplaySetting::get('gazu_payment_enabled', false)): ?>
                    <a href="<?php echo e(route('gazu.order.payment', ['order' => $order->id])); ?>" class="gazu-btn-primary no-underline">💳 Перейти до оплати</a>
                <?php endif; ?>
                <a wire:navigate href="<?php echo e(route('gazu.catalog')); ?>" class="gazu-btn-outline no-underline">Замовити ще</a>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('gazu.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/lionex/projects/gazu-shop/resources/views/gazu/account/order-details.blade.php ENDPATH**/ ?>