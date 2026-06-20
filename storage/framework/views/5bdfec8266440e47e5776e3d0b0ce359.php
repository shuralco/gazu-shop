<?php $__env->startSection('title', 'Замовлення оформлено — GAZU'); ?>

<?php
    $shippingData = is_array($order->shipping_data) ? $order->shipping_data : (json_decode($order->shipping_data ?? '[]', true) ?: []);
    $shippingMethod = \App\Support\OrderLabels::shipping($order->shipping_method);
    $shippingType = \App\Support\OrderLabels::shippingType($order->shipping_warehouse_type);
    $items = $order->orderProducts ?? collect();
?>

<?php $__env->startSection('content'); ?>
<div class="gazu-container pt-6">
    
    <nav aria-label="Прогрес замовлення" class="mb-7 max-w-3xl mx-auto">
        <ol class="flex items-center gap-2 sm:gap-4 text-sm overflow-x-auto">
            <li class="flex items-center gap-2 shrink-0">
                <span class="w-8 h-8 rounded-full bg-[var(--gazu-success)] text-[var(--gazu-on-brand)] flex items-center justify-center font-bold">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg>
                </span>
                <span class="text-[var(--gazu-graphite)]">Кошик</span>
            </li>
            <li class="flex-1 h-0.5 bg-[var(--gazu-success)] min-w-[24px]"></li>
            <li class="flex items-center gap-2 shrink-0">
                <span class="w-8 h-8 rounded-full bg-[var(--gazu-success)] text-[var(--gazu-on-brand)] flex items-center justify-center font-bold">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5L20 7"/></svg>
                </span>
                <span class="text-[var(--gazu-graphite)]">Оформлення</span>
            </li>
            <li class="flex-1 h-0.5 bg-[var(--gazu-success)] min-w-[24px]"></li>
            <li class="flex items-center gap-2 shrink-0">
                <span class="w-8 h-8 rounded-full bg-[var(--gazu-ink)] text-[var(--gazu-on-brand)] flex items-center justify-center font-bold">3</span>
                <span class="text-[var(--gazu-ink)] font-medium">Готово</span>
            </li>
        </ol>
    </nav>
</div>
<div class="gazu-container pb-12">
    <div class="max-w-3xl mx-auto bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-xl p-8">
        <div class="text-center mb-6">
            <div class="inline-flex w-20 h-20 bg-[var(--gazu-success-bg)] rounded-full items-center justify-center mb-5">
                <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'check','size' => '40','stroke' => 'var(--gazu-success)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'check','size' => '40','stroke' => 'var(--gazu-success)']); ?>
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
            <h1 class="gazu-display text-3xl font-semibold text-[var(--gazu-ink)] m-0 mb-2">
                Замовлення №<?php echo e($order->id); ?> оформлено
            </h1>
            <p class="text-sm text-[var(--gazu-graphite)] max-w-md mx-auto">
                Дякуємо! Менеджер передзвонить за <?php echo e($order->phone ?? 'вказаним номером'); ?> протягом 30 хвилин для уточнення доставки.
            </p>
        </div>

        <div class="grid md:grid-cols-2 gap-4 mb-6">
            
            <div class="bg-[var(--gazu-paper)] rounded-lg p-4">
                <div class="text-xs uppercase text-[var(--gazu-graphite)] tracking-wider mb-3 font-semibold">Контакт</div>
                <div class="space-y-1.5 text-sm">
                    <div><span class="text-[var(--gazu-graphite)]">Покупець:</span> <span class="font-medium"><?php echo e($order->name ?: $order->first_name); ?></span></div>
                    <div><span class="text-[var(--gazu-graphite)]">Телефон:</span> <span class="gazu-mono font-medium"><?php echo e($order->phone); ?></span></div>
                    <?php if($order->email): ?>
                        <div><span class="text-[var(--gazu-graphite)]">Email:</span> <span class="gazu-mono"><?php echo e($order->email); ?></span></div>
                    <?php endif; ?>
                </div>
            </div>

            
            <div class="bg-[var(--gazu-paper)] rounded-lg p-4">
                <div class="text-xs uppercase text-[var(--gazu-graphite)] tracking-wider mb-3 font-semibold">Доставка</div>
                <div class="space-y-1.5 text-sm">
                    <div class="font-medium text-[var(--gazu-ink)]">
                        <?php echo e($shippingMethod); ?>

                        <?php if($shippingType): ?> · <span class="text-[var(--gazu-blue)]"><?php echo e($shippingType); ?></span><?php endif; ?>
                    </div>
                    <?php if($order->shipping_city): ?>
                        <div><span class="text-[var(--gazu-graphite)]">Місто:</span> <?php echo e($order->shipping_city); ?></div>
                    <?php endif; ?>
                    <?php if($order->shipping_warehouse): ?>
                        <div class="text-[13px] text-[var(--gazu-graphite)]"><?php echo e($order->shipping_warehouse); ?></div>
                    <?php elseif($order->shipping_address): ?>
                        <div class="text-[13px] text-[var(--gazu-graphite)]"><?php echo e($order->shipping_address); ?></div>
                    <?php endif; ?>
                    <?php if(! empty($shippingData['preferred_date']) || ! empty($shippingData['preferred_time'])): ?>
                        <div class="text-[12px] text-[var(--gazu-graphite)] mt-2 pt-2 border-t border-[var(--gazu-line)]">
                            <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'clock','size' => '11']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'clock','size' => '11']); ?>
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
                            <?php if(! empty($shippingData['preferred_date'])): ?> <?php echo e(\Illuminate\Support\Carbon::parse($shippingData['preferred_date'])->format('d.m.Y')); ?><?php endif; ?>
                            <?php if(! empty($shippingData['preferred_time'])): ?>, <?php echo e($shippingData['preferred_time']); ?><?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        
        <?php if($items->count()): ?>
            <div class="mb-6">
                <div class="text-xs uppercase text-[var(--gazu-graphite)] tracking-wider mb-3 font-semibold">Товари (<?php echo e($items->sum('quantity')); ?>)</div>
                <div class="space-y-2">
                    <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center gap-3 py-2 border-b border-[var(--gazu-line)] last:border-b-0">
                            <div class="flex-1 min-w-0">
                                <div class="text-sm text-[var(--gazu-ink)] truncate"><?php echo e(is_array($item->title) ? ($item->title['uk'] ?? '') : $item->title); ?></div>
                                <div class="text-[11px] text-[var(--gazu-graphite)] gazu-mono"><?php echo e($item->quantity); ?> × <?php echo e(number_format((float) $item->price, 0, '.', ' ')); ?> ₴</div>
                            </div>
                            <div class="gazu-display font-bold text-sm whitespace-nowrap"><?php echo e(number_format((float) $item->price * (int) $item->quantity, 0, '.', ' ')); ?> ₴</div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endif; ?>

        
        <div class="bg-[var(--gazu-paper)] rounded-lg p-5 mb-6">
            <div class="flex justify-between mb-2 text-sm">
                <span class="text-[var(--gazu-graphite)]">Спосіб оплати</span>
                <span class="font-medium"><?php echo e(\App\Support\OrderLabels::payment($order->payment_method)); ?></span>
            </div>
            <?php if((float) $order->shipping_cost > 0): ?>
                <div class="flex justify-between mb-2 text-sm">
                    <span class="text-[var(--gazu-graphite)]">Доставка</span>
                    <span><?php echo e(number_format((float) $order->shipping_cost, 0, '.', ' ')); ?> ₴</span>
                </div>
            <?php endif; ?>
            <?php if((float) $order->discount_amount > 0): ?>
                <div class="flex justify-between mb-2 text-sm">
                    <span class="text-[var(--gazu-graphite)]">Знижка</span>
                    <span class="text-[var(--gazu-success)]">−<?php echo e(number_format((float) $order->discount_amount, 0, '.', ' ')); ?> ₴</span>
                </div>
            <?php endif; ?>
            <div class="h-px bg-[var(--gazu-line)] my-3"></div>
            <div class="flex justify-between items-baseline">
                <span class="font-medium text-[var(--gazu-ink)]">Усього</span>
                <span class="gazu-display text-3xl font-bold text-[var(--gazu-ink)]"><?php echo e(number_format((float) $order->total, 0, '.', ' ')); ?> ₴</span>
            </div>
        </div>

        <?php
            $needsPayment = in_array($order->payment_method, ['card', 'applepay'], true)
                && $order->payment_status !== 'paid';
        ?>

        <?php $paymentEnabled = \App\Models\DisplaySetting::get('gazu_payment_enabled', false); ?>
        <div class="flex gap-2 justify-center flex-wrap">
            <?php if($needsPayment && $paymentEnabled): ?>
                <?php if(auth()->guard()->check()): ?>
                    <a wire:navigate href="<?php echo e(route('gazu.order.payment', ['order' => $order->id])); ?>"
                       class="gazu-btn-primary no-underline">
                        💳 Перейти до оплати
                    </a>
                <?php else: ?>
                    <a wire:navigate href="<?php echo e(route('gazu.auth')); ?>" class="gazu-btn-primary no-underline">Увійти для оплати</a>
                <?php endif; ?>
                <a wire:navigate href="<?php echo e(route('gazu.catalog')); ?>" class="gazu-btn-outline no-underline">Продовжити покупки</a>
            <?php else: ?>
                <a wire:navigate href="<?php echo e(route('gazu.home')); ?>" class="gazu-btn-primary no-underline">На головну</a>
                <a wire:navigate href="<?php echo e(route('gazu.catalog')); ?>" class="gazu-btn-outline no-underline">Продовжити покупки</a>
            <?php endif; ?>

            <?php if(auth()->guard()->check()): ?>
                <a wire:navigate href="<?php echo e(route('gazu.account')); ?>" class="gazu-btn-outline no-underline">Мої замовлення</a>
            <?php endif; ?>
        </div>

        <?php if($needsPayment): ?>
            <div class="mt-4 text-xs text-[var(--gazu-graphite)] max-w-md mx-auto">
                <?php if($paymentEnabled): ?>
                    Замовлення оформлено, але не оплачено. Натисніть «Перейти до оплати», щоб завершити платіж.
                <?php else: ?>
                    Замовлення прийнято. Менеджер зв'яжеться з вами для підтвердження та оплати.
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('gazu.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/lionex/projects/gazu-shop/resources/views/gazu/checkout-success.blade.php ENDPATH**/ ?>