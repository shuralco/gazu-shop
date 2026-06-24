<?php
    $active = $active ?? 'orders';
    $user = $user ?? auth()->user();
    $name = $user?->name ?: 'Гість';
    $phone = $user?->phone ?: $user?->email ?: '—';
?>
<aside class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg p-5">
    <div class="flex items-center gap-3 mb-4 pb-4 border-b border-[var(--gazu-line)]">
        <div class="w-12 h-12 bg-[var(--gazu-mist)] rounded-full flex items-center justify-center text-[var(--gazu-blue)] uppercase font-bold gazu-display">
            <?php echo e(mb_substr($name, 0, 1)); ?>

        </div>
        <div class="min-w-0">
            <div class="font-semibold text-[var(--gazu-ink)] truncate"><?php echo e($name); ?></div>
            <div class="text-xs text-[var(--gazu-graphite)] gazu-mono truncate"><?php echo e($phone); ?></div>
        </div>
    </div>
    <?php
        $navItems = [
            ['orders', 'Мої замовлення', 'box', route('gazu.account')],
        ];
        // Route::has guard: модуль може бути enabled у БД, але роут не
        // зареєстрований (route:cache зібрано при вимкненому модулі) → route()
        // кидав 500 на /kabinet. Перевіряємо наявність роуту перед посиланням.
        if (module('gazu_garage')->enabled() && \Illuminate\Support\Facades\Route::has('gazu.garage')) {
            $navItems[] = ['garage', 'Гараж · мої авто', 'car', route('gazu.garage')];
        }
        $navItems = array_merge($navItems, [
            ['favs', 'Обране', 'heart', route('gazu.wishlist')],
        ]);
        // Loyalty tab only when the module is on (otherwise it's a dead link).
        if (function_exists('module') && module('loyalty')->enabled()) {
            $navItems[] = ['loyalty', 'Бонусна програма', 'shield', route('gazu.account')];
        }
    ?>
    <nav class="flex flex-col gap-0.5">
        <?php $__currentLoopData = $navItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$k, $l, $ic, $url]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a wire:navigate href="<?php echo e($url); ?>"
               class="flex items-center gap-3 px-3 py-2.5 rounded text-sm no-underline <?php echo e($active === $k ? 'bg-[var(--gazu-paper)] text-[var(--gazu-ink)] font-medium' : 'text-[var(--gazu-graphite)]'); ?>"
               style="border-left: 3px solid <?php echo e($active === $k ? 'var(--gazu-blue)' : 'transparent'); ?>;">
                <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => ''.e($ic).'','size' => '18']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => ''.e($ic).'','size' => '18']); ?>
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
                <?php echo e($l); ?>

            </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        <?php if(auth()->guard()->check()): ?>
            <form action="<?php echo e(route('gazu.auth.logout')); ?>" method="POST" class="mt-2 border-t border-[var(--gazu-line)] pt-4">
                <?php echo csrf_field(); ?>
                <button type="submit" class="flex items-center gap-3 px-3 py-2.5 rounded text-sm w-full text-left bg-transparent border-0 cursor-pointer text-[var(--gazu-graphite)] hover:text-[var(--gazu-danger)]">
                    <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'arrow-l','size' => '18']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'arrow-l','size' => '18']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $attributes = $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $component = $__componentOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?> Вийти
                </button>
            </form>
        <?php endif; ?>
    </nav>
</aside>
<?php /**PATH /home/lionex/projects/gazu-shop/resources/views/gazu/partials/account-sidebar.blade.php ENDPATH**/ ?>