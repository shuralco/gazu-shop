<?php $__env->startSection('title', 'Контакти — GAZU'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $s = $gazuSettings ?? [];
    $phone = $s['gazu_phone'] ?? '0 800 75 10 24';
    $hours = $s['gazu_topbar_hours'] ?? 'Пн-Нд 8:00–20:00';
    $tg = $s['gazu_contacts_telegram'] ?? '@gazu_support';
    $viber = $s['gazu_contacts_viber'] ?? '+380 67 123 45 67';
    $email = $s['gazu_contacts_email'] ?? 'support@gazu.ua';
    $offices = $s['gazu_contacts_offices'] ?? [];
?>
<div class="gazu-container">
    <?php if (isset($component)) { $__componentOriginaldd75f73904e8d7e4a617b590234b9aa0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldd75f73904e8d7e4a617b590234b9aa0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.breadcrumbs','data' => ['items' => [['Головна', route('gazu.home')], 'Контакти']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.breadcrumbs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['Головна', route('gazu.home')], 'Контакти'])]); ?>
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
    <h1 class="gazu-display text-4xl font-semibold m-0 mb-7">Контакти</h1>

    <div class="gazu-grid-contacts">
        <div>
            <div class="bg-[var(--gazu-ink)] text-white rounded-lg p-6 mb-4">
                <div class="gazu-mono text-[11px] text-[var(--gazu-azure)] tracking-widest uppercase mb-2">Гаряча лінія</div>
                <div class="gazu-display text-3xl font-bold mb-1"><?php echo e($phone); ?></div>
                <div class="text-sm text-[#9DA5B2]">Безкоштовно по Україні · <?php echo e($hours); ?></div>
            </div>

            <div class="bg-white border border-[var(--gazu-line)] rounded-lg p-5 mb-4">
                <h3 class="gazu-display text-base font-semibold m-0 mb-3">Месенджери</h3>
                <?php $__currentLoopData = [
                    ['phone', 'Telegram', $tg],
                    ['phone', 'Viber', $viber],
                    ['mail', 'Email', $email],
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$ic, $name, $val]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if(! empty($val)): ?>
                        <div class="flex items-center gap-3 py-2.5 border-b border-[var(--gazu-line)] last:border-b-0">
                            <span class="text-[var(--gazu-blue)]"><?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
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
<?php endif; ?></span>
                            <div class="flex-1">
                                <div class="text-xs text-[var(--gazu-graphite)]"><?php echo e($name); ?></div>
                                <div class="text-sm text-[var(--gazu-ink)] font-medium gazu-mono"><?php echo e($val); ?></div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <?php if(! empty($offices)): ?>
                <div class="bg-white border border-[var(--gazu-line)] rounded-lg p-5">
                    <h3 class="gazu-display text-base font-semibold m-0 mb-3"><?php echo e(count($offices)); ?> відділень в Україні</h3>
                    <?php $__currentLoopData = $offices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $off): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-start gap-3 py-2 border-b border-[var(--gazu-line)] last:border-b-0">
                            <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'location','size' => '14','stroke' => 'var(--gazu-blue)','class' => 'mt-0.5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'location','size' => '14','stroke' => 'var(--gazu-blue)','class' => 'mt-0.5']); ?>
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
                            <div class="flex-1">
                                <div class="text-sm font-medium text-[var(--gazu-ink)]"><?php echo e($off['city'] ?? ''); ?></div>
                                <div class="text-xs text-[var(--gazu-graphite)]"><?php echo e($off['addr'] ?? ''); ?></div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php endif; ?>
        </div>

        <div>
            <?php
                // Карта з адмінки: gazu_contacts_map може бути або повним <iframe>
                // embed-кодом Google Maps, або просто URL для src.
                $mapRaw = trim((string) ($s['gazu_contacts_map'] ?? ''));
                $mapIsIframe = \Illuminate\Support\Str::contains($mapRaw, '<iframe');
                $mapIsUrl = $mapRaw !== '' && \Illuminate\Support\Str::startsWith($mapRaw, 'http');
            ?>
            <div class="bg-white border border-[var(--gazu-line)] rounded-lg overflow-hidden mb-5" style="height: 420px;">
                <?php if($mapIsIframe): ?>
                    <div class="w-full h-full gazu-map-embed"><?php echo $mapRaw; ?></div>
                <?php elseif($mapIsUrl): ?>
                    <iframe src="<?php echo e($mapRaw); ?>" class="w-full h-full" style="border:0;" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen title="Карта"></iframe>
                <?php else: ?>
                    <div class="w-full h-full bg-[var(--gazu-mist)] gazu-grid-pattern flex items-center justify-center">
                        <div class="text-center text-[var(--gazu-graphite)]">
                            <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'location','size' => '40','stroke' => 'var(--gazu-blue)']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'location','size' => '40','stroke' => 'var(--gazu-blue)']); ?>
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
                            <div class="text-sm mt-2">Карта <?php echo e(count($offices)); ?> відділень</div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="bg-white border border-[var(--gazu-line)] rounded-lg p-6">
                <h3 class="gazu-display text-xl font-semibold m-0 mb-4">Напишіть нам</h3>
                <form class="grid grid-cols-2 gap-3">
                    <label class="block">
                        <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Імʼя</span>
                        <input type="text" name="name" class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none">
                    </label>
                    <label class="block">
                        <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Email</span>
                        <input type="email" name="email" class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none">
                    </label>
                    <label class="block col-span-2">
                        <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Тема</span>
                        <input type="text" name="subject" class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none">
                    </label>
                    <label class="block col-span-2">
                        <span class="text-xs text-[var(--gazu-graphite)] mb-1 block">Повідомлення</span>
                        <textarea rows="4" name="message" class="w-full px-3 py-2.5 border border-[var(--gazu-line)] rounded-md outline-none"></textarea>
                    </label>
                    <button type="submit" class="gazu-btn-primary col-span-2">Надіслати</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('gazu.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/lionex/projects/gazu-shop/resources/views/gazu/contacts.blade.php ENDPATH**/ ?>