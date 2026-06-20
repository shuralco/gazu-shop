<?php $__env->startSection('title', 'Вхід · реєстрація — GAZU'); ?>

<?php $__env->startSection('content'); ?>
<div class="gazu-container py-12">
    <?php if(session('flash_message')): ?>
        <div class="max-w-4xl mx-auto bg-[var(--gazu-success-bg)] text-[var(--gazu-success)] px-4 py-3 rounded-md mb-4 text-sm">
            <?php echo e(session('flash_message')); ?>

        </div>
    <?php endif; ?>

    <div class="grid md:grid-cols-2 gap-7 max-w-4xl mx-auto">
        
        <div class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg p-7">
            <h2 class="gazu-display text-2xl font-semibold m-0 mb-1">Вхід</h2>
            <p class="text-sm text-[var(--gazu-graphite)] mb-5">Якщо у вас вже є акаунт</p>

            <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <div class="bg-[var(--gazu-danger-bg)] text-[var(--gazu-danger)] px-3 py-2 rounded mb-4 text-xs"><?php echo e($message); ?></div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

            <form action="<?php echo e(route('gazu.auth.login')); ?>" method="POST" class="flex flex-col gap-3">
                <?php echo csrf_field(); ?>
                <label class="block">
                    <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Email</span>
                    <input type="email" name="email" value="<?php echo e(old('email')); ?>" required
                           class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none focus:border-[var(--gazu-ink)]">
                </label>
                <label class="block">
                    <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Пароль</span>
                    <input type="password" name="password" required minlength="4"
                           class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none focus:border-[var(--gazu-ink)]">
                </label>
                <div class="flex justify-between items-center">
                    <label class="flex items-center gap-2 text-xs text-[var(--gazu-graphite)] cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4"> Запамʼятати мене
                    </label>
                    <a href="#" class="text-xs text-[var(--gazu-blue)]">Забули пароль?</a>
                </div>
                <button type="submit" class="gazu-btn-primary mt-2">Увійти</button>
            </form>
        </div>

        
        <div class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg p-7">
            <h2 class="gazu-display text-2xl font-semibold m-0 mb-1">Реєстрація</h2>
            <p class="text-sm text-[var(--gazu-graphite)] mb-5">Створіть акаунт за 30 секунд</p>

            <?php if($errors->hasAny(['name', 'email_register', 'phone_register', 'password'])): ?>
                <div class="bg-[var(--gazu-danger-bg)] text-[var(--gazu-danger)] px-3 py-2 rounded mb-4 text-xs">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $err): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><div><?php echo e($err); ?></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>

            <form action="<?php echo e(route('gazu.auth.register')); ?>" method="POST" class="flex flex-col gap-3">
                <?php echo csrf_field(); ?>
                <label class="block">
                    <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Імʼя</span>
                    <input type="text" name="name" value="<?php echo e(old('name')); ?>" required
                           class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none focus:border-[var(--gazu-ink)]">
                </label>
                <label class="block">
                    <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Email</span>
                    <input type="email" name="email" value="<?php echo e(old('email')); ?>" required
                           class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none focus:border-[var(--gazu-ink)]">
                </label>
                <label class="block">
                    <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Телефон (необовʼязково)</span>
                    <input type="tel" name="phone" value="<?php echo e(old('phone')); ?>" placeholder="+380 67 123 45 67"
                           class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none focus:border-[var(--gazu-ink)] gazu-mono">
                </label>
                <div class="grid grid-cols-2 gap-2">
                    <label class="block">
                        <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Пароль</span>
                        <input type="password" name="password" required minlength="6"
                               class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none focus:border-[var(--gazu-ink)]">
                    </label>
                    <label class="block">
                        <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Повторіть</span>
                        <input type="password" name="password_confirmation" required minlength="6"
                               class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none focus:border-[var(--gazu-ink)]">
                    </label>
                </div>

                <?php
                    $bonuses = $gazuSettings['gazu_auth_bonuses'] ?? [
                        'Бонусна програма — кешбек 3% на замовлення',
                        'Збережені адреси та швидке оформлення',
                        'Історія замовлень + сервіс-нагадування',
                    ];
                ?>
                <ul class="text-xs text-[var(--gazu-graphite)] flex flex-col gap-1 mt-1">
                    <?php $__currentLoopData = (array) $bonuses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bonus): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="flex gap-2"><span class="text-[var(--gazu-success)]"><?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'check','size' => '12']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'check','size' => '12']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $attributes = $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $component = $__componentOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?></span> <?php echo e($bonus); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>

                <button type="submit" class="gazu-btn-blue mt-2">Створити акаунт →</button>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('gazu.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/lionex/projects/gazu-shop/resources/views/gazu/account/auth.blade.php ENDPATH**/ ?>