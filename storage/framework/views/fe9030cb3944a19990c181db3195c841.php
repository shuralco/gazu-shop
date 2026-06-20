<?php if (isset($component)) { $__componentOriginal166a02a7c5ef5a9331faf66fa665c256 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal166a02a7c5ef5a9331faf66fa665c256 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-panels::components.page.index','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament-panels::page'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php $stats = $this->getHitStats(); ?>

    
    <?php if(! isset($stats['error'])): ?>
        <?php
            $h = (int) $stats['keyspace_hits'];
            $m = (int) $stats['keyspace_misses'];
            $total = $h + $m;
            $rate = $total > 0 ? round(($h / $total) * 100, 1) : 0;
        ?>
        <div class="gap-3 mb-6" style="display:grid;gap:0.75rem;grid-template-columns:repeat(auto-fill,minmax(290px,1fr))">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-3 shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="text-[10px] uppercase tracking-wider text-gray-500">Hit rate</div>
                <div class="text-2xl font-bold mt-1 <?php echo e($rate >= 90 ? 'text-success-600' : ($rate >= 70 ? 'text-warning-500' : 'text-danger-500')); ?>">
                    <?php echo e($rate); ?>%
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg p-3 shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="text-[10px] uppercase tracking-wider text-gray-500">Hits</div>
                <div class="text-2xl font-bold mt-1 text-success-600"><?php echo e(number_format($h, 0, '.', ' ')); ?></div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg p-3 shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="text-[10px] uppercase tracking-wider text-gray-500">Misses</div>
                <div class="text-2xl font-bold mt-1 text-danger-500"><?php echo e(number_format($m, 0, '.', ' ')); ?></div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg p-3 shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="text-[10px] uppercase tracking-wider text-gray-500">Commands</div>
                <div class="text-2xl font-bold mt-1 text-gray-700 dark:text-gray-300"><?php echo e(number_format($stats['total_commands_processed'], 0, '.', ' ')); ?></div>
            </div>
        </div>
    <?php else: ?>
        <div class="text-danger-600 text-sm mb-6">Redis connection error: <?php echo e($stats['error']); ?></div>
    <?php endif; ?>

    
    <form wire:submit="save" class="space-y-6">
        <?php echo e($this->form); ?>


        <div class="flex gap-3">
            <?php if (isset($component)) { $__componentOriginal6330f08526bbb3ce2a0da37da512a11f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['type' => 'submit']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit']); ?>Зберегти <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6330f08526bbb3ce2a0da37da512a11f)): ?>
<?php $attributes = $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f; ?>
<?php unset($__attributesOriginal6330f08526bbb3ce2a0da37da512a11f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6330f08526bbb3ce2a0da37da512a11f)): ?>
<?php $component = $__componentOriginal6330f08526bbb3ce2a0da37da512a11f; ?>
<?php unset($__componentOriginal6330f08526bbb3ce2a0da37da512a11f); ?>
<?php endif; ?>
        </div>
    </form>

    
    <?php if (isset($component)) { $__componentOriginalee08b1367eba38734199cf7829b1d1e9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalee08b1367eba38734199cf7829b1d1e9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.section.index','data' => ['collapsible' => true,'collapsed' => true,'class' => 'mt-6']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['collapsible' => true,'collapsed' => true,'class' => 'mt-6']); ?>
         <?php $__env->slot('heading', null, []); ?> Як інтерпретувати hit rate <?php $__env->endSlot(); ?>
        <div class="text-sm text-gray-700 dark:text-gray-300 space-y-2">
            <p><strong>Hit rate ≥ 90%</strong> — кеш працює відмінно, більшість запитів повертаються з Redis.</p>
            <p><strong>70-89%</strong> — нормально для активного магазину з частими оновленнями товарів.</p>
            <p><strong>&lt; 70%</strong> — варто збільшити TTL для статичних доменів (categories, brands, info) або перевірити чи observers не flush'ять занадто часто.</p>
            <p class="text-gray-500 text-xs mt-2">Hits/misses — кумулятивні з моменту останнього restart Redis. Для «миттєвої» статистики дивитись keys count + memory.</p>
        </div>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalee08b1367eba38734199cf7829b1d1e9)): ?>
<?php $attributes = $__attributesOriginalee08b1367eba38734199cf7829b1d1e9; ?>
<?php unset($__attributesOriginalee08b1367eba38734199cf7829b1d1e9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalee08b1367eba38734199cf7829b1d1e9)): ?>
<?php $component = $__componentOriginalee08b1367eba38734199cf7829b1d1e9; ?>
<?php unset($__componentOriginalee08b1367eba38734199cf7829b1d1e9); ?>
<?php endif; ?>

        <?php
        $__scriptKey = '2684333512-4';
        ob_start();
    ?>
        <script>
            // Auto-refresh hit stats every 60s
            setInterval(() => { $wire.$refresh(); }, 60000);
        </script>
        <?php
        $__output = ob_get_clean();

        \Livewire\store($this)->push('scripts', $__output, $__scriptKey)
    ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal166a02a7c5ef5a9331faf66fa665c256)): ?>
<?php $attributes = $__attributesOriginal166a02a7c5ef5a9331faf66fa665c256; ?>
<?php unset($__attributesOriginal166a02a7c5ef5a9331faf66fa665c256); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal166a02a7c5ef5a9331faf66fa665c256)): ?>
<?php $component = $__componentOriginal166a02a7c5ef5a9331faf66fa665c256; ?>
<?php unset($__componentOriginal166a02a7c5ef5a9331faf66fa665c256); ?>
<?php endif; ?>
<?php /**PATH /home/lionex/projects/gazu-shop/resources/views/filament/pages/cache-settings.blade.php ENDPATH**/ ?>