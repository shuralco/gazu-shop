<?php
$info = $this->getModuleInfo();
$health = $this->getHealthChecks();
$activity = $this->getRecentActivity(10);
$healthCounts = ['ok'=>0,'warning'=>0,'error'=>0];
foreach($health as $h) { $healthCounts[$h['status']] = ($healthCounts[$h['status']] ?? 0) + 1; }
$overallHealth = $healthCounts['error'] > 0 ? 'error' : ($healthCounts['warning'] > 0 ? 'warning' : 'ok');
$actionLabels = [
  'enabled' => 'Увімкнено',
  'disabled' => 'Вимкнено',
  'settings_saved' => 'Налаштування збережено',
  'install' => 'Встановлено',
  'upgrade' => 'Оновлено',
  'uninstall' => 'Видалено',
];
$inputClass = 'fi-input block w-full rounded-lg border-none bg-white px-3 py-1.5 text-sm text-gray-950 shadow-sm ring-1 ring-inset ring-gray-950/10 transition focus:ring-2 focus:ring-inset focus:ring-primary-500 dark:bg-white/5 dark:text-white dark:ring-white/10';
?>

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
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 space-y-2" style="flex:1 1 0%">
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="text-xl font-bold tracking-tight text-gray-950 dark:text-white"><?php echo e($info['name']); ?></h2>
                    <?php if($info['enabled']): ?>
                        <?php if (isset($component)) { $__componentOriginal986dce9114ddce94a270ab00ce6c273d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal986dce9114ddce94a270ab00ce6c273d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.badge','data' => ['color' => 'success','icon' => 'heroicon-m-check-circle']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'success','icon' => 'heroicon-m-check-circle']); ?>Активний <?php echo $__env->renderComponent(); ?>
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
                        <?php if (isset($component)) { $__componentOriginal986dce9114ddce94a270ab00ce6c273d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal986dce9114ddce94a270ab00ce6c273d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.badge','data' => ['color' => 'gray','icon' => 'heroicon-m-pause-circle']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'gray','icon' => 'heroicon-m-pause-circle']); ?>Неактивний <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal986dce9114ddce94a270ab00ce6c273d)): ?>
<?php $attributes = $__attributesOriginal986dce9114ddce94a270ab00ce6c273d; ?>
<?php unset($__attributesOriginal986dce9114ddce94a270ab00ce6c273d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal986dce9114ddce94a270ab00ce6c273d)): ?>
<?php $component = $__componentOriginal986dce9114ddce94a270ab00ce6c273d; ?>
<?php unset($__componentOriginal986dce9114ddce94a270ab00ce6c273d); ?>
<?php endif; ?>
                    <?php endif; ?>
                </div>

                <div class="flex flex-wrap items-center gap-x-2 gap-y-1 font-mono text-xs text-gray-500 dark:text-gray-400">
                    <span><?php echo e($info['key']); ?></span>
                    <?php if($info['version']): ?><span class="text-gray-300 dark:text-gray-600">·</span><span>v<?php echo e($info['version']); ?></span><?php endif; ?>
                    <?php if($info['author']): ?><span class="text-gray-300 dark:text-gray-600">·</span><span class="font-sans"><?php echo e($info['author']); ?></span><?php endif; ?>
                </div>

                <?php if($info['description']): ?>
                    <p class="max-w-2xl text-sm leading-relaxed text-gray-600 dark:text-gray-300"><?php echo e($info['description']); ?></p>
                <?php endif; ?>
            </div>

            <div class="flex shrink-0 items-center gap-2">
                <?php if (isset($component)) { $__componentOriginalf0029cce6d19fd6d472097ff06a800a1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf0029cce6d19fd6d472097ff06a800a1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.icon-button','data' => ['icon' => 'heroicon-o-arrow-path','wire:click' => 'clearModuleCache','wire:loading.attr' => 'disabled','wire:target' => 'clearModuleCache','label' => 'Очистити кеш','color' => 'gray','size' => 'lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::icon-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'heroicon-o-arrow-path','wire:click' => 'clearModuleCache','wire:loading.attr' => 'disabled','wire:target' => 'clearModuleCache','label' => 'Очистити кеш','color' => 'gray','size' => 'lg']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf0029cce6d19fd6d472097ff06a800a1)): ?>
<?php $attributes = $__attributesOriginalf0029cce6d19fd6d472097ff06a800a1; ?>
<?php unset($__attributesOriginalf0029cce6d19fd6d472097ff06a800a1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf0029cce6d19fd6d472097ff06a800a1)): ?>
<?php $component = $__componentOriginalf0029cce6d19fd6d472097ff06a800a1; ?>
<?php unset($__componentOriginalf0029cce6d19fd6d472097ff06a800a1); ?>
<?php endif; ?>

                <?php if($info['enabled']): ?>
                    <?php if (isset($component)) { $__componentOriginal6330f08526bbb3ce2a0da37da512a11f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => 'toggleModule','wire:confirm' => 'Вимкнути модуль «'.e($info['name']).'»? Дані залишаються у БД.','wire:loading.attr' => 'disabled','wire:target' => 'toggleModule','color' => 'danger','icon' => 'heroicon-o-power']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'toggleModule','wire:confirm' => 'Вимкнути модуль «'.e($info['name']).'»? Дані залишаються у БД.','wire:loading.attr' => 'disabled','wire:target' => 'toggleModule','color' => 'danger','icon' => 'heroicon-o-power']); ?>
                        <span wire:loading.remove wire:target="toggleModule">Вимкнути</span>
                        <span wire:loading wire:target="toggleModule">Вимикаю…</span>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6330f08526bbb3ce2a0da37da512a11f)): ?>
<?php $attributes = $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f; ?>
<?php unset($__attributesOriginal6330f08526bbb3ce2a0da37da512a11f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6330f08526bbb3ce2a0da37da512a11f)): ?>
<?php $component = $__componentOriginal6330f08526bbb3ce2a0da37da512a11f; ?>
<?php unset($__componentOriginal6330f08526bbb3ce2a0da37da512a11f); ?>
<?php endif; ?>
                <?php else: ?>
                    <?php if (isset($component)) { $__componentOriginal6330f08526bbb3ce2a0da37da512a11f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => 'toggleModule','wire:loading.attr' => 'disabled','wire:target' => 'toggleModule','color' => 'primary','icon' => 'heroicon-o-bolt']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'toggleModule','wire:loading.attr' => 'disabled','wire:target' => 'toggleModule','color' => 'primary','icon' => 'heroicon-o-bolt']); ?>
                        <span wire:loading.remove wire:target="toggleModule">Увімкнути</span>
                        <span wire:loading wire:target="toggleModule">Вмикаю…</span>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6330f08526bbb3ce2a0da37da512a11f)): ?>
<?php $attributes = $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f; ?>
<?php unset($__attributesOriginal6330f08526bbb3ce2a0da37da512a11f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6330f08526bbb3ce2a0da37da512a11f)): ?>
<?php $component = $__componentOriginal6330f08526bbb3ce2a0da37da512a11f; ?>
<?php unset($__componentOriginal6330f08526bbb3ce2a0da37da512a11f); ?>
<?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        
        <div class="mt-5 overflow-hidden rounded-lg bg-gray-200 dark:bg-white/10" style="display:grid;gap:1px;grid-template-columns:repeat(auto-fit,minmax(110px,1fr))">
            <?php
                $stats = [
                    ['label'=>'Файлів','value'=>$info['file_count']],
                    ['label'=>'Migrations','value'=>$info['migrations_count']],
                    ['label'=>'Routes','value'=>$info['registered_routes']],
                    ['label'=>'Filament','value'=>count($info['filament_resources'])+count($info['filament_pages'])+count($info['filament_widgets'])],
                    ['label'=>'Hooks','value'=>count($info['hook_events'] ?? [])],
                ];
            ?>
            <?php $__currentLoopData = $stats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="bg-white px-4 py-3 dark:bg-gray-900">
                    <div class="text-[11px] font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400"><?php echo e($s['label']); ?></div>
                    <div class="mt-0.5 text-xl font-semibold tabular-nums text-gray-950 dark:text-white"><?php echo e($s['value']); ?></div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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

    
    <div style="display:grid;gap:1.5rem;grid-template-columns:repeat(auto-fit,minmax(340px,1fr))">

        
        <?php if (isset($component)) { $__componentOriginalee08b1367eba38734199cf7829b1d1e9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalee08b1367eba38734199cf7829b1d1e9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.section.index','data' => ['icon' => 'heroicon-o-link','iconColor' => 'gray']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'heroicon-o-link','icon-color' => 'gray']); ?>
             <?php $__env->slot('heading', null, []); ?> Залежності <?php $__env->endSlot(); ?>

            <div class="space-y-4 text-sm">
                <div>
                    <div class="mb-1.5 text-xs text-gray-500">Потребує</div>
                    <?php if(empty($info['requires'])): ?>
                        <span class="text-sm text-gray-400">— нічого —</span>
                    <?php else: ?>
                        <div class="flex flex-wrap gap-1.5">
                            <?php $__currentLoopData = $info['requires']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $req): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <a href="<?php echo e(url('/admin/modules/view?key='.$req)); ?>">
                                    <?php if (isset($component)) { $__componentOriginal986dce9114ddce94a270ab00ce6c273d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal986dce9114ddce94a270ab00ce6c273d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.badge','data' => ['color' => 'warning']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'warning']); ?><?php echo e($req); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal986dce9114ddce94a270ab00ce6c273d)): ?>
<?php $attributes = $__attributesOriginal986dce9114ddce94a270ab00ce6c273d; ?>
<?php unset($__attributesOriginal986dce9114ddce94a270ab00ce6c273d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal986dce9114ddce94a270ab00ce6c273d)): ?>
<?php $component = $__componentOriginal986dce9114ddce94a270ab00ce6c273d; ?>
<?php unset($__componentOriginal986dce9114ddce94a270ab00ce6c273d); ?>
<?php endif; ?>
                                </a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div>
                    <div class="mb-1.5 text-xs text-gray-500">Від нього залежать</div>
                    <?php if(empty($info['dependents'])): ?>
                        <span class="text-sm text-gray-400">— ніхто —</span>
                    <?php else: ?>
                        <div class="flex flex-wrap gap-1.5">
                            <?php $__currentLoopData = $info['dependents']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dep => $depEnabled): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <a href="<?php echo e(url('/admin/modules/view?key='.$dep)); ?>">
                                    <?php if (isset($component)) { $__componentOriginal986dce9114ddce94a270ab00ce6c273d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal986dce9114ddce94a270ab00ce6c273d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.badge','data' => ['color' => $depEnabled ? 'success' : 'gray']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($depEnabled ? 'success' : 'gray')]); ?><?php echo e($dep); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal986dce9114ddce94a270ab00ce6c273d)): ?>
<?php $attributes = $__attributesOriginal986dce9114ddce94a270ab00ce6c273d; ?>
<?php unset($__attributesOriginal986dce9114ddce94a270ab00ce6c273d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal986dce9114ddce94a270ab00ce6c273d)): ?>
<?php $component = $__componentOriginal986dce9114ddce94a270ab00ce6c273d; ?>
<?php unset($__componentOriginal986dce9114ddce94a270ab00ce6c273d); ?>
<?php endif; ?>
                                </a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="grid grid-cols-2 gap-3 border-t border-gray-100 pt-3 text-xs dark:border-white/5">
                    <div>
                        <div class="text-gray-500">За замовчуванням</div>
                        <div class="mt-0.5 font-medium text-gray-900 dark:text-gray-100"><?php echo e($info['enabled_by_default'] ? 'увімкнено' : 'вимкнено'); ?></div>
                    </div>
                    <?php if($info['enabled_at']): ?>
                        <div>
                            <div class="text-gray-500">Увімкнено</div>
                            <div class="mt-0.5 font-medium text-gray-900 dark:text-gray-100"><?php echo e(\Carbon\Carbon::parse($info['enabled_at'])->diffForHumans()); ?></div>
                        </div>
                    <?php endif; ?>
                </div>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.section.index','data' => ['icon' => 'heroicon-o-cog-6-tooth','iconColor' => 'gray']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'heroicon-o-cog-6-tooth','icon-color' => 'gray']); ?>
             <?php $__env->slot('heading', null, []); ?> Налаштування <?php $__env->endSlot(); ?>
            <?php if($info['has_settings']): ?>
                 <?php $__env->slot('headerEnd', null, []); ?> 
                    <?php if (isset($component)) { $__componentOriginal986dce9114ddce94a270ab00ce6c273d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal986dce9114ddce94a270ab00ce6c273d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.badge','data' => ['color' => 'gray']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'gray']); ?><?php echo e(count($info['settings_schema'])); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal986dce9114ddce94a270ab00ce6c273d)): ?>
<?php $attributes = $__attributesOriginal986dce9114ddce94a270ab00ce6c273d; ?>
<?php unset($__attributesOriginal986dce9114ddce94a270ab00ce6c273d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal986dce9114ddce94a270ab00ce6c273d)): ?>
<?php $component = $__componentOriginal986dce9114ddce94a270ab00ce6c273d; ?>
<?php unset($__componentOriginal986dce9114ddce94a270ab00ce6c273d); ?>
<?php endif; ?>
                 <?php $__env->endSlot(); ?>
            <?php endif; ?>

            <?php if(! $info['has_settings']): ?>
                <div class="py-6 text-center">
                    <?php if (isset($component)) { $__componentOriginalbfc641e0710ce04e5fe02876ffc6f950 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbfc641e0710ce04e5fe02876ffc6f950 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.icon','data' => ['icon' => 'heroicon-o-cog-6-tooth','class' => 'mx-auto mb-2 h-8 w-8 text-gray-300 dark:text-gray-700']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'heroicon-o-cog-6-tooth','class' => 'mx-auto mb-2 h-8 w-8 text-gray-300 dark:text-gray-700']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalbfc641e0710ce04e5fe02876ffc6f950)): ?>
<?php $attributes = $__attributesOriginalbfc641e0710ce04e5fe02876ffc6f950; ?>
<?php unset($__attributesOriginalbfc641e0710ce04e5fe02876ffc6f950); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalbfc641e0710ce04e5fe02876ffc6f950)): ?>
<?php $component = $__componentOriginalbfc641e0710ce04e5fe02876ffc6f950; ?>
<?php unset($__componentOriginalbfc641e0710ce04e5fe02876ffc6f950); ?>
<?php endif; ?>
                    <p class="text-sm text-gray-500">Модуль не оголошує налаштувань</p>
                    <p class="mt-1 text-xs text-gray-400">Додай <code class="font-mono">settings_schema</code> у <code class="font-mono">module.json</code></p>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php $__currentLoopData = $info['settings_schema']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $settingKey => $schema): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $type = $schema['type'] ?? 'string';
                            $hasError = ! empty($this->settingsErrors[$settingKey]);
                            $errorMsg = $this->settingsErrors[$settingKey] ?? null;
                            $label = $schema['label'] ?? $settingKey;
                            $help = $schema['help'] ?? null;
                            $required = $schema['required'] ?? false;
                            $fieldClass = $inputClass . ($hasError ? ' ring-danger-500 dark:ring-danger-500' : '');
                        ?>
                        <div>
                            <div class="mb-1 flex items-baseline justify-between">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-200">
                                    <?php echo e($label); ?>

                                    <?php if($required): ?><span class="text-danger-500" title="обов'язкове">*</span><?php endif; ?>
                                </label>
                                <span class="font-mono text-[10px] uppercase tracking-wide text-gray-400"><?php echo e($type); ?></span>
                            </div>
                            <?php if($type === 'bool'): ?>
                                <label class="inline-flex cursor-pointer items-center gap-2">
                                    <input type="checkbox" wire:model="settings.<?php echo e($settingKey); ?>" class="fi-checkbox-input rounded border-none bg-white shadow-sm ring-1 ring-gray-950/10 checked:ring-0 dark:bg-white/5 dark:ring-white/20" />
                                    <span class="text-xs text-gray-500">Увімкнено</span>
                                </label>
                            <?php elseif($type === 'int' || $type === 'float'): ?>
                                <input type="number" wire:model="settings.<?php echo e($settingKey); ?>"
                                    <?php if(isset($schema['min'])): ?>min="<?php echo e($schema['min']); ?>"<?php endif; ?>
                                    <?php if(isset($schema['max'])): ?>max="<?php echo e($schema['max']); ?>"<?php endif; ?>
                                    <?php if($type === 'float'): ?> step="0.01" <?php endif; ?>
                                    placeholder="<?php echo e($schema['default'] ?? ''); ?>" class="<?php echo e($fieldClass); ?>" />
                            <?php elseif(! empty($schema['enum'])): ?>
                                <select wire:model="settings.<?php echo e($settingKey); ?>" class="<?php echo e($fieldClass); ?>">
                                    <option value="">— оберіть —</option>
                                    <?php $__currentLoopData = $schema['enum']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($opt); ?>"><?php echo e($opt); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            <?php else: ?>
                                <input type="text" wire:model="settings.<?php echo e($settingKey); ?>" placeholder="<?php echo e($schema['default'] ?? ''); ?>" class="<?php echo e($fieldClass); ?>" />
                            <?php endif; ?>
                            <?php if($hasError): ?>
                                <p class="mt-1 flex items-center gap-1 text-xs text-danger-600 dark:text-danger-400">
                                    <?php if (isset($component)) { $__componentOriginalbfc641e0710ce04e5fe02876ffc6f950 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbfc641e0710ce04e5fe02876ffc6f950 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.icon','data' => ['icon' => 'heroicon-o-exclamation-circle','class' => 'h-3 w-3']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'heroicon-o-exclamation-circle','class' => 'h-3 w-3']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalbfc641e0710ce04e5fe02876ffc6f950)): ?>
<?php $attributes = $__attributesOriginalbfc641e0710ce04e5fe02876ffc6f950; ?>
<?php unset($__attributesOriginalbfc641e0710ce04e5fe02876ffc6f950); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalbfc641e0710ce04e5fe02876ffc6f950)): ?>
<?php $component = $__componentOriginalbfc641e0710ce04e5fe02876ffc6f950; ?>
<?php unset($__componentOriginalbfc641e0710ce04e5fe02876ffc6f950); ?>
<?php endif; ?>
                                    <?php echo e($errorMsg); ?>

                                </p>
                            <?php elseif($help): ?>
                                <p class="mt-1 text-xs text-gray-500"><?php echo e($help); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex gap-2 pt-1">
                        <?php if (isset($component)) { $__componentOriginal6330f08526bbb3ce2a0da37da512a11f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => 'saveSettings','wire:loading.attr' => 'disabled','wire:target' => 'saveSettings','color' => 'primary','size' => 'sm','icon' => 'heroicon-o-check']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'saveSettings','wire:loading.attr' => 'disabled','wire:target' => 'saveSettings','color' => 'primary','size' => 'sm','icon' => 'heroicon-o-check']); ?>
                            <span wire:loading.remove wire:target="saveSettings">Зберегти</span>
                            <span wire:loading wire:target="saveSettings">Зберігаю…</span>
                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6330f08526bbb3ce2a0da37da512a11f)): ?>
<?php $attributes = $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f; ?>
<?php unset($__attributesOriginal6330f08526bbb3ce2a0da37da512a11f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6330f08526bbb3ce2a0da37da512a11f)): ?>
<?php $component = $__componentOriginal6330f08526bbb3ce2a0da37da512a11f; ?>
<?php unset($__componentOriginal6330f08526bbb3ce2a0da37da512a11f); ?>
<?php endif; ?>
                        <?php if (isset($component)) { $__componentOriginal6330f08526bbb3ce2a0da37da512a11f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => 'resetSettings','wire:confirm' => 'Скинути всі налаштування до значень з manifest?','color' => 'gray','size' => 'sm','outlined' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'resetSettings','wire:confirm' => 'Скинути всі налаштування до значень з manifest?','color' => 'gray','size' => 'sm','outlined' => true]); ?>
                            Скинути
                         <?php echo $__env->renderComponent(); ?>
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
    </div>

    
    <?php if (isset($component)) { $__componentOriginalee08b1367eba38734199cf7829b1d1e9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalee08b1367eba38734199cf7829b1d1e9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.section.index','data' => ['icon' => $overallHealth==='ok' ? 'heroicon-o-check-circle' : ($overallHealth==='warning' ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-x-circle'),'iconColor' => $overallHealth==='ok' ? 'success' : ($overallHealth==='warning' ? 'warning' : 'danger')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($overallHealth==='ok' ? 'heroicon-o-check-circle' : ($overallHealth==='warning' ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-x-circle')),'icon-color' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($overallHealth==='ok' ? 'success' : ($overallHealth==='warning' ? 'warning' : 'danger'))]); ?>
         <?php $__env->slot('heading', null, []); ?> Стан здоров'я <?php $__env->endSlot(); ?>
         <?php $__env->slot('headerEnd', null, []); ?> 
            <div class="flex items-center gap-1.5">
                <?php if($healthCounts['ok'] > 0): ?><?php if (isset($component)) { $__componentOriginal986dce9114ddce94a270ab00ce6c273d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal986dce9114ddce94a270ab00ce6c273d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.badge','data' => ['color' => 'success','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'success','size' => 'sm']); ?><?php echo e($healthCounts['ok']); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal986dce9114ddce94a270ab00ce6c273d)): ?>
<?php $attributes = $__attributesOriginal986dce9114ddce94a270ab00ce6c273d; ?>
<?php unset($__attributesOriginal986dce9114ddce94a270ab00ce6c273d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal986dce9114ddce94a270ab00ce6c273d)): ?>
<?php $component = $__componentOriginal986dce9114ddce94a270ab00ce6c273d; ?>
<?php unset($__componentOriginal986dce9114ddce94a270ab00ce6c273d); ?>
<?php endif; ?><?php endif; ?>
                <?php if($healthCounts['warning'] > 0): ?><?php if (isset($component)) { $__componentOriginal986dce9114ddce94a270ab00ce6c273d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal986dce9114ddce94a270ab00ce6c273d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.badge','data' => ['color' => 'warning','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'warning','size' => 'sm']); ?><?php echo e($healthCounts['warning']); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal986dce9114ddce94a270ab00ce6c273d)): ?>
<?php $attributes = $__attributesOriginal986dce9114ddce94a270ab00ce6c273d; ?>
<?php unset($__attributesOriginal986dce9114ddce94a270ab00ce6c273d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal986dce9114ddce94a270ab00ce6c273d)): ?>
<?php $component = $__componentOriginal986dce9114ddce94a270ab00ce6c273d; ?>
<?php unset($__componentOriginal986dce9114ddce94a270ab00ce6c273d); ?>
<?php endif; ?><?php endif; ?>
                <?php if($healthCounts['error'] > 0): ?><?php if (isset($component)) { $__componentOriginal986dce9114ddce94a270ab00ce6c273d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal986dce9114ddce94a270ab00ce6c273d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.badge','data' => ['color' => 'danger','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'danger','size' => 'sm']); ?><?php echo e($healthCounts['error']); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal986dce9114ddce94a270ab00ce6c273d)): ?>
<?php $attributes = $__attributesOriginal986dce9114ddce94a270ab00ce6c273d; ?>
<?php unset($__attributesOriginal986dce9114ddce94a270ab00ce6c273d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal986dce9114ddce94a270ab00ce6c273d)): ?>
<?php $component = $__componentOriginal986dce9114ddce94a270ab00ce6c273d; ?>
<?php unset($__componentOriginal986dce9114ddce94a270ab00ce6c273d); ?>
<?php endif; ?><?php endif; ?>
            </div>
         <?php $__env->endSlot(); ?>

        <ul class="divide-y divide-gray-100 dark:divide-white/5">
            <?php $__currentLoopData = $health; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $check): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li class="flex items-center gap-3 py-2.5 first:pt-0 last:pb-0">
                    <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                        'h-2 w-2 shrink-0 rounded-full',
                        'bg-success-500' => $check['status']==='ok',
                        'bg-warning-500' => $check['status']==='warning',
                        'bg-danger-500' => $check['status']==='error',
                    ]); ?>"></span>
                    <div class="min-w-0" style="flex:1 1 0%">
                        <span class="text-sm text-gray-900 dark:text-gray-100"><?php echo e($check['label']); ?></span>
                        <?php if($check['detail']): ?>
                            <span class="ml-1.5 text-xs text-gray-500">— <?php echo e($check['detail']); ?></span>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.section.index','data' => ['icon' => 'heroicon-o-clock','iconColor' => 'gray']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'heroicon-o-clock','icon-color' => 'gray']); ?>
         <?php $__env->slot('heading', null, []); ?> Активність <?php $__env->endSlot(); ?>
        <?php if($activity->count() > 0): ?>
             <?php $__env->slot('headerEnd', null, []); ?> 
                <?php if (isset($component)) { $__componentOriginal986dce9114ddce94a270ab00ce6c273d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal986dce9114ddce94a270ab00ce6c273d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.badge','data' => ['color' => 'gray']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'gray']); ?><?php echo e($activity->count()); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal986dce9114ddce94a270ab00ce6c273d)): ?>
<?php $attributes = $__attributesOriginal986dce9114ddce94a270ab00ce6c273d; ?>
<?php unset($__attributesOriginal986dce9114ddce94a270ab00ce6c273d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal986dce9114ddce94a270ab00ce6c273d)): ?>
<?php $component = $__componentOriginal986dce9114ddce94a270ab00ce6c273d; ?>
<?php unset($__componentOriginal986dce9114ddce94a270ab00ce6c273d); ?>
<?php endif; ?>
             <?php $__env->endSlot(); ?>
        <?php endif; ?>

        <?php if($activity->count() === 0): ?>
            <div class="py-8 text-center">
                <?php if (isset($component)) { $__componentOriginalbfc641e0710ce04e5fe02876ffc6f950 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbfc641e0710ce04e5fe02876ffc6f950 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.icon','data' => ['icon' => 'heroicon-o-clock','class' => 'mx-auto mb-1.5 h-7 w-7 text-gray-300 dark:text-gray-700']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'heroicon-o-clock','class' => 'mx-auto mb-1.5 h-7 w-7 text-gray-300 dark:text-gray-700']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalbfc641e0710ce04e5fe02876ffc6f950)): ?>
<?php $attributes = $__attributesOriginalbfc641e0710ce04e5fe02876ffc6f950; ?>
<?php unset($__attributesOriginalbfc641e0710ce04e5fe02876ffc6f950); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalbfc641e0710ce04e5fe02876ffc6f950)): ?>
<?php $component = $__componentOriginalbfc641e0710ce04e5fe02876ffc6f950; ?>
<?php unset($__componentOriginalbfc641e0710ce04e5fe02876ffc6f950); ?>
<?php endif; ?>
                <p class="text-sm text-gray-500">Поки що порожньо</p>
                <p class="mt-0.5 text-xs text-gray-400">Зміни модуля з'являться тут</p>
            </div>
        <?php else: ?>
            <ul class="divide-y divide-gray-100 dark:divide-white/5">
                <?php $__currentLoopData = $activity; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="flex items-center gap-3 py-2.5 text-sm first:pt-0 last:pb-0">
                        <time class="w-20 shrink-0 font-mono text-xs tabular-nums text-gray-400"><?php echo e($entry->created_at->diffForHumans()); ?></time>
                        <div class="min-w-0" style="flex:1 1 0%">
                            <span class="text-gray-900 dark:text-gray-100"><?php echo e($actionLabels[$entry->action] ?? $entry->action); ?></span>
                            <?php if($entry->user_id): ?><span class="ml-1.5 text-xs text-gray-400">user #<?php echo e($entry->user_id); ?></span><?php endif; ?>
                            <?php if(! empty($entry->payload['from_version']) && ! empty($entry->payload['to_version']) && $entry->payload['from_version'] !== $entry->payload['to_version']): ?>
                                <code class="ml-1.5 rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs text-gray-600 dark:bg-white/10 dark:text-gray-400"><?php echo e($entry->payload['from_version']); ?> → <?php echo e($entry->payload['to_version']); ?></code>
                            <?php endif; ?>
                        </div>
                        <span class="hidden font-mono text-xs text-gray-400 sm:inline"><?php echo e($entry->created_at->format('d.m H:i')); ?></span>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
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

    
    <div style="display:grid;gap:1.5rem;grid-template-columns:repeat(auto-fit,minmax(340px,1fr))">

        <?php if (isset($component)) { $__componentOriginalee08b1367eba38734199cf7829b1d1e9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalee08b1367eba38734199cf7829b1d1e9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.section.index','data' => ['icon' => 'heroicon-o-document-text','iconColor' => 'gray']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'heroicon-o-document-text','icon-color' => 'gray']); ?>
             <?php $__env->slot('heading', null, []); ?> Manifest <?php $__env->endSlot(); ?>

            <div class="space-y-3 text-sm">
                <?php $__currentLoopData = [
                    'providers' => 'Service providers',
                    'filament_resources' => 'Resources',
                    'filament_pages' => 'Pages',
                    'filament_widgets' => 'Widgets',
                    'composer_packages' => 'Composer пакети',
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if(! empty($info[$field])): ?>
                        <div>
                            <div class="mb-1.5 text-[11px] uppercase tracking-wide text-gray-500"><?php echo e($label); ?> <span class="text-gray-400"><?php echo e(count($info[$field])); ?></span></div>
                            <div class="space-y-1">
                                <?php $__currentLoopData = $info[$field]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cls): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <code class="block break-all rounded bg-gray-50 px-2 py-1 font-mono text-[11px] text-gray-700 dark:bg-white/5 dark:text-gray-300"><?php echo e($cls); ?></code>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php if($info['views_namespace']): ?>
                    <div>
                        <div class="mb-1.5 text-[11px] uppercase tracking-wide text-gray-500">Views namespace</div>
                        <code class="rounded bg-gray-50 px-2 py-1 font-mono text-[11px] text-gray-700 dark:bg-white/5 dark:text-gray-300"><?php echo e($info['views_namespace']); ?>::view-name</code>
                    </div>
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

        
        <?php if(! empty($info['hook_events'])): ?>
            <?php if (isset($component)) { $__componentOriginalee08b1367eba38734199cf7829b1d1e9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalee08b1367eba38734199cf7829b1d1e9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.section.index','data' => ['icon' => 'heroicon-o-bolt','iconColor' => 'warning']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'heroicon-o-bolt','icon-color' => 'warning']); ?>
                 <?php $__env->slot('heading', null, []); ?> Hook subscriptions <?php $__env->endSlot(); ?>
                 <?php $__env->slot('description', null, []); ?> На які core-events модуль підписаний <?php $__env->endSlot(); ?>

                <ul class="space-y-1.5">
                    <?php $__currentLoopData = $info['hook_events']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $listeners = \App\Support\Hooks::listenersFor($event); ?>
                        <li class="flex items-baseline gap-3 text-xs">
                            <code class="rounded bg-amber-50 px-1.5 py-0.5 font-mono text-amber-700 dark:bg-amber-900/20 dark:text-amber-400"><?php echo e($event); ?></code>
                            <?php $myEntry = collect($listeners)->firstWhere('source', $info['key']); ?>
                            <?php if($myEntry): ?>
                                <span class="text-xs text-gray-500"><?php echo e($myEntry['type']); ?> · priority <?php echo e($myEntry['priority']); ?></span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
                <p class="mt-3 text-xs leading-relaxed text-gray-500">
                    Модуль слухає ці події в core. Якщо вимкнути модуль — listener'и зникають,
                    core працює без них (graceful degradation).
                </p>
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
        <?php endif; ?>

        
        <?php if (isset($component)) { $__componentOriginalee08b1367eba38734199cf7829b1d1e9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalee08b1367eba38734199cf7829b1d1e9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.section.index','data' => ['icon' => 'heroicon-o-folder','iconColor' => 'gray']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'heroicon-o-folder','icon-color' => 'gray']); ?>
             <?php $__env->slot('heading', null, []); ?> Файли <?php $__env->endSlot(); ?>

            <div class="space-y-3 text-sm">
                <div>
                    <div class="mb-1.5 text-[11px] uppercase tracking-wide text-gray-500">Шлях</div>
                    <code class="block break-all rounded bg-gray-50 px-2 py-1 font-mono text-[11px] text-gray-700 dark:bg-white/5 dark:text-gray-300"><?php echo e(str_replace(base_path().'/', '', $info['module_path'])); ?></code>
                    <span class="mt-1 block text-[11px] text-gray-500"><?php echo e($info['folder_exists'] ? '✓ існує' : '✗ відсутня'); ?> · <?php echo e($info['file_count']); ?> файлів</span>
                </div>
                <?php if($info['migrations_count'] > 0): ?>
                    <details class="group">
                        <summary class="flex cursor-pointer select-none items-center gap-1.5 text-[11px] uppercase tracking-wide text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                            <?php if (isset($component)) { $__componentOriginalbfc641e0710ce04e5fe02876ffc6f950 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbfc641e0710ce04e5fe02876ffc6f950 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.icon','data' => ['icon' => 'heroicon-o-chevron-right','class' => 'h-3 w-3 transition-transform group-open:rotate-90']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'heroicon-o-chevron-right','class' => 'h-3 w-3 transition-transform group-open:rotate-90']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalbfc641e0710ce04e5fe02876ffc6f950)): ?>
<?php $attributes = $__attributesOriginalbfc641e0710ce04e5fe02876ffc6f950; ?>
<?php unset($__attributesOriginalbfc641e0710ce04e5fe02876ffc6f950); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalbfc641e0710ce04e5fe02876ffc6f950)): ?>
<?php $component = $__componentOriginalbfc641e0710ce04e5fe02876ffc6f950; ?>
<?php unset($__componentOriginalbfc641e0710ce04e5fe02876ffc6f950); ?>
<?php endif; ?>
                            Migrations <span class="text-gray-400"><?php echo e($info['migrations_count']); ?></span>
                        </summary>
                        <div class="mt-2 space-y-1">
                            <?php $__currentLoopData = $info['migrations']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mig): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <code class="block break-all rounded bg-gray-50 px-2 py-1 font-mono text-[11px] text-gray-700 dark:bg-white/5 dark:text-gray-300"><?php echo e($mig); ?></code>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <div class="pt-1">
                                <?php if (isset($component)) { $__componentOriginal6330f08526bbb3ce2a0da37da512a11f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => 'runMigrations','wire:loading.attr' => 'disabled','wire:target' => 'runMigrations','color' => 'gray','size' => 'sm','outlined' => true,'icon' => 'heroicon-o-play']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'runMigrations','wire:loading.attr' => 'disabled','wire:target' => 'runMigrations','color' => 'gray','size' => 'sm','outlined' => true,'icon' => 'heroicon-o-play']); ?>
                                    <span wire:loading.remove wire:target="runMigrations">Запустити migrate</span>
                                    <span wire:loading wire:target="runMigrations">Виконую…</span>
                                 <?php echo $__env->renderComponent(); ?>
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
                        </div>
                    </details>
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
    </div>

    
    <?php if (isset($component)) { $__componentOriginalee08b1367eba38734199cf7829b1d1e9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalee08b1367eba38734199cf7829b1d1e9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.section.index','data' => ['icon' => 'heroicon-o-bug-ant','iconColor' => 'gray']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'heroicon-o-bug-ant','icon-color' => 'gray']); ?>
         <?php $__env->slot('heading', null, []); ?> Debug info <?php $__env->endSlot(); ?>
         <?php $__env->slot('description', null, []); ?> Файли · routes · DB tables · hooks · env. Підвантажується при потребі. <?php $__env->endSlot(); ?>
         <?php $__env->slot('headerEnd', null, []); ?> 
            <?php if (isset($component)) { $__componentOriginal6330f08526bbb3ce2a0da37da512a11f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => 'toggleDebug','wire:loading.attr' => 'disabled','wire:target' => 'toggleDebug','color' => 'gray','size' => 'sm','outlined' => true,'icon' => $showDebug ? 'heroicon-o-eye-slash' : 'heroicon-o-eye']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'toggleDebug','wire:loading.attr' => 'disabled','wire:target' => 'toggleDebug','color' => 'gray','size' => 'sm','outlined' => true,'icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($showDebug ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')]); ?>
                <?php echo e($showDebug ? 'Сховати' : 'Показати'); ?>

             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6330f08526bbb3ce2a0da37da512a11f)): ?>
<?php $attributes = $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f; ?>
<?php unset($__attributesOriginal6330f08526bbb3ce2a0da37da512a11f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6330f08526bbb3ce2a0da37da512a11f)): ?>
<?php $component = $__componentOriginal6330f08526bbb3ce2a0da37da512a11f; ?>
<?php unset($__componentOriginal6330f08526bbb3ce2a0da37da512a11f); ?>
<?php endif; ?>
         <?php $__env->endSlot(); ?>

        <?php if($showDebug): ?>
            <?php $debug = $this->getDebugInfo(); ?>
            <div class="-mx-6 -mb-6 divide-y divide-gray-100 border-t border-gray-200 dark:divide-white/5 dark:border-white/10">

                
                <details open class="group">
                    <summary class="flex cursor-pointer select-none items-center gap-1.5 px-6 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-white/5">
                        <?php if (isset($component)) { $__componentOriginalbfc641e0710ce04e5fe02876ffc6f950 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbfc641e0710ce04e5fe02876ffc6f950 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.icon','data' => ['icon' => 'heroicon-o-chevron-right','class' => 'h-3 w-3 transition-transform group-open:rotate-90']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'heroicon-o-chevron-right','class' => 'h-3 w-3 transition-transform group-open:rotate-90']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalbfc641e0710ce04e5fe02876ffc6f950)): ?>
<?php $attributes = $__attributesOriginalbfc641e0710ce04e5fe02876ffc6f950; ?>
<?php unset($__attributesOriginalbfc641e0710ce04e5fe02876ffc6f950); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalbfc641e0710ce04e5fe02876ffc6f950)): ?>
<?php $component = $__componentOriginalbfc641e0710ce04e5fe02876ffc6f950; ?>
<?php unset($__componentOriginalbfc641e0710ce04e5fe02876ffc6f950); ?>
<?php endif; ?>
                        Файлове дерево
                        <span class="ml-1 text-gray-400"><?php echo e($debug['file_tree_total']); ?></span>
                        <?php if($debug['file_tree_total'] === 40): ?><span class="ml-1 text-[10px] text-amber-600">(перші 40)</span><?php endif; ?>
                    </summary>
                    <div class="bg-gray-50/50 px-6 py-3 dark:bg-white/5">
                        <div class="space-y-0.5 font-mono text-[11px] leading-relaxed text-gray-700 dark:text-gray-300">
                            <?php $__currentLoopData = $debug['file_tree']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="truncate"><?php echo e($f); ?></div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                </details>

                
                <details class="group">
                    <summary class="flex cursor-pointer select-none items-center gap-1.5 px-6 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-white/5">
                        <?php if (isset($component)) { $__componentOriginalbfc641e0710ce04e5fe02876ffc6f950 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbfc641e0710ce04e5fe02876ffc6f950 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.icon','data' => ['icon' => 'heroicon-o-chevron-right','class' => 'h-3 w-3 transition-transform group-open:rotate-90']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'heroicon-o-chevron-right','class' => 'h-3 w-3 transition-transform group-open:rotate-90']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalbfc641e0710ce04e5fe02876ffc6f950)): ?>
<?php $attributes = $__attributesOriginalbfc641e0710ce04e5fe02876ffc6f950; ?>
<?php unset($__attributesOriginalbfc641e0710ce04e5fe02876ffc6f950); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalbfc641e0710ce04e5fe02876ffc6f950)): ?>
<?php $component = $__componentOriginalbfc641e0710ce04e5fe02876ffc6f950; ?>
<?php unset($__componentOriginalbfc641e0710ce04e5fe02876ffc6f950); ?>
<?php endif; ?>
                        Routes зареєстровано
                        <span class="ml-1 text-gray-400"><?php echo e(count($debug['routes'])); ?></span>
                    </summary>
                    <div class="bg-gray-50/50 px-6 py-3 dark:bg-white/5">
                        <?php if(empty($debug['routes'])): ?>
                            <p class="text-[11px] text-gray-500">Жодного route не зареєстровано через цей модуль.</p>
                        <?php else: ?>
                            <div class="space-y-1 font-mono text-[11px]">
                                <?php $__currentLoopData = $debug['routes']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="flex items-center gap-2">
                                        <span class="w-16 shrink-0 text-gray-500"><?php echo e($r['method']); ?></span>
                                        <span class="flex-1 truncate text-gray-900 dark:text-gray-100"><?php echo e($r['uri']); ?></span>
                                        <span class="shrink-0 text-gray-400"><?php echo e($r['name']); ?></span>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </details>

                
                <details class="group">
                    <summary class="flex cursor-pointer select-none items-center gap-1.5 px-6 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-white/5">
                        <?php if (isset($component)) { $__componentOriginalbfc641e0710ce04e5fe02876ffc6f950 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbfc641e0710ce04e5fe02876ffc6f950 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.icon','data' => ['icon' => 'heroicon-o-chevron-right','class' => 'h-3 w-3 transition-transform group-open:rotate-90']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'heroicon-o-chevron-right','class' => 'h-3 w-3 transition-transform group-open:rotate-90']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalbfc641e0710ce04e5fe02876ffc6f950)): ?>
<?php $attributes = $__attributesOriginalbfc641e0710ce04e5fe02876ffc6f950; ?>
<?php unset($__attributesOriginalbfc641e0710ce04e5fe02876ffc6f950); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalbfc641e0710ce04e5fe02876ffc6f950)): ?>
<?php $component = $__componentOriginalbfc641e0710ce04e5fe02876ffc6f950; ?>
<?php unset($__componentOriginalbfc641e0710ce04e5fe02876ffc6f950); ?>
<?php endif; ?>
                        DB-таблиці + кількість рядків
                        <span class="ml-1 text-gray-400"><?php echo e(count($debug['table_counts'])); ?></span>
                    </summary>
                    <div class="bg-gray-50/50 px-6 py-3 dark:bg-white/5">
                        <?php if(empty($debug['table_counts'])): ?>
                            <p class="text-[11px] text-gray-500">Таблиць з ім'ям, що містить «<?php echo e($info['key']); ?>», не знайдено.</p>
                        <?php else: ?>
                            <div class="space-y-0.5 font-mono text-[11px]">
                                <?php $__currentLoopData = $debug['table_counts']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $table => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="flex items-center justify-between">
                                        <span class="text-gray-900 dark:text-gray-100"><?php echo e($table); ?></span>
                                        <span class="tabular-nums text-gray-500"><?php echo e($count); ?> rows</span>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </details>

                
                <details class="group">
                    <summary class="flex cursor-pointer select-none items-center gap-1.5 px-6 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-white/5">
                        <?php if (isset($component)) { $__componentOriginalbfc641e0710ce04e5fe02876ffc6f950 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbfc641e0710ce04e5fe02876ffc6f950 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.icon','data' => ['icon' => 'heroicon-o-chevron-right','class' => 'h-3 w-3 transition-transform group-open:rotate-90']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'heroicon-o-chevron-right','class' => 'h-3 w-3 transition-transform group-open:rotate-90']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalbfc641e0710ce04e5fe02876ffc6f950)): ?>
<?php $attributes = $__attributesOriginalbfc641e0710ce04e5fe02876ffc6f950; ?>
<?php unset($__attributesOriginalbfc641e0710ce04e5fe02876ffc6f950); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalbfc641e0710ce04e5fe02876ffc6f950)): ?>
<?php $component = $__componentOriginalbfc641e0710ce04e5fe02876ffc6f950; ?>
<?php unset($__componentOriginalbfc641e0710ce04e5fe02876ffc6f950); ?>
<?php endif; ?>
                        Hooks listeners (global)
                        <span class="ml-1 text-gray-400"><?php echo e(count($debug['hook_listeners'])); ?></span>
                    </summary>
                    <div class="bg-gray-50/50 px-6 py-3 dark:bg-white/5">
                        <?php if(empty($debug['hook_listeners'])): ?>
                            <p class="text-[11px] text-gray-500">Жодного слухача не зареєстровано через Hooks API.</p>
                        <?php else: ?>
                            <div class="space-y-0.5 font-mono text-[11px]">
                                <?php $__currentLoopData = $debug['hook_listeners']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="flex items-center justify-between">
                                        <span class="text-gray-900 dark:text-gray-100"><?php echo e($event); ?></span>
                                        <span class="tabular-nums text-gray-500"><?php echo e($count); ?> listener<?php echo e($count === 1 ? '' : 's'); ?></span>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </details>

                
                <details class="group">
                    <summary class="flex cursor-pointer select-none items-center gap-1.5 px-6 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-white/5">
                        <?php if (isset($component)) { $__componentOriginalbfc641e0710ce04e5fe02876ffc6f950 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbfc641e0710ce04e5fe02876ffc6f950 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.icon','data' => ['icon' => 'heroicon-o-chevron-right','class' => 'h-3 w-3 transition-transform group-open:rotate-90']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'heroicon-o-chevron-right','class' => 'h-3 w-3 transition-transform group-open:rotate-90']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalbfc641e0710ce04e5fe02876ffc6f950)): ?>
<?php $attributes = $__attributesOriginalbfc641e0710ce04e5fe02876ffc6f950; ?>
<?php unset($__attributesOriginalbfc641e0710ce04e5fe02876ffc6f950); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalbfc641e0710ce04e5fe02876ffc6f950)): ?>
<?php $component = $__componentOriginalbfc641e0710ce04e5fe02876ffc6f950; ?>
<?php unset($__componentOriginalbfc641e0710ce04e5fe02876ffc6f950); ?>
<?php endif; ?>
                        PHP class_exists перевірка
                    </summary>
                    <div class="space-y-2 bg-gray-50/50 px-6 py-3 font-mono text-[11px] dark:bg-white/5">
                        <?php $__currentLoopData = ['providers' => 'Providers', 'resources' => 'Filament resources']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if(! empty($debug['php_class_loaded_check'][$field])): ?>
                                <div>
                                    <div class="mb-1 text-gray-500"><?php echo e($label); ?></div>
                                    <?php $__currentLoopData = $debug['php_class_loaded_check'][$field]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $check): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="flex items-center gap-2">
                                            <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                                'block h-1.5 w-1.5 shrink-0 rounded-full',
                                                'bg-success-500' => $check['exists'],
                                                'bg-danger-500' => ! $check['exists'],
                                            ]); ?>"></span>
                                            <span class="break-all text-gray-900 dark:text-gray-100"><?php echo e($check['class']); ?></span>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <div class="border-t border-gray-200 pt-2 dark:border-white/10">
                            <span class="text-gray-500">Composer classmap matches:</span>
                            <span class="ml-2 text-gray-900 dark:text-gray-100"><?php echo e($debug['composer_classmap_check']['matches'] ?? '?'); ?></span>
                        </div>
                    </div>
                </details>

                
                <details class="group">
                    <summary class="flex cursor-pointer select-none items-center gap-1.5 px-6 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-white/5">
                        <?php if (isset($component)) { $__componentOriginalbfc641e0710ce04e5fe02876ffc6f950 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbfc641e0710ce04e5fe02876ffc6f950 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.icon','data' => ['icon' => 'heroicon-o-chevron-right','class' => 'h-3 w-3 transition-transform group-open:rotate-90']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'heroicon-o-chevron-right','class' => 'h-3 w-3 transition-transform group-open:rotate-90']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalbfc641e0710ce04e5fe02876ffc6f950)): ?>
<?php $attributes = $__attributesOriginalbfc641e0710ce04e5fe02876ffc6f950; ?>
<?php unset($__attributesOriginalbfc641e0710ce04e5fe02876ffc6f950); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalbfc641e0710ce04e5fe02876ffc6f950)): ?>
<?php $component = $__componentOriginalbfc641e0710ce04e5fe02876ffc6f950; ?>
<?php unset($__componentOriginalbfc641e0710ce04e5fe02876ffc6f950); ?>
<?php endif; ?>
                        ENV
                    </summary>
                    <div class="space-y-0.5 bg-gray-50/50 px-6 py-3 font-mono text-[11px] dark:bg-white/5">
                        <?php $__currentLoopData = $debug['env_vars']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $envKey => $envVal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="flex items-baseline gap-2">
                                <span class="w-44 shrink-0 text-gray-500"><?php echo e($envKey); ?></span>
                                <span class="text-gray-900 dark:text-gray-100"><?php echo e($envVal === null ? '(unset)' : var_export($envVal, true)); ?></span>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </details>

                
                <details class="group">
                    <summary class="flex cursor-pointer select-none items-center gap-1.5 px-6 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-gray-600 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-white/5">
                        <?php if (isset($component)) { $__componentOriginalbfc641e0710ce04e5fe02876ffc6f950 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbfc641e0710ce04e5fe02876ffc6f950 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.icon','data' => ['icon' => 'heroicon-o-chevron-right','class' => 'h-3 w-3 transition-transform group-open:rotate-90']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'heroicon-o-chevron-right','class' => 'h-3 w-3 transition-transform group-open:rotate-90']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalbfc641e0710ce04e5fe02876ffc6f950)): ?>
<?php $attributes = $__attributesOriginalbfc641e0710ce04e5fe02876ffc6f950; ?>
<?php unset($__attributesOriginalbfc641e0710ce04e5fe02876ffc6f950); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalbfc641e0710ce04e5fe02876ffc6f950)): ?>
<?php $component = $__componentOriginalbfc641e0710ce04e5fe02876ffc6f950; ?>
<?php unset($__componentOriginalbfc641e0710ce04e5fe02876ffc6f950); ?>
<?php endif; ?>
                        module.json (raw)
                    </summary>
                    <pre class="overflow-x-auto bg-gray-50/50 px-6 py-3 font-mono text-[11px] leading-relaxed text-gray-700 dark:bg-white/5 dark:text-gray-400"><code><?php echo e(json_encode($debug['manifest'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)); ?></code></pre>
                </details>
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

    
    <div>
        <?php if (isset($component)) { $__componentOriginal549c94d872270b69c72bdf48cb183bc9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal549c94d872270b69c72bdf48cb183bc9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.link','data' => ['tag' => 'a','href' => ''.e(route('filament.admin.pages.modules')).'','icon' => 'heroicon-o-arrow-left','color' => 'gray']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['tag' => 'a','href' => ''.e(route('filament.admin.pages.modules')).'','icon' => 'heroicon-o-arrow-left','color' => 'gray']); ?>
            Усі модулі
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal549c94d872270b69c72bdf48cb183bc9)): ?>
<?php $attributes = $__attributesOriginal549c94d872270b69c72bdf48cb183bc9; ?>
<?php unset($__attributesOriginal549c94d872270b69c72bdf48cb183bc9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal549c94d872270b69c72bdf48cb183bc9)): ?>
<?php $component = $__componentOriginal549c94d872270b69c72bdf48cb183bc9; ?>
<?php unset($__componentOriginal549c94d872270b69c72bdf48cb183bc9); ?>
<?php endif; ?>
    </div>
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
<?php /**PATH /home/lionex/projects/gazu-shop/resources/views/filament/pages/module-detail.blade.php ENDPATH**/ ?>