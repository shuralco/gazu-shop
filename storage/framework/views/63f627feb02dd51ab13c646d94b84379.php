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
    <?php $stats = $this->getCacheStats(); ?>

    <div class="space-y-6">
        
        <div style="display:grid;gap:0.75rem;grid-template-columns:repeat(auto-fill,minmax(150px,1fr))">
            <?php
                // color → Filament badge palette (green→success, red→danger, gray→gray)
                $cards = [
                    ['label' => 'Cache driver',       'value' => $stats['cache_driver']],
                    ['label' => 'Response store',     'value' => $stats['response_cache_driver']],
                    ['label' => 'Octane (Swoole)',    'value' => $stats['octane_active'] ? 'Активний' : 'Не доступний', 'color' => $stats['octane_active'] ? 'success' : 'danger'],
                    ['label' => 'OPcache',            'value' => $stats['opcache_enabled'] ? 'УВІМК' : 'ВИМК',        'color' => $stats['opcache_enabled'] ? 'success' : 'danger'],
                    ['label' => 'Config cached',      'value' => $stats['config_cached'] ? 'YES' : 'NO',              'color' => $stats['config_cached'] ? 'success' : 'gray'],
                    ['label' => 'Routes cached',      'value' => $stats['routes_cached'] ? 'YES' : 'NO',              'color' => $stats['routes_cached'] ? 'success' : 'gray'],
                ];
            ?>
            <?php $__currentLoopData = $cards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="bg-white dark:bg-gray-800 rounded-lg p-3 shadow-sm border border-gray-100 dark:border-gray-700">
                    <div class="text-[10px] uppercase tracking-wider text-gray-500 dark:text-gray-400"><?php echo e($c['label']); ?></div>
                    <div class="mt-1">
                        <?php if(isset($c['color'])): ?>
                            <?php if (isset($component)) { $__componentOriginal986dce9114ddce94a270ab00ce6c273d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal986dce9114ddce94a270ab00ce6c273d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.badge','data' => ['color' => $c['color']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($c['color'])]); ?><?php echo e($c['value']); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal986dce9114ddce94a270ab00ce6c273d)): ?>
<?php $attributes = $__attributesOriginal986dce9114ddce94a270ab00ce6c273d; ?>
<?php unset($__attributesOriginal986dce9114ddce94a270ab00ce6c273d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal986dce9114ddce94a270ab00ce6c273d)): ?>
<?php $component = $__componentOriginal986dce9114ddce94a270ab00ce6c273d; ?>
<?php unset($__componentOriginal986dce9114ddce94a270ab00ce6c273d); ?>
<?php endif; ?>
                        <?php else: ?>
                            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100"><?php echo e($c['value']); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        
        <?php if (isset($component)) { $__componentOriginalee08b1367eba38734199cf7829b1d1e9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalee08b1367eba38734199cf7829b1d1e9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.section.index','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
             <?php $__env->slot('heading', null, []); ?> Redis (cache storage) <?php $__env->endSlot(); ?>
             <?php $__env->slot('description', null, []); ?> Усі response cache і application cache зберігаються тут. <?php $__env->endSlot(); ?>

            <?php if(isset($stats['redis']['error'])): ?>
                <div class="text-danger-600 text-sm">Помилка з'єднання: <?php echo e($stats['redis']['error']); ?></div>
            <?php else: ?>
                <div class="text-sm" style="display:grid;gap:1rem;grid-template-columns:repeat(auto-fit,minmax(160px,1fr))">
                    <div>
                        <div class="text-gray-500">Використано пам'яті</div>
                        <div class="font-semibold text-base"><?php echo e($stats['redis']['used_memory_human'] ?? '—'); ?></div>
                    </div>
                    <div>
                        <div class="text-gray-500">Всього keys</div>
                        <div class="font-semibold text-base"><?php echo e(number_format($stats['redis']['total_keys'] ?? 0, 0, '.', ' ')); ?></div>
                    </div>
                    <div>
                        <div class="text-gray-500">App cache (на диску)</div>
                        <div class="font-semibold text-base"><?php echo e($stats['cache_files']); ?> файлів / <?php echo e($stats['cache_size']); ?></div>
                    </div>
                    <div>
                        <div class="text-gray-500">Blade compiled</div>
                        <div class="font-semibold text-base"><?php echo e($stats['view_cache_files']); ?> файлів / <?php echo e($stats['view_cache_size']); ?></div>
                    </div>
                </div>
            <?php endif; ?>
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

        
        <?php if (isset($component)) { $__componentOriginalee08b1367eba38734199cf7829b1d1e9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalee08b1367eba38734199cf7829b1d1e9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.section.index','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
             <?php $__env->slot('heading', null, []); ?> Журнал останніх дій (30) <?php $__env->endSlot(); ?>

            <div class="bg-gray-50 dark:bg-gray-900 p-3 rounded-lg font-mono text-xs max-h-72 overflow-y-auto border border-gray-100 dark:border-gray-700">
                <?php if(session('cache_logs')): ?>
                    <?php $__currentLoopData = session('cache_logs', []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="mb-1.5 flex items-start gap-2">
                            <span class="text-gray-400 shrink-0">[<?php echo e($log['time']); ?>]</span>
                            <span class="text-info-600 dark:text-info-400 font-semibold shrink-0"><?php echo e($log['action']); ?></span>
                            <span class="text-gray-700 dark:text-gray-300">— <?php echo e($log['details']); ?></span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php else: ?>
                    <div class="text-gray-400 italic">Поки що жодних дій. Використайте кнопки вгорі сторінки.</div>
                <?php endif; ?>
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

        
        <?php if (isset($component)) { $__componentOriginalee08b1367eba38734199cf7829b1d1e9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalee08b1367eba38734199cf7829b1d1e9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.section.index','data' => ['collapsible' => true,'collapsed' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['collapsible' => true,'collapsed' => true]); ?>
             <?php $__env->slot('heading', null, []); ?> Як працює cache stack <?php $__env->endSlot(); ?>

            <div class="text-sm text-gray-700 dark:text-gray-300 space-y-3">
                <p><strong>1. Laravel Octane (Swoole)</strong> — application boot tримається в RAM між запитами. Cold-start ~150ms → warm ~5ms.</p>
                <p><strong>2. Spatie ResponseCache</strong> — full HTML response storing in Redis tag <code class="text-xs bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded">gazu-response</code>. TTL 7 днів. Cache HIT ~2-8ms.</p>
                <p><strong>3. Cache::tags(...)</strong> — domain-scoped cache (products, categories, brands, blog, cars, settings, warehouses). Granular flush через «По домену» кнопку.</p>
                <p><strong>4. Eloquent observers</strong> — авто-flush при save/update/delete на: Product, Category, Brand, InfoPage, Page, DisplaySetting, MerchantWarehouse, Inventory.</p>
                <p><strong>5. Octane reload</strong> — graceful restart workers без downtime (для застосування змін у коді без full deploy).</p>
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
    </div>

        <?php
        $__scriptKey = '2858694160-4';
        ob_start();
    ?>
        <script>
            setInterval(() => { $wire.$refresh(); }, 30000);
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
<?php /**PATH /home/lionex/projects/gazu-shop/resources/views/filament/pages/cache-management.blade.php ENDPATH**/ ?>