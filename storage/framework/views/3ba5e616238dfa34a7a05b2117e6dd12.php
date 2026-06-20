<?php
    $s = $gazuSettings ?? [];
    $cities = $s['gazu_topbar_cities'] ?? ($shopStats['cities_with_count'] ?? 'Україна');
    $hours = $s['gazu_topbar_hours'] ?? 'Пн-Нд 8:00–20:00';

    // Fallback map: коли admin зберіг label без URL — підставляємо з label-to-route.
    $labelToRoute = [
        'Гуртом'             => 'gazu.wholesale',
        'Гуртовим клієнтам'  => 'gazu.wholesale',
        'Доставка та оплата' => 'gazu.delivery',
        'Доставка'           => 'gazu.delivery',
        'Гарантія'           => 'gazu.warranty',
        'Гарантія та повернення' => 'gazu.warranty',
        'Контакти'           => 'gazu.contacts',
        'Про нас'            => 'gazu.about',
        'Блог'               => 'gazu.blog',
        'FAQ'                => 'gazu.faq',
    ];

    $rawLinks = $s['gazu_topbar_links'] ?? [
        ['label' => 'Гуртом', 'url' => route('gazu.wholesale')],
        ['label' => 'Доставка та оплата', 'url' => route('gazu.delivery')],
        ['label' => 'Гарантія', 'url' => route('gazu.warranty')],
        ['label' => 'Контакти', 'url' => route('gazu.contacts')],
    ];

    // Normalize: завжди мати валідний href. Pusty url + знайомий label → route.
    $links = collect((array) $rawLinks)
        ->map(function ($link) use ($labelToRoute) {
            $label = trim((string) ($link['label'] ?? ''));
            $url = trim((string) ($link['url'] ?? ''));
            if ($url === '' || $url === '#') {
                $routeName = $labelToRoute[$label] ?? null;
                if ($routeName && \Illuminate\Support\Facades\Route::has($routeName)) {
                    $url = route($routeName);
                }
            }
            return ['label' => $label, 'url' => $url ?: null];
        })
        ->filter(fn ($l) => $l['label'] !== '' && $l['url'] !== null)
        ->values()
        ->all();
?>

<div class="bg-[var(--gazu-ink)] text-[#CDD3DC] text-xs">
    <div class="gazu-container py-2 flex items-center gap-6">
        <?php if($cities): ?>
            <span class="inline-flex items-center gap-1.5"><?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'location','size' => '14']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'location','size' => '14']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $attributes = $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__attributesOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64)): ?>
<?php $component = $__componentOriginal6ccaa7247ed520b12783ad61ab722d64; ?>
<?php unset($__componentOriginal6ccaa7247ed520b12783ad61ab722d64); ?>
<?php endif; ?> <?php echo e($cities); ?></span>
        <?php endif; ?>
        <?php if($hours): ?>
            <span class="hidden md:inline"><?php echo e($hours); ?></span>
        <?php endif; ?>
        <span class="flex-1"></span>
        <?php $__currentLoopData = $links; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a wire:navigate href="<?php echo e($link['url']); ?>" class="hidden md:inline text-[#CDD3DC] no-underline hover:text-[var(--gazu-on-brand)]"><?php echo e($link['label']); ?></a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        
        <?php if ($__env->exists('multilang::switcher')) echo $__env->make('multilang::switcher', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>
</div>
<?php /**PATH /home/lionex/projects/gazu-shop/resources/views/gazu/partials/topbar.blade.php ENDPATH**/ ?>