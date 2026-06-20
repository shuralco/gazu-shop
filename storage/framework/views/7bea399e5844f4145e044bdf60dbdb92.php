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
    <div class="space-y-6">

        
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Перетягуйте модулі для зміни порядку. Натисніть на модуль для редагування налаштувань.
                </p>
            </div>
            <?php if (isset($component)) { $__componentOriginal6330f08526bbb3ce2a0da37da512a11f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => 'openAddModal','icon' => 'heroicon-o-plus']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'openAddModal','icon' => 'heroicon-o-plus']); ?>
                Додати модуль
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

        
        <div class="space-y-3">
            <?php $modules = $this->getModules(); ?>
            <?php $__empty_1 = true; $__currentLoopData = $modules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $types = $this->getAvailableTypes();
                    $typeInfo = $types[$module->type] ?? ['name' => $module->type, 'emoji' => '📦', 'description' => ''];
                ?>
                <div class="relative bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 transition-all
                    <?php echo e(!$module->is_active ? 'opacity-50' : ''); ?>">
                    <div class="flex items-center gap-4">
                        
                        <div class="flex flex-col gap-1">
                            <button wire:click="moveUp(<?php echo e($module->id); ?>)"
                                    class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
                                    title="Вгору">
                                <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-chevron-up'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-4 h-4']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
                            </button>
                            <button wire:click="moveDown(<?php echo e($module->id); ?>)"
                                    class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
                                    title="Вниз">
                                <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-chevron-down'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-4 h-4']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
                            </button>
                        </div>

                        
                        <div class="flex-shrink-0 w-12 h-12 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center text-2xl">
                            <?php echo e($typeInfo['emoji']); ?>

                        </div>

                        
                        <div class="flex-grow min-w-0">
                            <div class="flex items-center gap-2">
                                <h3 class="font-semibold text-gray-900 dark:text-white truncate">
                                    <?php echo e($module->title ?: $typeInfo['name']); ?>

                                </h3>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400">
                                    <?php echo e($typeInfo['name']); ?>

                                </span>
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                                <?php echo e($typeInfo['description']); ?>

                            </p>
                        </div>

                        
                        <div class="flex-shrink-0 w-8 h-8 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                            <span class="text-xs font-bold text-gray-500 dark:text-gray-400"><?php echo e($module->sort_order); ?></span>
                        </div>

                        
                        <div class="flex items-center gap-2 flex-shrink-0">
                            
                            <button wire:click="toggleModule(<?php echo e($module->id); ?>)"
                                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none
                                        <?php echo e($module->is_active ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-600'); ?>"
                                    title="<?php echo e($module->is_active ? 'Вимкнути' : 'Увімкнути'); ?>">
                                <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out
                                    <?php echo e($module->is_active ? 'translate-x-5' : 'translate-x-0'); ?>"></span>
                            </button>

                            
                            <?php if (isset($component)) { $__componentOriginalf0029cce6d19fd6d472097ff06a800a1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf0029cce6d19fd6d472097ff06a800a1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.icon-button','data' => ['wire:click' => 'openEditModal('.e($module->id).')','icon' => 'heroicon-o-pencil-square','color' => 'gray','tooltip' => 'Редагувати']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::icon-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'openEditModal('.e($module->id).')','icon' => 'heroicon-o-pencil-square','color' => 'gray','tooltip' => 'Редагувати']); ?>
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

                            
                            <?php if (isset($component)) { $__componentOriginalf0029cce6d19fd6d472097ff06a800a1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf0029cce6d19fd6d472097ff06a800a1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.icon-button','data' => ['wire:click' => 'deleteModule('.e($module->id).')','wire:confirm' => 'Видалити цей модуль?','icon' => 'heroicon-o-trash','color' => 'danger','tooltip' => 'Видалити']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::icon-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'deleteModule('.e($module->id).')','wire:confirm' => 'Видалити цей модуль?','icon' => 'heroicon-o-trash','color' => 'danger','tooltip' => 'Видалити']); ?>
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
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="text-center py-12">
                    <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-squares-plus'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-12 h-12 mx-auto text-gray-400']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
                    <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">Немає модулів</h3>
                    <p class="mt-2 text-sm text-gray-500">Додайте перший модуль для побудови головної сторінки.</p>
                </div>
            <?php endif; ?>
        </div>

        
        <div class="flex justify-center pt-4">
            <a href="<?php echo e(url('/')); ?>" target="_blank" class="inline-flex items-center gap-2 text-sm text-primary-600 hover:text-primary-700">
                <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-eye'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-4 h-4']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
                Переглянути головну сторінку
            </a>
        </div>
    </div>

    
    <?php if($showAddModal): ?>
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/50 transition-opacity" wire:click="closeAddModal"></div>

            <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-2xl w-full p-6 z-10">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Додати модуль</h2>
                    <button wire:click="closeAddModal" class="text-gray-400 hover:text-gray-600">
                        <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-x-mark'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-6 h-6']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
                    </button>
                </div>

                <div style="display:grid;gap:0.75rem;grid-template-columns:repeat(auto-fit,minmax(240px,1fr))">
                    <?php $__currentLoopData = $this->getAvailableTypes(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $info): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <button wire:click="addModule('<?php echo e($type); ?>')"
                                class="flex items-start gap-3 p-4 rounded-lg border-2 border-gray-200 dark:border-gray-600 hover:border-primary-500 dark:hover:border-primary-500 transition-colors text-left">
                            <span class="text-2xl flex-shrink-0 mt-0.5"><?php echo e($info['emoji']); ?></span>
                            <div>
                                <div class="font-semibold text-gray-900 dark:text-white"><?php echo e($info['name']); ?></div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5"><?php echo e($info['description']); ?></div>
                            </div>
                        </button>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    
    <?php if($showEditModal && $editingModuleId): ?>
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/50 transition-opacity" wire:click="closeEditModal"></div>

            <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-2xl w-full p-6 z-10 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between mb-6">
                    <?php
                        $types = $this->getAvailableTypes();
                        $typeInfo = $types[$editingModuleType] ?? ['name' => $editingModuleType, 'emoji' => '📦'];
                    ?>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                        <?php echo e($typeInfo['emoji']); ?> Редагувати: <?php echo e($typeInfo['name']); ?>

                    </h2>
                    <button wire:click="closeEditModal" class="text-gray-400 hover:text-gray-600">
                        <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-x-mark'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-6 h-6']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
                    </button>
                </div>

                <div class="space-y-4">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Заголовок секції</label>
                        <input type="text" wire:model="moduleTitle"
                               class="fi-input block w-full rounded-lg border-none bg-white px-3 py-1.5 text-sm text-gray-950 shadow-sm ring-1 ring-inset ring-gray-950/10 transition focus:ring-2 focus:ring-inset focus:ring-primary-500 dark:bg-white/5 dark:text-white dark:ring-white/10"
                               placeholder="Заголовок (або залиште порожнім)">
                    </div>

                    
                    <?php if($editingModuleType === 'hero'): ?>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Підзаголовок (badge)</label>
                            <input type="text" wire:model="moduleSettings.subtitle"
                                   class="fi-input block w-full rounded-lg border-none bg-white px-3 py-1.5 text-sm text-gray-950 shadow-sm ring-1 ring-inset ring-gray-950/10 transition focus:ring-2 focus:ring-inset focus:ring-primary-500 dark:bg-white/5 dark:text-white dark:ring-white/10">
                        </div>
                        <div style="display:grid;gap:1rem;grid-template-columns:repeat(auto-fit,minmax(200px,1fr))">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Рядок 1 заголовку</label>
                                <input type="text" wire:model="moduleSettings.title_line1"
                                       class="fi-input block w-full rounded-lg border-none bg-white px-3 py-1.5 text-sm text-gray-950 shadow-sm ring-1 ring-inset ring-gray-950/10 transition focus:ring-2 focus:ring-inset focus:ring-primary-500 dark:bg-white/5 dark:text-white dark:ring-white/10">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Рядок 2 заголовку</label>
                                <input type="text" wire:model="moduleSettings.title_line2"
                                       class="fi-input block w-full rounded-lg border-none bg-white px-3 py-1.5 text-sm text-gray-950 shadow-sm ring-1 ring-inset ring-gray-950/10 transition focus:ring-2 focus:ring-inset focus:ring-primary-500 dark:bg-white/5 dark:text-white dark:ring-white/10">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Опис (кожен рядок окремо)</label>
                            <textarea wire:model="moduleSettings.description" rows="3"
                                      class="fi-input block w-full rounded-lg border-none bg-white px-3 py-1.5 text-sm text-gray-950 shadow-sm ring-1 ring-inset ring-gray-950/10 transition focus:ring-2 focus:ring-inset focus:ring-primary-500 dark:bg-white/5 dark:text-white dark:ring-white/10"></textarea>
                        </div>
                        <div style="display:grid;gap:1rem;grid-template-columns:repeat(auto-fit,minmax(200px,1fr))">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Текст кнопки</label>
                                <input type="text" wire:model="moduleSettings.button_text"
                                       class="fi-input block w-full rounded-lg border-none bg-white px-3 py-1.5 text-sm text-gray-950 shadow-sm ring-1 ring-inset ring-gray-950/10 transition focus:ring-2 focus:ring-inset focus:ring-primary-500 dark:bg-white/5 dark:text-white dark:ring-white/10">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">URL кнопки</label>
                                <input type="text" wire:model="moduleSettings.button_url"
                                       class="fi-input block w-full rounded-lg border-none bg-white px-3 py-1.5 text-sm text-gray-950 shadow-sm ring-1 ring-inset ring-gray-950/10 transition focus:ring-2 focus:ring-inset focus:ring-primary-500 dark:bg-white/5 dark:text-white dark:ring-white/10">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Колір фону</label>
                            <input type="color" wire:model="moduleSettings.bg_color"
                                   class="h-10 w-20 cursor-pointer rounded-lg border-none bg-white shadow-sm ring-1 ring-inset ring-gray-950/10 dark:bg-white/5 dark:ring-white/10">
                        </div>

                    <?php elseif($editingModuleType === 'products_grid'): ?>
                        <div style="display:grid;gap:1rem;grid-template-columns:repeat(auto-fit,minmax(160px,1fr))">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Фільтр</label>
                                <select wire:model="moduleSettings.filter"
                                        class="fi-input block w-full rounded-lg border-none bg-white px-3 py-1.5 text-sm text-gray-950 shadow-sm ring-1 ring-inset ring-gray-950/10 transition focus:ring-2 focus:ring-inset focus:ring-primary-500 dark:bg-white/5 dark:text-white dark:ring-white/10">
                                    <option value="hits">Хіти продажів</option>
                                    <option value="new">Новинки</option>
                                    <option value="specials">Акційні</option>
                                    <option value="all">Всі товари</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Кількість</label>
                                <select wire:model="moduleSettings.limit"
                                        class="fi-input block w-full rounded-lg border-none bg-white px-3 py-1.5 text-sm text-gray-950 shadow-sm ring-1 ring-inset ring-gray-950/10 transition focus:ring-2 focus:ring-inset focus:ring-primary-500 dark:bg-white/5 dark:text-white dark:ring-white/10">
                                    <option value="4">4</option>
                                    <option value="8">8</option>
                                    <option value="12">12</option>
                                    <option value="16">16</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Колонки</label>
                                <select wire:model="moduleSettings.columns"
                                        class="fi-input block w-full rounded-lg border-none bg-white px-3 py-1.5 text-sm text-gray-950 shadow-sm ring-1 ring-inset ring-gray-950/10 transition focus:ring-2 focus:ring-inset focus:ring-primary-500 dark:bg-white/5 dark:text-white dark:ring-white/10">
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                    <option value="5">5</option>
                                </select>
                            </div>
                        </div>

                    <?php elseif($editingModuleType === 'categories'): ?>
                        <div style="display:grid;gap:1rem;grid-template-columns:repeat(auto-fit,minmax(200px,1fr))">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Кількість</label>
                                <input type="number" wire:model="moduleSettings.limit" min="1" max="20"
                                       class="fi-input block w-full rounded-lg border-none bg-white px-3 py-1.5 text-sm text-gray-950 shadow-sm ring-1 ring-inset ring-gray-950/10 transition focus:ring-2 focus:ring-inset focus:ring-primary-500 dark:bg-white/5 dark:text-white dark:ring-white/10">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Стиль</label>
                                <select wire:model="moduleSettings.style"
                                        class="fi-input block w-full rounded-lg border-none bg-white px-3 py-1.5 text-sm text-gray-950 shadow-sm ring-1 ring-inset ring-gray-950/10 transition focus:ring-2 focus:ring-inset focus:ring-primary-500 dark:bg-white/5 dark:text-white dark:ring-white/10">
                                    <option value="grid">Сітка</option>
                                    <option value="list">Список</option>
                                </select>
                            </div>
                        </div>

                    <?php elseif($editingModuleType === 'banner'): ?>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Основний текст</label>
                            <input type="text" wire:model="moduleSettings.text"
                                   class="fi-input block w-full rounded-lg border-none bg-white px-3 py-1.5 text-sm text-gray-950 shadow-sm ring-1 ring-inset ring-gray-950/10 transition focus:ring-2 focus:ring-inset focus:ring-primary-500 dark:bg-white/5 dark:text-white dark:ring-white/10">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Додатковий текст</label>
                            <input type="text" wire:model="moduleSettings.subtext"
                                   class="fi-input block w-full rounded-lg border-none bg-white px-3 py-1.5 text-sm text-gray-950 shadow-sm ring-1 ring-inset ring-gray-950/10 transition focus:ring-2 focus:ring-inset focus:ring-primary-500 dark:bg-white/5 dark:text-white dark:ring-white/10">
                        </div>
                        <div style="display:grid;gap:1rem;grid-template-columns:repeat(auto-fit,minmax(200px,1fr))">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Текст кнопки</label>
                                <input type="text" wire:model="moduleSettings.button_text"
                                       class="fi-input block w-full rounded-lg border-none bg-white px-3 py-1.5 text-sm text-gray-950 shadow-sm ring-1 ring-inset ring-gray-950/10 transition focus:ring-2 focus:ring-inset focus:ring-primary-500 dark:bg-white/5 dark:text-white dark:ring-white/10">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">URL кнопки</label>
                                <input type="text" wire:model="moduleSettings.button_url"
                                       class="fi-input block w-full rounded-lg border-none bg-white px-3 py-1.5 text-sm text-gray-950 shadow-sm ring-1 ring-inset ring-gray-950/10 transition focus:ring-2 focus:ring-inset focus:ring-primary-500 dark:bg-white/5 dark:text-white dark:ring-white/10">
                            </div>
                        </div>
                        <div style="display:grid;gap:1rem;grid-template-columns:repeat(auto-fit,minmax(200px,1fr))">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Колір фону</label>
                                <input type="color" wire:model="moduleSettings.bg_color"
                                       class="h-10 w-20 cursor-pointer rounded-lg border-none bg-white shadow-sm ring-1 ring-inset ring-gray-950/10 dark:bg-white/5 dark:ring-white/10">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Колір тексту</label>
                                <input type="color" wire:model="moduleSettings.text_color"
                                       class="h-10 w-20 cursor-pointer rounded-lg border-none bg-white shadow-sm ring-1 ring-inset ring-gray-950/10 dark:bg-white/5 dark:ring-white/10">
                            </div>
                        </div>

                    <?php elseif($editingModuleType === 'text'): ?>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">HTML контент</label>
                            <textarea wire:model="moduleSettings.content" rows="10"
                                      class="fi-input block w-full rounded-lg border-none bg-white px-3 py-1.5 font-mono text-sm text-gray-950 shadow-sm ring-1 ring-inset ring-gray-950/10 transition focus:ring-2 focus:ring-inset focus:ring-primary-500 dark:bg-white/5 dark:text-white dark:ring-white/10"></textarea>
                        </div>

                    <?php elseif($editingModuleType === 'brands'): ?>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Кількість брендів</label>
                            <input type="number" wire:model="moduleSettings.limit" min="1" max="30"
                                   class="fi-input block w-full rounded-lg border-none bg-white px-3 py-1.5 text-sm text-gray-950 shadow-sm ring-1 ring-inset ring-gray-950/10 transition focus:ring-2 focus:ring-inset focus:ring-primary-500 dark:bg-white/5 dark:text-white dark:ring-white/10">
                        </div>

                    <?php elseif($editingModuleType === 'advantages'): ?>
                        <div class="space-y-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Переваги (до 6 елементів)</label>
                            <?php $__currentLoopData = $moduleSettings['items'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                    <div class="w-16">
                                        <label class="block text-xs text-gray-500 mb-1">Іконка</label>
                                        <input type="text" wire:model="moduleSettings.items.<?php echo e($index); ?>.icon"
                                               class="fi-input block w-full rounded-lg border-none bg-white p-1 text-center text-xl text-gray-950 shadow-sm ring-1 ring-inset ring-gray-950/10 transition focus:ring-2 focus:ring-inset focus:ring-primary-500 dark:bg-white/5 dark:text-white dark:ring-white/10">
                                    </div>
                                    <div style="flex:1 1 0%">
                                        <label class="block text-xs text-gray-500 mb-1">Заголовок</label>
                                        <input type="text" wire:model="moduleSettings.items.<?php echo e($index); ?>.title"
                                               class="fi-input block w-full rounded-lg border-none bg-white px-3 py-1.5 text-sm text-gray-950 shadow-sm ring-1 ring-inset ring-gray-950/10 transition focus:ring-2 focus:ring-inset focus:ring-primary-500 dark:bg-white/5 dark:text-white dark:ring-white/10">
                                    </div>
                                    <div style="flex:1 1 0%">
                                        <label class="block text-xs text-gray-500 mb-1">Текст</label>
                                        <input type="text" wire:model="moduleSettings.items.<?php echo e($index); ?>.text"
                                               class="fi-input block w-full rounded-lg border-none bg-white px-3 py-1.5 text-sm text-gray-950 shadow-sm ring-1 ring-inset ring-gray-950/10 transition focus:ring-2 focus:ring-inset focus:ring-primary-500 dark:bg-white/5 dark:text-white dark:ring-white/10">
                                    </div>
                                    <button wire:click="$set('moduleSettings.items', <?php echo e(json_encode(collect($moduleSettings['items'] ?? [])->forget($index)->values()->toArray())); ?>)"
                                            class="mt-5 text-danger-500 hover:text-danger-700">
                                        <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-trash'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-4 h-4']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $attributes = $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c)): ?>
<?php $component = $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c; ?>
<?php unset($__componentOriginal643fe1b47aec0b76658e1a0200b34b2c); ?>
<?php endif; ?>
                                    </button>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php if(count($moduleSettings['items'] ?? []) < 6): ?>
                                <button wire:click="$set('moduleSettings.items', <?php echo e(json_encode(array_merge($moduleSettings['items'] ?? [], [['icon' => '⭐', 'title' => '', 'text' => '']]))); ?>)"
                                        class="text-sm text-primary-600 hover:text-primary-700 font-medium">
                                    + Додати перевагу
                                </button>
                            <?php endif; ?>
                        </div>

                    <?php elseif($editingModuleType === 'newsletter'): ?>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Заголовок</label>
                            <input type="text" wire:model="moduleSettings.title"
                                   class="fi-input block w-full rounded-lg border-none bg-white px-3 py-1.5 text-sm text-gray-950 shadow-sm ring-1 ring-inset ring-gray-950/10 transition focus:ring-2 focus:ring-inset focus:ring-primary-500 dark:bg-white/5 dark:text-white dark:ring-white/10">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Опис</label>
                            <input type="text" wire:model="moduleSettings.description"
                                   class="fi-input block w-full rounded-lg border-none bg-white px-3 py-1.5 text-sm text-gray-950 shadow-sm ring-1 ring-inset ring-gray-950/10 transition focus:ring-2 focus:ring-inset focus:ring-primary-500 dark:bg-white/5 dark:text-white dark:ring-white/10">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Текст кнопки</label>
                            <input type="text" wire:model="moduleSettings.button_text"
                                   class="fi-input block w-full rounded-lg border-none bg-white px-3 py-1.5 text-sm text-gray-950 shadow-sm ring-1 ring-inset ring-gray-950/10 transition focus:ring-2 focus:ring-inset focus:ring-primary-500 dark:bg-white/5 dark:text-white dark:ring-white/10">
                        </div>

                    <?php elseif($editingModuleType === 'reviews'): ?>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Кількість відгуків</label>
                            <input type="number" wire:model="moduleSettings.limit" min="1" max="20"
                                   class="fi-input block w-full rounded-lg border-none bg-white px-3 py-1.5 text-sm text-gray-950 shadow-sm ring-1 ring-inset ring-gray-950/10 transition focus:ring-2 focus:ring-inset focus:ring-primary-500 dark:bg-white/5 dark:text-white dark:ring-white/10">
                        </div>

                    <?php elseif($editingModuleType === 'hero_slider'): ?>
                        <div class="space-y-4">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Налаштування слайдів. Кожен слайд — JSON-об'єкт з полями: subtitle, title, description, button_text, button_url, bg_color, text_color.</p>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Кількість слайдів: <?php echo e(count($moduleSettings['slides'] ?? [])); ?></label>
                                <textarea wire:model="moduleSettings.slides_json" rows="10"
                                    class="fi-input block w-full rounded-lg border-none bg-white px-3 py-1.5 font-mono text-sm text-gray-950 shadow-sm ring-1 ring-inset ring-gray-950/10 transition focus:ring-2 focus:ring-inset focus:ring-primary-500 dark:bg-white/5 dark:text-white dark:ring-white/10"
                                    placeholder='[{"subtitle":"АКЦІЯ","title":"ЗНИЖКИ","description":"...","button_text":"ДИВИТИСЬ","button_url":"/specials","bg_color":"#000","text_color":"#fff"}]'></textarea>
                            </div>
                            <div style="display:grid;gap:1rem;grid-template-columns:repeat(auto-fit,minmax(200px,1fr))">
                                <label class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                    <input type="checkbox" wire:model="moduleSettings.autoplay"
                                        class="fi-checkbox-input rounded border-none bg-white text-primary-600 shadow-sm ring-1 ring-gray-950/10 checked:ring-0 focus:ring-primary-500 dark:bg-white/5 dark:ring-white/20">
                                    Автопрокрутка
                                </label>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Інтервал (мс)</label>
                                    <input type="number" wire:model="moduleSettings.interval" min="1000" max="30000" step="500"
                                        class="fi-input block w-full rounded-lg border-none bg-white px-3 py-1.5 text-sm text-gray-950 shadow-sm ring-1 ring-inset ring-gray-950/10 transition focus:ring-2 focus:ring-inset focus:ring-primary-500 dark:bg-white/5 dark:text-white dark:ring-white/10">
                                </div>
                            </div>
                        </div>

                    <?php elseif($editingModuleType === 'countdown'): ?>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Дата закінчення</label>
                            <input type="datetime-local" wire:model="moduleSettings.end_date"
                                   class="fi-input block w-full rounded-lg border-none bg-white px-3 py-1.5 text-sm text-gray-950 shadow-sm ring-1 ring-inset ring-gray-950/10 transition focus:ring-2 focus:ring-inset focus:ring-primary-500 dark:bg-white/5 dark:text-white dark:ring-white/10">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Заголовок</label>
                            <input type="text" wire:model="moduleSettings.title"
                                   class="fi-input block w-full rounded-lg border-none bg-white px-3 py-1.5 text-sm text-gray-950 shadow-sm ring-1 ring-inset ring-gray-950/10 transition focus:ring-2 focus:ring-inset focus:ring-primary-500 dark:bg-white/5 dark:text-white dark:ring-white/10">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Опис</label>
                            <input type="text" wire:model="moduleSettings.description"
                                   class="fi-input block w-full rounded-lg border-none bg-white px-3 py-1.5 text-sm text-gray-950 shadow-sm ring-1 ring-inset ring-gray-950/10 transition focus:ring-2 focus:ring-inset focus:ring-primary-500 dark:bg-white/5 dark:text-white dark:ring-white/10">
                        </div>
                    <?php endif; ?>
                </div>

                
                <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <?php if (isset($component)) { $__componentOriginal6330f08526bbb3ce2a0da37da512a11f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['color' => 'gray','wire:click' => 'closeEditModal']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'gray','wire:click' => 'closeEditModal']); ?>
                        Скасувати
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => 'saveModuleSettings']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'saveModuleSettings']); ?>
                        Зберегти
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
        </div>
    </div>
    <?php endif; ?>
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
<?php /**PATH /home/lionex/projects/gazu-shop/resources/views/filament/pages/homepage-builder.blade.php ENDPATH**/ ?>