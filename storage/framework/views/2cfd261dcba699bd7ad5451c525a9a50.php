<?php
    $count = $count ?? 0;
    $view = $view ?? 'grid';
    $currentSort = $currentSort ?? 'popular';
    $sortLabels = [
        'popular' => 'Популярні',
        'price-asc' => 'Дешевше',
        'price-desc' => 'Дорожче',
        'new' => 'Нові',
    ];
?>
<div class="bg-white border border-[var(--gazu-line)] rounded-lg px-3.5 py-2.5 flex items-center gap-3 text-[13px] whitespace-nowrap font-text relative" x-data="{ openSort: false }">
    <span class="text-[var(--gazu-graphite)]"><span class="text-[var(--gazu-ink)] font-semibold"><?php echo e($count); ?></span> товарів</span>
    <span class="flex-1"></span>
    <span class="text-[var(--gazu-graphite)] hidden sm:inline">Сорт:</span>
    <div class="relative">
        <button type="button" @click="openSort = !openSort" @click.outside="openSort = false"
                class="px-2.5 py-1.5 bg-[var(--gazu-paper)] border border-[var(--gazu-line)] rounded text-[var(--gazu-ink)] inline-flex items-center gap-1.5 cursor-pointer">
            <?php echo e($sortLabels[$currentSort] ?? 'Популярні'); ?> <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'chevron','size' => '14']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'chevron','size' => '14']); ?>
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
        </button>
        <div x-show="openSort" x-cloak x-transition.opacity
             class="absolute top-full right-0 mt-1 bg-white border border-[var(--gazu-line)] rounded-lg shadow-lg z-30 min-w-[160px] overflow-hidden">
            <?php $__currentLoopData = $sortLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a wire:navigate href="<?php echo e(url()->current().'?'.http_build_query(array_merge(request()->except(['cat']),['sort' => $key, 'page' => null]))); ?>"
                   class="block px-3 py-2 text-[13px] no-underline <?php echo e($currentSort === $key ? 'bg-[var(--gazu-paper)] text-[var(--gazu-ink)] font-medium' : 'text-[var(--gazu-graphite)] hover:bg-[var(--gazu-paper)]'); ?>">
                    <?php echo e($label); ?>

                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
    <div class="flex border border-[var(--gazu-line)] rounded overflow-hidden">
        <a wire:navigate href="<?php echo e(url()->current().'?'.http_build_query(array_merge(request()->except(['cat']),['view' => 'grid']))); ?>"
           class="p-2 <?php echo e($view === 'grid' ? 'bg-[var(--gazu-ink)] text-white' : 'bg-white text-[var(--gazu-graphite)]'); ?> flex items-center cursor-pointer no-underline">
            <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'grid','size' => '14']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'grid','size' => '14']); ?>
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
        </a>
        <a wire:navigate href="<?php echo e(url()->current().'?'.http_build_query(array_merge(request()->except(['cat']),['view' => 'list']))); ?>"
           class="p-2 <?php echo e($view === 'list' ? 'bg-[var(--gazu-ink)] text-white' : 'bg-white text-[var(--gazu-graphite)]'); ?> flex items-center border-l border-[var(--gazu-line)] cursor-pointer no-underline">
            <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'list','size' => '14']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'list','size' => '14']); ?>
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
        </a>
    </div>
</div>
<?php /**PATH /home/lionex/projects/gazu-shop/resources/views/gazu/partials/sort-bar.blade.php ENDPATH**/ ?>