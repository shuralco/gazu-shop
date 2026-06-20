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

        
        <div class="flex gap-1 border-b border-gray-200 dark:border-white/10">
            <button
                wire:click="$set('activeTab', 'products')"
                class="px-4 py-2 text-sm font-medium rounded-t-lg transition <?php echo e($activeTab === 'products' ? 'bg-primary-500 text-white' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5'); ?>"
            >
                ТОВАРИ
            </button>
            <button
                wire:click="$set('activeTab', 'categories')"
                class="px-4 py-2 text-sm font-medium rounded-t-lg transition <?php echo e($activeTab === 'categories' ? 'bg-primary-500 text-white' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5'); ?>"
            >
                КАТЕГОРІЇ
            </button>
            <button
                wire:click="$set('activeTab', 'orders')"
                class="px-4 py-2 text-sm font-medium rounded-t-lg transition <?php echo e($activeTab === 'orders' ? 'bg-primary-500 text-white' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5'); ?>"
            >
                ЗАМОВЛЕННЯ
            </button>
            <button
                wire:click="$set('activeTab', 'reviews')"
                class="px-4 py-2 text-sm font-medium rounded-t-lg transition <?php echo e($activeTab === 'reviews' ? 'bg-primary-500 text-white' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5'); ?>"
            >
                ВІДГУКИ
            </button>
            <button
                wire:click="$set('activeTab', 'journal')"
                class="px-4 py-2 text-sm font-medium rounded-t-lg transition <?php echo e($activeTab === 'journal' ? 'bg-primary-500 text-white' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5'); ?>"
            >
                ЖУРНАЛ
            </button>
        </div>

        
        <?php if($activeTab === 'products'): ?>

        
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-3 mb-3">
            <div class="flex flex-wrap items-end gap-2">
                <select wire:model.live="filterCategory" class="text-xs rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-2 py-1.5 min-w-[120px]">
                    <option value="">Категорія: всі</option>
                    <?php $__currentLoopData = $this->getCategories(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $title): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($id); ?>"><?php echo e($title); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <select wire:model.live="filterBrand" class="text-xs rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-2 py-1.5 min-w-[100px]">
                    <option value="">Бренд: всі</option>
                    <?php $__currentLoopData = $this->getBrands(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($id); ?>"><?php echo e($name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <select wire:model.live="filterStatus" class="text-xs rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-2 py-1.5 min-w-[100px]">
                    <option value="">Статус: всі</option>
                    <option value="active">Активні</option>
                    <option value="inactive">Неактивні</option>
                    <option value="hit">Хіти</option>
                    <option value="new">Новинки</option>
                    <option value="sale">Акційні</option>
                </select>
                <select wire:model.live="filterStockStatus" class="text-xs rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-2 py-1.5 min-w-[100px]">
                    <option value="">Наявність: всі</option>
                    <option value="in_stock">В наявності</option>
                    <option value="out_of_stock">Немає</option>
                    <option value="preorder">Предзамовлення</option>
                </select>
                <input type="text" wire:model.live.debounce.500ms="filterManufacturer" placeholder="Виробник..." class="text-xs rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-2 py-1.5 w-28">
                <input type="number" wire:model.live.debounce.500ms="filterPriceFrom" placeholder="Ціна від" class="text-xs rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-2 py-1.5 w-20">
                <input type="number" wire:model.live.debounce.500ms="filterPriceTo" placeholder="до" class="text-xs rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-2 py-1.5 w-20">
                <input type="text" wire:model.live.debounce.500ms="filterSearch" placeholder="🔍 Пошук..." class="text-xs rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-2 py-1.5 w-36">
                <button wire:click="$toggle('showAdvancedFilters')" class="text-xs text-primary-600 dark:text-primary-400 font-medium px-2 py-1.5 hover:underline">
                    <?php echo e($showAdvancedFilters ? '▲ Менше' : '▼ Більше'); ?>

                </button>
                <button wire:click="resetFilters" class="text-xs text-danger-600 dark:text-danger-400 font-medium px-2 py-1.5 hover:underline">× Скинути</button>
            </div>
            <div x-data="{ show: <?php if ((object) ('showAdvancedFilters') instanceof \Livewire\WireDirective) : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('showAdvancedFilters'->value()); ?>')<?php echo e('showAdvancedFilters'->hasModifier('live') ? '.live' : ''); ?><?php else : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('showAdvancedFilters'); ?>')<?php endif; ?> }" x-show="show" x-cloak x-transition
                 class="flex flex-wrap items-center gap-2 mt-2 pt-2 border-t border-gray-200 dark:border-gray-700">
                <label class="flex items-center gap-2 text-xs text-gray-700 dark:text-gray-300">
                    <input type="checkbox" wire:model.live="filterNoImage" class="fi-checkbox-input rounded text-primary-600"> Без фото
                </label>
                <label class="flex items-center gap-2 text-xs text-gray-700 dark:text-gray-300">
                    <input type="checkbox" wire:model.live="filterNoDescription" class="fi-checkbox-input rounded text-primary-600"> Без опису
                </label>
                <label class="flex items-center gap-2 text-xs text-gray-700 dark:text-gray-300">
                    <input type="checkbox" wire:model.live="filterNoSeo" class="fi-checkbox-input rounded text-primary-600"> Без SEO
                </label>
                <label class="flex items-center gap-2 text-xs text-gray-700 dark:text-gray-300">
                    <input type="checkbox" wire:model.live="filterHasVariants" class="fi-checkbox-input rounded text-primary-600"> З варіантами
                </label>
                <label class="flex items-center gap-2 text-xs text-gray-700 dark:text-gray-300">
                    <input type="checkbox" wire:model.live="filterHasGroupPrice" class="fi-checkbox-input rounded text-primary-600"> З гуртовою
                </label>
                <input type="number" wire:model.live.debounce.500ms="filterQtyFrom" placeholder="К-сть від" class="text-xs rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-2 py-1.5 w-20">
                <input type="number" wire:model.live.debounce.500ms="filterQtyTo" placeholder="К-сть до" class="text-xs rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-2 py-1.5 w-20">
                <input type="date" wire:model.live="filterDateFrom" class="text-xs rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-2 py-1.5">
                <input type="date" wire:model.live="filterDateTo" class="text-xs rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-2 py-1.5">
            </div>
        </div>

        
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 p-3 mb-4">
            
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-3">
                    <?php if (isset($component)) { $__componentOriginal6330f08526bbb3ce2a0da37da512a11f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => 'saveChanges','color' => 'success','icon' => 'heroicon-m-check','size' => 'sm','badge' => count($editedData) > 0 ? count($editedData) : null]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'saveChanges','color' => 'success','icon' => 'heroicon-m-check','size' => 'sm','badge' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(count($editedData) > 0 ? count($editedData) : null)]); ?>
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
                    <?php if(count($selectedIds) > 0): ?>
                    <span class="text-xs text-gray-500 dark:text-gray-400">Вибрано: <strong class="text-primary-600 dark:text-primary-400"><?php echo e(count($selectedIds)); ?></strong></span>
                    <?php endif; ?>
                </div>

                
                <div x-data="{ open: false }" class="relative">
                    <?php if (isset($component)) { $__componentOriginal6330f08526bbb3ce2a0da37da512a11f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['@click' => 'open = !open','color' => 'gray','icon' => 'heroicon-m-view-columns','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['@click' => 'open = !open','color' => 'gray','icon' => 'heroicon-m-view-columns','size' => 'sm']); ?>
                        Колонки
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
                    <div x-show="open" @click.away="open = false" x-cloak x-transition
                         class="absolute right-0 mt-1 w-52 bg-white dark:bg-gray-800 rounded-xl shadow-lg ring-1 ring-gray-950/5 dark:ring-white/10 p-2 z-50 max-h-80 overflow-y-auto">
                        <?php $__currentLoopData = $this->getAvailableColumns(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <label class="flex items-center gap-2 px-2 py-1.5 text-xs hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg cursor-pointer transition">
                            <input type="checkbox" value="<?php echo e($key); ?>" wire:model.live="visibleColumns" class="fi-checkbox-input rounded text-primary-600">
                            <?php echo e($label); ?>

                        </label>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>

            
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1" style="-ms-overflow-style: none; scrollbar-width: thin;">
                <span class="text-xs text-gray-400 dark:text-gray-500 font-medium flex-shrink-0 mr-1">ДII:</span>

                
                <?php if (isset($component)) { $__componentOriginal6330f08526bbb3ce2a0da37da512a11f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => '$set(\'showPriceModal\', true)','color' => 'primary','icon' => 'heroicon-m-currency-dollar','size' => 'xs','class' => 'flex-shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => '$set(\'showPriceModal\', true)','color' => 'primary','icon' => 'heroicon-m-currency-dollar','size' => 'xs','class' => 'flex-shrink-0']); ?>Ціна <?php echo $__env->renderComponent(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => '$set(\'showSaleModal\', true)','color' => 'warning','icon' => 'heroicon-m-tag','size' => 'xs','class' => 'flex-shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => '$set(\'showSaleModal\', true)','color' => 'warning','icon' => 'heroicon-m-tag','size' => 'xs','class' => 'flex-shrink-0']); ?>Акція <?php echo $__env->renderComponent(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => 'removeSale','wire:confirm' => 'Зняти акцію з вибраних товарів?','color' => 'gray','icon' => 'heroicon-m-arrow-uturn-left','size' => 'xs','class' => 'flex-shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'removeSale','wire:confirm' => 'Зняти акцію з вибраних товарів?','color' => 'gray','icon' => 'heroicon-m-arrow-uturn-left','size' => 'xs','class' => 'flex-shrink-0']); ?>Зняти <?php echo $__env->renderComponent(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => '$set(\'showGroupPriceModal\', true)','color' => 'success','icon' => 'heroicon-m-user-group','size' => 'xs','class' => 'flex-shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => '$set(\'showGroupPriceModal\', true)','color' => 'success','icon' => 'heroicon-m-user-group','size' => 'xs','class' => 'flex-shrink-0']); ?>Гуртові <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6330f08526bbb3ce2a0da37da512a11f)): ?>
<?php $attributes = $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f; ?>
<?php unset($__attributesOriginal6330f08526bbb3ce2a0da37da512a11f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6330f08526bbb3ce2a0da37da512a11f)): ?>
<?php $component = $__componentOriginal6330f08526bbb3ce2a0da37da512a11f; ?>
<?php unset($__componentOriginal6330f08526bbb3ce2a0da37da512a11f); ?>
<?php endif; ?>

                <span class="w-px h-5 bg-gray-200 dark:bg-gray-700 flex-shrink-0"></span>

                
                <?php if (isset($component)) { $__componentOriginal6330f08526bbb3ce2a0da37da512a11f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => '$set(\'showStatusModal\', true)','color' => 'gray','icon' => 'heroicon-m-eye','size' => 'xs','class' => 'flex-shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => '$set(\'showStatusModal\', true)','color' => 'gray','icon' => 'heroicon-m-eye','size' => 'xs','class' => 'flex-shrink-0']); ?>Статус <?php echo $__env->renderComponent(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => '$set(\'showCategoryModal\', true)','color' => 'gray','icon' => 'heroicon-m-folder','size' => 'xs','class' => 'flex-shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => '$set(\'showCategoryModal\', true)','color' => 'gray','icon' => 'heroicon-m-folder','size' => 'xs','class' => 'flex-shrink-0']); ?>Категорія <?php echo $__env->renderComponent(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => '$set(\'showBrandModal\', true)','color' => 'gray','icon' => 'heroicon-m-building-storefront','size' => 'xs','class' => 'flex-shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => '$set(\'showBrandModal\', true)','color' => 'gray','icon' => 'heroicon-m-building-storefront','size' => 'xs','class' => 'flex-shrink-0']); ?>Бренд <?php echo $__env->renderComponent(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => '$set(\'showFilterModal\', true)','color' => 'gray','icon' => 'heroicon-m-funnel','size' => 'xs','class' => 'flex-shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => '$set(\'showFilterModal\', true)','color' => 'gray','icon' => 'heroicon-m-funnel','size' => 'xs','class' => 'flex-shrink-0']); ?>Фільтри <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6330f08526bbb3ce2a0da37da512a11f)): ?>
<?php $attributes = $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f; ?>
<?php unset($__attributesOriginal6330f08526bbb3ce2a0da37da512a11f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6330f08526bbb3ce2a0da37da512a11f)): ?>
<?php $component = $__componentOriginal6330f08526bbb3ce2a0da37da512a11f; ?>
<?php unset($__componentOriginal6330f08526bbb3ce2a0da37da512a11f); ?>
<?php endif; ?>

                <span class="w-px h-5 bg-gray-200 dark:bg-gray-700 flex-shrink-0"></span>

                
                <?php if (isset($component)) { $__componentOriginal6330f08526bbb3ce2a0da37da512a11f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => '$set(\'showSeoModal\', true)','color' => 'info','icon' => 'heroicon-m-magnifying-glass','size' => 'xs','class' => 'flex-shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => '$set(\'showSeoModal\', true)','color' => 'info','icon' => 'heroicon-m-magnifying-glass','size' => 'xs','class' => 'flex-shrink-0']); ?>SEO <?php echo $__env->renderComponent(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => '$set(\'showSearchReplaceModal\', true)','color' => 'gray','icon' => 'heroicon-m-magnifying-glass','size' => 'xs','class' => 'flex-shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => '$set(\'showSearchReplaceModal\', true)','color' => 'gray','icon' => 'heroicon-m-magnifying-glass','size' => 'xs','class' => 'flex-shrink-0']); ?>Пошук <?php echo $__env->renderComponent(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => '$set(\'showWeightModal\', true)','color' => 'gray','icon' => 'heroicon-m-scale','size' => 'xs','class' => 'flex-shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => '$set(\'showWeightModal\', true)','color' => 'gray','icon' => 'heroicon-m-scale','size' => 'xs','class' => 'flex-shrink-0']); ?>Вага <?php echo $__env->renderComponent(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => 'duplicateSelected','wire:confirm' => 'Дублювати вибрані товари?','color' => 'gray','icon' => 'heroicon-m-document-duplicate','size' => 'xs','class' => 'flex-shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'duplicateSelected','wire:confirm' => 'Дублювати вибрані товари?','color' => 'gray','icon' => 'heroicon-m-document-duplicate','size' => 'xs','class' => 'flex-shrink-0']); ?>Копія <?php echo $__env->renderComponent(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => 'openVariantModal','color' => 'info','icon' => 'heroicon-m-squares-plus','size' => 'xs','class' => 'flex-shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'openVariantModal','color' => 'info','icon' => 'heroicon-m-squares-plus','size' => 'xs','class' => 'flex-shrink-0']); ?>Варіанти <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6330f08526bbb3ce2a0da37da512a11f)): ?>
<?php $attributes = $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f; ?>
<?php unset($__attributesOriginal6330f08526bbb3ce2a0da37da512a11f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6330f08526bbb3ce2a0da37da512a11f)): ?>
<?php $component = $__componentOriginal6330f08526bbb3ce2a0da37da512a11f; ?>
<?php unset($__componentOriginal6330f08526bbb3ce2a0da37da512a11f); ?>
<?php endif; ?>

                <span class="w-px h-5 bg-gray-200 dark:bg-gray-700 flex-shrink-0"></span>

                
                <?php if (isset($component)) { $__componentOriginal6330f08526bbb3ce2a0da37da512a11f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => 'openImportModal','color' => 'info','icon' => 'heroicon-m-arrow-up-tray','size' => 'xs','class' => 'flex-shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'openImportModal','color' => 'info','icon' => 'heroicon-m-arrow-up-tray','size' => 'xs','class' => 'flex-shrink-0']); ?>Імпорт <?php echo $__env->renderComponent(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => 'exportSelected','color' => 'gray','icon' => 'heroicon-m-arrow-down-tray','size' => 'xs','class' => 'flex-shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'exportSelected','color' => 'gray','icon' => 'heroicon-m-arrow-down-tray','size' => 'xs','class' => 'flex-shrink-0']); ?>Експорт <?php echo $__env->renderComponent(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => 'deleteSelected','wire:confirm' => 'Видалити вибрані товари? Цю дію не можна скасувати!','color' => 'danger','icon' => 'heroicon-m-trash','size' => 'xs','class' => 'flex-shrink-0']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'deleteSelected','wire:confirm' => 'Видалити вибрані товари? Цю дію не можна скасувати!','color' => 'danger','icon' => 'heroicon-m-trash','size' => 'xs','class' => 'flex-shrink-0']); ?>Видалити <?php echo $__env->renderComponent(); ?>
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

        
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="fi-ta-table w-full table-auto divide-y divide-gray-200 dark:divide-white/5 text-start">
                    <thead class="bg-gray-50 dark:bg-white/5">
                        <tr>
                            <th class="fi-ta-header-cell px-3 py-2.5 w-10">
                                <input type="checkbox" wire:model.live="selectAll" wire:click="toggleSelectAll" class="fi-checkbox-input rounded border-gray-300 text-primary-600 dark:border-white/10 dark:bg-white/5">
                            </th>
                            <?php if(in_array('id', $visibleColumns)): ?><th class="fi-ta-header-cell px-3 py-2.5 text-xs font-medium text-gray-600 dark:text-gray-400 text-start w-16">ID</th><?php endif; ?>
                            <?php if(in_array('title', $visibleColumns)): ?><th class="fi-ta-header-cell px-3 py-2.5 text-xs font-medium text-gray-600 dark:text-gray-400 text-start min-w-[200px]">Назва</th><?php endif; ?>
                            <?php if(in_array('sku', $visibleColumns)): ?><th class="fi-ta-header-cell px-3 py-2.5 text-xs font-medium text-gray-600 dark:text-gray-400 text-start w-28">SKU</th><?php endif; ?>
                            <?php if(in_array('price', $visibleColumns)): ?><th class="fi-ta-header-cell px-3 py-2.5 text-xs font-medium text-gray-600 dark:text-gray-400 text-start w-28">Ціна</th><?php endif; ?>
                            <?php if(in_array('old_price', $visibleColumns)): ?><th class="fi-ta-header-cell px-3 py-2.5 text-xs font-medium text-gray-600 dark:text-gray-400 text-start w-28">Стара ціна</th><?php endif; ?>
                            <?php if(in_array('quantity', $visibleColumns)): ?><th class="fi-ta-header-cell px-3 py-2.5 text-xs font-medium text-gray-600 dark:text-gray-400 text-start w-20">К-сть</th><?php endif; ?>
                            <?php if(in_array('stock_status', $visibleColumns)): ?><th class="fi-ta-header-cell px-3 py-2.5 text-xs font-medium text-gray-600 dark:text-gray-400 text-start w-36">Наявність</th><?php endif; ?>
                            <?php if(in_array('is_active', $visibleColumns)): ?><th class="fi-ta-header-cell px-3 py-2.5 text-xs font-medium text-gray-600 dark:text-gray-400 text-center w-14">Акт</th><?php endif; ?>
                            <?php if(in_array('is_hit', $visibleColumns)): ?><th class="fi-ta-header-cell px-3 py-2.5 text-xs font-medium text-gray-600 dark:text-gray-400 text-center w-14">Хіт</th><?php endif; ?>
                            <?php if(in_array('is_new', $visibleColumns)): ?><th class="fi-ta-header-cell px-3 py-2.5 text-xs font-medium text-gray-600 dark:text-gray-400 text-center w-14">Нов</th><?php endif; ?>
                            <?php if(in_array('category', $visibleColumns)): ?><th class="fi-ta-header-cell px-3 py-2.5 text-xs font-medium text-gray-600 dark:text-gray-400 text-start w-40">Категорія</th><?php endif; ?>
                            <?php if(in_array('brand', $visibleColumns)): ?><th class="fi-ta-header-cell px-3 py-2.5 text-xs font-medium text-gray-600 dark:text-gray-400 text-start w-36">Бренд</th><?php endif; ?>
                            <?php if(in_array('manufacturer', $visibleColumns)): ?><th class="fi-ta-header-cell px-3 py-2.5 text-xs font-medium text-gray-600 dark:text-gray-400 text-start w-32">Виробник</th><?php endif; ?>
                            <?php if(in_array('weight', $visibleColumns)): ?><th class="fi-ta-header-cell px-3 py-2.5 text-xs font-medium text-gray-600 dark:text-gray-400 text-start w-20">Вага</th><?php endif; ?>
                            <?php if(in_array('rating', $visibleColumns)): ?><th class="fi-ta-header-cell px-3 py-2.5 text-xs font-medium text-gray-600 dark:text-gray-400 text-start w-16">&#9733;</th><?php endif; ?>
                            <?php if(in_array('reviews_count', $visibleColumns)): ?><th class="fi-ta-header-cell px-3 py-2.5 text-xs font-medium text-gray-600 dark:text-gray-400 text-start w-16">Відг</th><?php endif; ?>
                            <?php if(in_array('created_at', $visibleColumns)): ?><th class="fi-ta-header-cell px-3 py-2.5 text-xs font-medium text-gray-600 dark:text-gray-400 text-start w-28">Створено</th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                        <?php $__empty_1 = true; $__currentLoopData = $this->getProducts(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition <?php echo e(isset($editedData[$product->id]) ? 'bg-warning-50/50 dark:bg-warning-400/10' : ''); ?>" wire:key="product-<?php echo e($product->id); ?>">
                            <td class="px-3 py-1.5 text-center">
                                <input type="checkbox" value="<?php echo e($product->id); ?>" wire:model.live="selectedIds" class="fi-checkbox-input rounded border-gray-300 text-primary-600 dark:border-white/10 dark:bg-white/5">
                            </td>

                            <?php if(in_array('id', $visibleColumns)): ?>
                            <td class="px-3 py-1.5 text-xs text-gray-500 dark:text-gray-400 font-mono"><?php echo e($product->id); ?></td>
                            <?php endif; ?>

                            <?php if(in_array('title', $visibleColumns)): ?>
                            <td class="px-3 py-1.5 relative group/title">
                                <input type="text" value="<?php echo e($editedData[$product->id]['title'] ?? $product->title); ?>"
                                    wire:change="updateField(<?php echo e($product->id); ?>, 'title', $event.target.value)"
                                    class="fi-input w-full text-sm border-0 bg-transparent px-1.5 py-1 focus:ring-1 focus:ring-primary-500 rounded transition <?php echo e(isset($editedData[$product->id]['title']) ? 'bg-warning-100 dark:bg-warning-400/20 ring-1 ring-warning-400/50' : 'hover:bg-gray-50 dark:hover:bg-white/5'); ?>">
                                <?php if($product->image): ?>
                                <div class="hidden group-hover/title:block absolute z-50 left-0 top-full mt-1 w-32 h-32 bg-white dark:bg-gray-800 rounded-lg shadow-xl ring-1 ring-gray-200 dark:ring-white/10 p-1">
                                    <img src="<?php echo e(asset($product->getImage())); ?>" alt="" class="w-full h-full object-contain">
                                </div>
                                <?php endif; ?>
                            </td>
                            <?php endif; ?>

                            <?php if(in_array('sku', $visibleColumns)): ?>
                            <td class="px-3 py-1.5">
                                <input type="text" value="<?php echo e($editedData[$product->id]['sku'] ?? $product->sku); ?>"
                                    wire:change="updateField(<?php echo e($product->id); ?>, 'sku', $event.target.value)"
                                    class="fi-input w-full text-xs font-mono border-0 bg-transparent px-1.5 py-1 focus:ring-1 focus:ring-primary-500 rounded transition <?php echo e(isset($editedData[$product->id]['sku']) ? 'bg-warning-100 dark:bg-warning-400/20 ring-1 ring-warning-400/50' : 'hover:bg-gray-50 dark:hover:bg-white/5'); ?>">
                            </td>
                            <?php endif; ?>

                            <?php if(in_array('price', $visibleColumns)): ?>
                            <td class="px-3 py-1.5">
                                <input type="number" step="0.01" value="<?php echo e($editedData[$product->id]['price'] ?? $product->price); ?>"
                                    wire:change="updateField(<?php echo e($product->id); ?>, 'price', $event.target.value)"
                                    class="fi-input w-full text-sm border-0 bg-transparent px-1.5 py-1 focus:ring-1 focus:ring-primary-500 rounded text-right font-medium transition <?php echo e(isset($editedData[$product->id]['price']) ? 'bg-warning-100 dark:bg-warning-400/20 ring-1 ring-warning-400/50' : 'hover:bg-gray-50 dark:hover:bg-white/5'); ?>">
                            </td>
                            <?php endif; ?>

                            <?php if(in_array('old_price', $visibleColumns)): ?>
                            <td class="px-3 py-1.5">
                                <input type="number" step="0.01" value="<?php echo e($editedData[$product->id]['old_price'] ?? $product->old_price); ?>"
                                    wire:change="updateField(<?php echo e($product->id); ?>, 'old_price', $event.target.value)"
                                    class="fi-input w-full text-xs border-0 bg-transparent px-1.5 py-1 focus:ring-1 focus:ring-primary-500 rounded text-right transition <?php echo e(isset($editedData[$product->id]['old_price']) ? 'bg-warning-100 dark:bg-warning-400/20 ring-1 ring-warning-400/50' : 'hover:bg-gray-50 dark:hover:bg-white/5'); ?>">
                            </td>
                            <?php endif; ?>

                            <?php if(in_array('quantity', $visibleColumns)): ?>
                            <td class="px-3 py-1.5">
                                <input type="number" value="<?php echo e($editedData[$product->id]['quantity'] ?? $product->quantity); ?>"
                                    wire:change="updateField(<?php echo e($product->id); ?>, 'quantity', $event.target.value)"
                                    class="fi-input w-20 text-sm border-0 bg-transparent px-1.5 py-1 focus:ring-1 focus:ring-primary-500 rounded text-center transition <?php echo e(isset($editedData[$product->id]['quantity']) ? 'bg-warning-100 dark:bg-warning-400/20 ring-1 ring-warning-400/50' : 'hover:bg-gray-50 dark:hover:bg-white/5'); ?>">
                            </td>
                            <?php endif; ?>

                            <?php if(in_array('stock_status', $visibleColumns)): ?>
                            <td class="px-3 py-1.5">
                                <?php
                                    $stockVal = $editedData[$product->id]['stock_status'] ?? $product->stock_status ?? \App\Models\StockStatus::defaultKey();
                                    $stockOptions = \App\Models\StockStatus::options();
                                ?>
                                <select wire:change="updateField(<?php echo e($product->id); ?>, 'stock_status', $event.target.value)"
                                    class="text-xs w-full py-1 px-1.5 rounded bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-200 border border-gray-200 dark:border-gray-600 <?php echo e(isset($editedData[$product->id]['stock_status']) ? 'bg-warning-100 dark:bg-warning-400/20 border-warning-400' : ''); ?>">
                                    <?php $__currentLoopData = $stockOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($key); ?>" <?php echo e($stockVal === $key ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </td>
                            <?php endif; ?>

                            <?php if(in_array('is_active', $visibleColumns)): ?>
                            <td class="px-3 py-1.5 text-center">
                                <input type="checkbox" <?php echo e(($editedData[$product->id]['is_active'] ?? $product->is_active) ? 'checked' : ''); ?>

                                    wire:change="updateField(<?php echo e($product->id); ?>, 'is_active', $event.target.checked)"
                                    class="fi-checkbox-input rounded border-gray-300 text-primary-600 dark:border-white/10 dark:bg-white/5 <?php echo e(isset($editedData[$product->id]['is_active']) ? 'ring-2 ring-warning-400' : ''); ?>">
                            </td>
                            <?php endif; ?>

                            <?php if(in_array('is_hit', $visibleColumns)): ?>
                            <td class="px-3 py-1.5 text-center">
                                <input type="checkbox" <?php echo e(($editedData[$product->id]['is_hit'] ?? $product->is_hit) ? 'checked' : ''); ?>

                                    wire:change="updateField(<?php echo e($product->id); ?>, 'is_hit', $event.target.checked)"
                                    class="fi-checkbox-input rounded border-gray-300 text-primary-600 dark:border-white/10 dark:bg-white/5 <?php echo e(isset($editedData[$product->id]['is_hit']) ? 'ring-2 ring-warning-400' : ''); ?>">
                            </td>
                            <?php endif; ?>

                            <?php if(in_array('is_new', $visibleColumns)): ?>
                            <td class="px-3 py-1.5 text-center">
                                <input type="checkbox" <?php echo e(($editedData[$product->id]['is_new'] ?? $product->is_new) ? 'checked' : ''); ?>

                                    wire:change="updateField(<?php echo e($product->id); ?>, 'is_new', $event.target.checked)"
                                    class="fi-checkbox-input rounded border-gray-300 text-primary-600 dark:border-white/10 dark:bg-white/5 <?php echo e(isset($editedData[$product->id]['is_new']) ? 'ring-2 ring-warning-400' : ''); ?>">
                            </td>
                            <?php endif; ?>

                            <?php if(in_array('category', $visibleColumns)): ?>
                            <td class="px-3 py-1.5">
                                <?php $catCurrent = $editedData[$product->id]['category_id'] ?? $product->category_id; ?>
                                <div x-data="{
                                    open: false, search: '', selected: '<?php echo e($catCurrent); ?>',
                                    label: '<?php echo e(addslashes($this->getCategories()[$catCurrent] ?? '—')); ?>',
                                    items: <?php echo e(json_encode(collect($this->getCategories())->map(fn($t,$i) => ['id'=>$i,'title'=>$t])->values())); ?>,
                                    get filtered() { return this.search ? this.items.filter(i => i.title.toLowerCase().includes(this.search.toLowerCase())) : this.items; },
                                    pick(item) { this.selected = item.id; this.label = item.title; this.open = false; this.search = '';
                                        $wire.updateField(<?php echo e($product->id); ?>, 'category_id', item.id); }
                                }" class="relative">
                                    <button @click="open = !open" type="button"
                                        class="w-full text-left text-xs px-1.5 py-1 rounded truncate max-w-[140px] <?php echo e(isset($editedData[$product->id]['category_id']) ? 'bg-warning-100 dark:bg-warning-400/20 ring-1 ring-warning-400/50' : 'hover:bg-gray-100 dark:hover:bg-white/10'); ?>"
                                        x-text="label"></button>
                                    <div x-show="open" @click.away="open = false; search = ''" x-cloak x-transition
                                         class="absolute z-50 mt-1 w-56 bg-white dark:bg-gray-800 rounded-lg shadow-lg ring-1 ring-gray-900/10 dark:ring-white/10 overflow-hidden">
                                        <input x-model="search" placeholder="Пошук..." class="w-full px-3 py-2 text-xs border-b border-gray-200 dark:border-gray-700 bg-transparent dark:text-white outline-none">
                                        <div class="max-h-48 overflow-y-auto">
                                            <template x-for="item in filtered" :key="item.id">
                                                <button @click="pick(item)" type="button"
                                                    class="block w-full text-left px-3 py-1.5 text-xs hover:bg-primary-50 dark:hover:bg-primary-900/20 dark:text-gray-200"
                                                    :class="selected == item.id ? 'bg-primary-50 dark:bg-primary-900/30 font-bold' : ''"
                                                    x-text="item.title"></button>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <?php endif; ?>

                            <?php if(in_array('brand', $visibleColumns)): ?>
                            <td class="px-3 py-1.5">
                                <?php $brandCurrent = $editedData[$product->id]['brand_id'] ?? $product->brand_id; ?>
                                <div x-data="{
                                    open: false, search: '', selected: '<?php echo e($brandCurrent); ?>',
                                    label: '<?php echo e(addslashes($this->getBrands()[$brandCurrent] ?? '—')); ?>',
                                    items: <?php echo e(json_encode(collect($this->getBrands())->map(fn($n,$i) => ['id'=>$i,'name'=>$n])->values())); ?>,
                                    get filtered() { return this.search ? this.items.filter(i => i.name.toLowerCase().includes(this.search.toLowerCase())) : this.items; },
                                    pick(item) { this.selected = item.id; this.label = item.name; this.open = false; this.search = '';
                                        $wire.updateField(<?php echo e($product->id); ?>, 'brand_id', item.id); }
                                }" class="relative">
                                    <button @click="open = !open" type="button"
                                        class="w-full text-left text-xs px-1.5 py-1 rounded truncate max-w-[120px] <?php echo e(isset($editedData[$product->id]['brand_id']) ? 'bg-warning-100 dark:bg-warning-400/20 ring-1 ring-warning-400/50' : 'hover:bg-gray-100 dark:hover:bg-white/10'); ?>"
                                        x-text="label || '—'"></button>
                                    <div x-show="open" @click.away="open = false; search = ''" x-cloak x-transition
                                         class="absolute z-50 mt-1 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg ring-1 ring-gray-900/10 dark:ring-white/10 overflow-hidden">
                                        <input x-model="search" placeholder="Пошук..." class="w-full px-3 py-2 text-xs border-b border-gray-200 dark:border-gray-700 bg-transparent dark:text-white outline-none">
                                        <div class="max-h-48 overflow-y-auto">
                                            <button @click="pick({id:'',name:'—'})" type="button" class="block w-full text-left px-3 py-1.5 text-xs hover:bg-gray-100 dark:hover:bg-white/10 dark:text-gray-400">—</button>
                                            <template x-for="item in filtered" :key="item.id">
                                                <button @click="pick(item)" type="button"
                                                    class="block w-full text-left px-3 py-1.5 text-xs hover:bg-primary-50 dark:hover:bg-primary-900/20 dark:text-gray-200"
                                                    :class="selected == item.id ? 'bg-primary-50 dark:bg-primary-900/30 font-bold' : ''"
                                                    x-text="item.name"></button>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <?php endif; ?>

                            <?php if(in_array('manufacturer', $visibleColumns)): ?>
                            <td class="px-3 py-1.5">
                                <input type="text" value="<?php echo e($editedData[$product->id]['manufacturer'] ?? $product->manufacturer); ?>"
                                    wire:change="updateField(<?php echo e($product->id); ?>, 'manufacturer', $event.target.value)"
                                    class="fi-input text-xs border-0 bg-transparent w-full px-1.5 py-1 rounded transition <?php echo e(isset($editedData[$product->id]['manufacturer']) ? 'bg-warning-100 dark:bg-warning-400/20 ring-1 ring-warning-400/50' : 'hover:bg-gray-50 dark:hover:bg-white/5'); ?>">
                            </td>
                            <?php endif; ?>

                            <?php if(in_array('weight', $visibleColumns)): ?>
                            <td class="px-3 py-1.5">
                                <input type="number" step="0.001" value="<?php echo e($editedData[$product->id]['weight'] ?? $product->weight); ?>"
                                    wire:change="updateField(<?php echo e($product->id); ?>, 'weight', $event.target.value)"
                                    class="fi-input w-20 text-xs border-0 bg-transparent px-1.5 py-1 text-right rounded transition <?php echo e(isset($editedData[$product->id]['weight']) ? 'bg-warning-100 dark:bg-warning-400/20 ring-1 ring-warning-400/50' : 'hover:bg-gray-50 dark:hover:bg-white/5'); ?>">
                            </td>
                            <?php endif; ?>

                            <?php if(in_array('rating', $visibleColumns)): ?>
                            <td class="px-3 py-1.5 text-xs text-gray-500 dark:text-gray-400"><?php echo e(number_format($product->rating ?? 0, 1)); ?></td>
                            <?php endif; ?>

                            <?php if(in_array('reviews_count', $visibleColumns)): ?>
                            <td class="px-3 py-1.5 text-xs text-gray-500 dark:text-gray-400"><?php echo e($product->reviews_count ?? 0); ?></td>
                            <?php endif; ?>

                            <?php if(in_array('created_at', $visibleColumns)): ?>
                            <td class="px-3 py-1.5 text-xs text-gray-500 dark:text-gray-400"><?php echo e($product->created_at?->format('d.m.Y')); ?></td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="20" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-inbox'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mx-auto h-10 w-10 text-gray-300 dark:text-gray-600 mb-2']); ?>
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
                                Товарів не знайдено
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-gray-200 dark:border-white/5">
                <?php echo e($this->getProducts()->links()); ?>

            </div>
        </div>

        

        
        <?php if($showPriceModal): ?>
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" x-data x-on:keydown.escape.window="$wire.set('showPriceModal', false)">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-gray-900">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Масова зміна ціни</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Тип операції</label>
                        <select wire:model="priceType" class="fi-select-input block w-full rounded-lg border-gray-300 text-sm dark:border-white/10 dark:bg-white/5 dark:text-white">
                            <option value="set">Встановити фіксовану ціну</option>
                            <option value="increase">Збільшити на суму</option>
                            <option value="decrease">Зменшити на суму</option>
                            <option value="increase_percent">Збільшити на %</option>
                            <option value="decrease_percent">Зменшити на %</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Значення</label>
                        <input type="number" wire:model="priceValue" step="0.01" min="0" class="fi-input block w-full rounded-lg border-gray-300 text-sm dark:border-white/10 dark:bg-white/5 dark:text-white">
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <?php if (isset($component)) { $__componentOriginal6330f08526bbb3ce2a0da37da512a11f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => '$set(\'showPriceModal\', false)','color' => 'gray']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => '$set(\'showPriceModal\', false)','color' => 'gray']); ?>Скасувати <?php echo $__env->renderComponent(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => 'previewPrice','color' => 'primary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'previewPrice','color' => 'primary']); ?>Попередній перегляд <?php echo $__env->renderComponent(); ?>
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
        <?php endif; ?>

        
        <?php if($showSaleModal): ?>
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" x-data x-on:keydown.escape.window="$wire.set('showSaleModal', false)">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-gray-900">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Встановити акцію</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Тип знижки</label>
                        <select wire:model="saleType" class="fi-select-input block w-full rounded-lg border-gray-300 text-sm dark:border-white/10 dark:bg-white/5 dark:text-white">
                            <option value="percent">Відсоток (%)</option>
                            <option value="fixed">Фіксована сума (грн)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Значення знижки</label>
                        <input type="number" wire:model="saleValue" step="0.01" min="0" class="fi-input block w-full rounded-lg border-gray-300 text-sm dark:border-white/10 dark:bg-white/5 dark:text-white">
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <?php if (isset($component)) { $__componentOriginal6330f08526bbb3ce2a0da37da512a11f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => 'removeSale','color' => 'danger','outlined' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'removeSale','color' => 'danger','outlined' => true]); ?>Зняти акцію <?php echo $__env->renderComponent(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => '$set(\'showSaleModal\', false)','color' => 'gray']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => '$set(\'showSaleModal\', false)','color' => 'gray']); ?>Скасувати <?php echo $__env->renderComponent(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => 'previewSale','color' => 'primary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'previewSale','color' => 'primary']); ?>Попередній перегляд <?php echo $__env->renderComponent(); ?>
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
        <?php endif; ?>

        
        <?php if($showGroupPriceModal): ?>
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" x-data x-on:keydown.escape.window="$wire.set('showGroupPriceModal', false)">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-gray-900">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Гуртові ціни</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Група клієнтів</label>
                        <select wire:model="groupPriceGroupId" class="fi-select-input block w-full rounded-lg border-gray-300 text-sm dark:border-white/10 dark:bg-white/5 dark:text-white">
                            <option value="">Виберіть групу</option>
                            <?php $__currentLoopData = $this->getCustomerGroups(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($id); ?>"><?php echo e($name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Тип ціни</label>
                        <select wire:model="groupPriceType" class="fi-select-input block w-full rounded-lg border-gray-300 text-sm dark:border-white/10 dark:bg-white/5 dark:text-white">
                            <option value="percent">Знижка від основної ціни (%)</option>
                            <option value="fixed">Фіксована ціна (грн)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Значення</label>
                        <input type="number" wire:model="groupPriceValue" step="0.01" min="0" class="fi-input block w-full rounded-lg border-gray-300 text-sm dark:border-white/10 dark:bg-white/5 dark:text-white">
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <?php if (isset($component)) { $__componentOriginal6330f08526bbb3ce2a0da37da512a11f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => '$set(\'showGroupPriceModal\', false)','color' => 'gray']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => '$set(\'showGroupPriceModal\', false)','color' => 'gray']); ?>Скасувати <?php echo $__env->renderComponent(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => 'previewGroupPrice','color' => 'primary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'previewGroupPrice','color' => 'primary']); ?>Попередній перегляд <?php echo $__env->renderComponent(); ?>
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
        <?php endif; ?>

        
        <?php if($showStatusModal): ?>
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" x-data x-on:keydown.escape.window="$wire.set('showStatusModal', false)">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-gray-900">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Масова зміна статусу</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Поле</label>
                        <select wire:model="statusField" class="fi-select-input block w-full rounded-lg border-gray-300 text-sm dark:border-white/10 dark:bg-white/5 dark:text-white">
                            <option value="is_active">Активність</option>
                            <option value="is_hit">Хіт продажів</option>
                            <option value="is_new">Новинка</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Значення</label>
                        <select wire:model="statusValue" class="fi-select-input block w-full rounded-lg border-gray-300 text-sm dark:border-white/10 dark:bg-white/5 dark:text-white">
                            <option value="1">Увімкнено</option>
                            <option value="0">Вимкнено</option>
                        </select>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <?php if (isset($component)) { $__componentOriginal6330f08526bbb3ce2a0da37da512a11f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => '$set(\'showStatusModal\', false)','color' => 'gray']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => '$set(\'showStatusModal\', false)','color' => 'gray']); ?>Скасувати <?php echo $__env->renderComponent(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => 'applyStatus','wire:confirm' => 'Змінити статус для '.e(count($selectedIds)).' товарів?','color' => 'primary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'applyStatus','wire:confirm' => 'Змінити статус для '.e(count($selectedIds)).' товарів?','color' => 'primary']); ?>Застосувати <?php echo $__env->renderComponent(); ?>
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
        <?php endif; ?>

        
        <?php if($showCategoryModal): ?>
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" x-data x-on:keydown.escape.window="$wire.set('showCategoryModal', false)">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-gray-900">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Змінити категорію</h3>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Нова категорія</label>
                    <select wire:model="newCategoryId" class="fi-select-input block w-full rounded-lg border-gray-300 text-sm dark:border-white/10 dark:bg-white/5 dark:text-white">
                        <option value="">Виберіть категорію</option>
                        <?php $__currentLoopData = $this->getCategories(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $title): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($id); ?>"><?php echo e($title); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <?php if (isset($component)) { $__componentOriginal6330f08526bbb3ce2a0da37da512a11f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => '$set(\'showCategoryModal\', false)','color' => 'gray']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => '$set(\'showCategoryModal\', false)','color' => 'gray']); ?>Скасувати <?php echo $__env->renderComponent(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => 'applyCategory','color' => 'primary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'applyCategory','color' => 'primary']); ?>Застосувати <?php echo $__env->renderComponent(); ?>
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
        <?php endif; ?>

        
        <?php if($showBrandModal): ?>
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" x-data x-on:keydown.escape.window="$wire.set('showBrandModal', false)">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-gray-900">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Змінити бренд/виробника</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Бренд</label>
                        <select wire:model="newBrandId" class="fi-select-input block w-full rounded-lg border-gray-300 text-sm dark:border-white/10 dark:bg-white/5 dark:text-white">
                            <option value="">Не змінювати</option>
                            <?php $__currentLoopData = $this->getBrands(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($id); ?>"><?php echo e($name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Виробник (текст)</label>
                        <input type="text" wire:model="newManufacturer" placeholder="Не змінювати" class="fi-input block w-full rounded-lg border-gray-300 text-sm dark:border-white/10 dark:bg-white/5 dark:text-white">
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <?php if (isset($component)) { $__componentOriginal6330f08526bbb3ce2a0da37da512a11f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => '$set(\'showBrandModal\', false)','color' => 'gray']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => '$set(\'showBrandModal\', false)','color' => 'gray']); ?>Скасувати <?php echo $__env->renderComponent(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => 'applyBrand','color' => 'primary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'applyBrand','color' => 'primary']); ?>Застосувати <?php echo $__env->renderComponent(); ?>
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
        <?php endif; ?>

        
        <?php if($showFilterModal): ?>
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" x-data x-on:keydown.escape.window="$wire.set('showFilterModal', false)">
            <div class="w-full max-w-lg rounded-xl bg-white p-6 shadow-xl dark:bg-gray-900">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Масове управління фільтрами</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Дія</label>
                        <select wire:model="filterAction" class="fi-select-input block w-full rounded-lg border-gray-300 text-sm dark:border-white/10 dark:bg-white/5 dark:text-white">
                            <option value="attach">Додати фільтри</option>
                            <option value="detach">Видалити фільтри</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Фільтри</label>
                        <div class="max-h-60 overflow-y-auto space-y-3 rounded-lg border border-gray-200 dark:border-white/10 p-3">
                            <?php $__currentLoopData = $this->getFilterGroups(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-1"><?php echo e($group->name); ?></p>
                                    <div class="space-y-1">
                                        <?php $__currentLoopData = $group->filters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $filter): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                                                <input type="checkbox" value="<?php echo e($filter->id); ?>" wire:model="selectedFilterIds" class="fi-checkbox-input rounded border-gray-300 text-primary-600 dark:border-white/10 dark:bg-white/5">
                                                <?php echo e($filter->name); ?>

                                            </label>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <?php if (isset($component)) { $__componentOriginal6330f08526bbb3ce2a0da37da512a11f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => '$set(\'showFilterModal\', false)','color' => 'gray']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => '$set(\'showFilterModal\', false)','color' => 'gray']); ?>Скасувати <?php echo $__env->renderComponent(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => 'applyFilters','color' => 'primary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'applyFilters','color' => 'primary']); ?>Застосувати <?php echo $__env->renderComponent(); ?>
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
        <?php endif; ?>

        
        <?php if($showSearchReplaceModal): ?>
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" x-data x-on:keydown.escape.window="$wire.set('showSearchReplaceModal', false)">
            <div class="w-full max-w-lg rounded-xl bg-white p-6 shadow-xl dark:bg-gray-900">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Пошук та заміна</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Поле</label>
                        <select wire:model="srField" class="fi-select-input block w-full rounded-lg border-gray-300 text-sm dark:border-white/10 dark:bg-white/5 dark:text-white">
                            <option value="title">Назва</option>
                            <option value="excerpt">Короткий опис</option>
                            <option value="content">Повний опис</option>
                            <option value="meta_title">SEO Title</option>
                            <option value="meta_description">SEO Description</option>
                            <option value="manufacturer">Виробник</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Знайти</label>
                        <input type="text" wire:model="srSearch" class="fi-input block w-full rounded-lg border-gray-300 text-sm dark:border-white/10 dark:bg-white/5 dark:text-white" placeholder="Текст для пошуку...">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Замінити на</label>
                        <input type="text" wire:model="srReplace" class="fi-input block w-full rounded-lg border-gray-300 text-sm dark:border-white/10 dark:bg-white/5 dark:text-white" placeholder="Текст заміни...">
                    </div>
                    <div class="flex items-center gap-4">
                        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                            <input type="checkbox" wire:model="srCaseSensitive" class="fi-checkbox-input rounded border-gray-300 text-primary-600 dark:border-white/10 dark:bg-white/5">
                            Враховувати регістр
                        </label>
                        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                            <input type="checkbox" wire:model="srUseRegex" class="fi-checkbox-input rounded border-gray-300 text-primary-600 dark:border-white/10 dark:bg-white/5">
                            Regex
                        </label>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <?php if (isset($component)) { $__componentOriginal6330f08526bbb3ce2a0da37da512a11f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => '$set(\'showSearchReplaceModal\', false)','color' => 'gray']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => '$set(\'showSearchReplaceModal\', false)','color' => 'gray']); ?>Скасувати <?php echo $__env->renderComponent(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => 'previewSR','color' => 'primary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'previewSR','color' => 'primary']); ?>Знайти <?php echo $__env->renderComponent(); ?>
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
        <?php endif; ?>

        
        <?php if($showWeightModal): ?>
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" x-data x-on:keydown.escape.window="$wire.set('showWeightModal', false)">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-gray-900">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Вага та розміри</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Вага (кг)</label>
                        <input type="number" wire:model="newWeight" step="0.001" min="0" class="fi-input block w-full rounded-lg border-gray-300 text-sm dark:border-white/10 dark:bg-white/5 dark:text-white" placeholder="0.000">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Розміри (Д x Ш x В)</label>
                        <input type="text" wire:model="newDimensions" class="fi-input block w-full rounded-lg border-gray-300 text-sm dark:border-white/10 dark:bg-white/5 dark:text-white" placeholder="10x20x30">
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <?php if (isset($component)) { $__componentOriginal6330f08526bbb3ce2a0da37da512a11f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => '$set(\'showWeightModal\', false)','color' => 'gray']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => '$set(\'showWeightModal\', false)','color' => 'gray']); ?>Скасувати <?php echo $__env->renderComponent(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => 'applyWeight','color' => 'primary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'applyWeight','color' => 'primary']); ?>Застосувати <?php echo $__env->renderComponent(); ?>
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
        <?php endif; ?>

        
        <?php if($showImportModal): ?>
        <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" x-data x-on:keydown.escape.window="$wire.set('showImportModal', false)">
            <div class="bg-white dark:bg-gray-800 rounded-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto p-6">
                
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Імпорт CSV</h3>
                    
                    <?php if (isset($component)) { $__componentOriginalf0029cce6d19fd6d472097ff06a800a1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf0029cce6d19fd6d472097ff06a800a1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.icon-button','data' => ['icon' => 'heroicon-m-x-mark','wire:click' => '$set(\'showImportModal\', false)','label' => 'Закрити','color' => 'gray']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::icon-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'heroicon-m-x-mark','wire:click' => '$set(\'showImportModal\', false)','label' => 'Закрити','color' => 'gray']); ?>
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

                
                <div class="flex items-center gap-2 mb-6">
                    <?php $__currentLoopData = [1 => 'Завантаження', 2 => 'Налаштування', 3 => 'Результат']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex items-center gap-2" <?php if($step < 3): ?> style="flex:1 1 0%" <?php endif; ?>>
                        <div class="flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold transition
                            <?php echo e($importStep >= $step ? 'bg-primary-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400'); ?>">
                            <?php if($importStep > $step): ?>
                                <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-m-check'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-4 w-4']); ?>
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
                            <?php else: ?>
                                <?php echo e($step); ?>

                            <?php endif; ?>
                        </div>
                        <span class="text-xs font-medium <?php echo e($importStep >= $step ? 'text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-500'); ?>"><?php echo e($label); ?></span>
                        <?php if($step < 3): ?>
                        <div class="h-px <?php echo e($importStep > $step ? 'bg-primary-400' : 'bg-gray-200 dark:bg-gray-700'); ?>" style="flex:1 1 0%"></div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                
                <?php if($importStep === 1): ?>
                <div class="space-y-4">
                    <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-8 text-center hover:border-primary-400 dark:hover:border-primary-500 transition">
                        <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-arrow-up-tray'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mx-auto h-10 w-10 text-gray-400 dark:text-gray-500 mb-3']); ?>
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
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">Виберіть CSV файл для імпорту товарів</p>
                        <input type="file" wire:model="importFile" accept=".csv,.txt"
                            class="fi-input block w-full max-w-sm mx-auto rounded-lg border-gray-300 text-sm shadow-sm dark:border-white/10 dark:bg-white/5 dark:text-white p-2">
                        <div wire:loading wire:target="importFile" class="text-sm text-primary-600 dark:text-primary-400 mt-3 flex items-center justify-center gap-2">
                            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            Завантаження файлу...
                        </div>
                    </div>
                    <?php if (isset($component)) { $__componentOriginalee08b1367eba38734199cf7829b1d1e9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalee08b1367eba38734199cf7829b1d1e9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.section.index','data' => ['icon' => 'heroicon-o-information-circle','iconColor' => 'info']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'heroicon-o-information-circle','icon-color' => 'info']); ?>
                         <?php $__env->slot('heading', null, []); ?> Вимоги до файлу <?php $__env->endSlot(); ?>
                        <ul class="text-xs text-gray-600 dark:text-gray-400 space-y-0.5 list-disc list-inside">
                            <li>Формат CSV з роздільником ","</li>
                            <li>Перший рядок -- заголовки колонок</li>
                            <li>Кодування UTF-8</li>
                            <li>Для оновлення існуючих товарів використовується поле SKU</li>
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
                </div>
                <?php endif; ?>

                
                <?php if($importStep === 2): ?>
                <div class="space-y-4">
                    
                    <div class="flex items-center justify-between bg-gray-50 dark:bg-white/5 rounded-lg p-3">
                        <div class="flex items-center gap-2">
                            <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-m-document-text'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-5 w-5 text-gray-400']); ?>
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
                            <span class="text-sm text-gray-700 dark:text-gray-300"><?php echo e($importTotalRows); ?> рядків знайдено</span>
                        </div>
                        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                            <input type="checkbox" wire:model="importUpdateExisting" class="fi-checkbox-input rounded border-gray-300 text-primary-600 dark:border-white/10 dark:bg-white/5">
                            Оновлювати існуючі (за SKU)
                        </label>
                    </div>

                    
                    <div>
                        <h4 class="font-semibold text-sm text-gray-900 dark:text-white mb-3">Відповідність колонок</h4>
                        <div style="display:grid;gap:0.5rem;grid-template-columns:repeat(auto-fit,minmax(340px,1fr))">
                            <?php $fieldLabels = $this->getImportFieldLabels(); ?>
                            <?php $__currentLoopData = $importHeaders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $header): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="flex items-center gap-2 bg-gray-50 dark:bg-white/5 rounded-lg px-3 py-2">
                                <span class="text-xs font-mono bg-white dark:bg-gray-700 px-2 py-1 rounded shadow-sm text-gray-700 dark:text-gray-300 min-w-[80px] truncate" title="<?php echo e($header); ?>"><?php echo e(Str::limit($header, 18)); ?></span>
                                <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-m-arrow-right'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-3.5 w-3.5 text-gray-400 flex-shrink-0']); ?>
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
                                <select wire:model="importMapping.<?php echo e($i); ?>" class="fi-select-input rounded-lg border-gray-300 text-xs shadow-sm dark:border-white/10 dark:bg-white/5 dark:text-white py-1.5" style="flex:1 1 0%">
                                    <option value="skip">-- Пропустити --</option>
                                    <?php $__currentLoopData = $fieldLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($field); ?>"><?php echo e($label); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>

                    
                    <?php if(!empty($importPreview)): ?>
                    <div>
                        <h4 class="font-semibold text-sm text-gray-900 dark:text-white mb-2">Перегляд (перші <?php echo e(count($importPreview)); ?> рядків)</h4>
                        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-white/10">
                            <table class="w-full text-xs border-collapse">
                                <thead>
                                    <tr>
                                        <th class="border-b border-r border-gray-200 dark:border-white/10 p-2 bg-gray-100 dark:bg-white/5 text-gray-500 dark:text-gray-400 text-center w-10">#</th>
                                        <?php $__currentLoopData = $importHeaders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <th class="border-b border-r last:border-r-0 border-gray-200 dark:border-white/10 p-2 bg-gray-100 dark:bg-white/5 text-left">
                                            <span class="text-gray-700 dark:text-gray-300 font-semibold"><?php echo e($h); ?></span>
                                            <?php if(isset($importMapping[$idx]) && $importMapping[$idx] !== 'skip'): ?>
                                            <span class="block text-[10px] text-primary-600 dark:text-primary-400 font-normal mt-0.5">
                                                &rarr; <?php echo e($fieldLabels[$importMapping[$idx]] ?? $importMapping[$idx]); ?>

                                            </span>
                                            <?php endif; ?>
                                        </th>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $importPreview; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rowIdx => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                                        <td class="border-b border-r border-gray-200 dark:border-white/10 p-1.5 text-center text-gray-400 font-mono"><?php echo e($rowIdx + 1); ?></td>
                                        <?php $__currentLoopData = $row; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cellIdx => $cell): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <td class="border-b border-r last:border-r-0 border-gray-200 dark:border-white/10 p-1.5 text-gray-600 dark:text-gray-400
                                            <?php echo e(isset($importMapping[$cellIdx]) && $importMapping[$cellIdx] !== 'skip' ? 'bg-primary-50/50 dark:bg-primary-900/10' : ''); ?>">
                                            <?php echo e(Str::limit($cell, 35)); ?>

                                        </td>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php endif; ?>

                    
                    <div class="flex items-center justify-between pt-2">
                        <?php if (isset($component)) { $__componentOriginal6330f08526bbb3ce2a0da37da512a11f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => 'resetImport','color' => 'gray','icon' => 'heroicon-m-arrow-left','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'resetImport','color' => 'gray','icon' => 'heroicon-m-arrow-left','size' => 'sm']); ?>Назад <?php echo $__env->renderComponent(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => 'executeImport','wire:confirm' => 'Імпортувати '.e($importTotalRows).' рядків?','color' => 'success','icon' => 'heroicon-m-arrow-up-tray']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'executeImport','wire:confirm' => 'Імпортувати '.e($importTotalRows).' рядків?','color' => 'success','icon' => 'heroicon-m-arrow-up-tray']); ?>
                            Імпортувати (<?php echo e($importTotalRows); ?> рядків)
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

                
                <?php if($importStep === 3): ?>
                <div class="space-y-4">
                    
                    <div style="display:grid;gap:0.75rem;grid-template-columns:repeat(auto-fit,minmax(110px,1fr))">
                        <div class="bg-success-50 dark:bg-success-400/10 border border-success-200 dark:border-success-400/20 rounded-xl p-4 text-center">
                            <p class="text-2xl font-bold text-success-700 dark:text-success-300"><?php echo e($importStats['created'] ?? 0); ?></p>
                            <p class="text-xs text-success-600 dark:text-success-400 font-medium mt-1">Створено</p>
                        </div>
                        <div class="bg-info-50 dark:bg-info-400/10 border border-info-200 dark:border-info-400/20 rounded-xl p-4 text-center">
                            <p class="text-2xl font-bold text-info-700 dark:text-info-300"><?php echo e($importStats['updated'] ?? 0); ?></p>
                            <p class="text-xs text-info-600 dark:text-info-400 font-medium mt-1">Оновлено</p>
                        </div>
                        <div class="bg-warning-50 dark:bg-warning-400/10 border border-warning-200 dark:border-warning-400/20 rounded-xl p-4 text-center">
                            <p class="text-2xl font-bold text-warning-700 dark:text-warning-300"><?php echo e($importStats['skipped'] ?? 0); ?></p>
                            <p class="text-xs text-warning-600 dark:text-warning-400 font-medium mt-1">Пропущено</p>
                        </div>
                        <div class="bg-danger-50 dark:bg-danger-400/10 border border-danger-200 dark:border-danger-400/20 rounded-xl p-4 text-center">
                            <p class="text-2xl font-bold text-danger-700 dark:text-danger-300"><?php echo e($importStats['errors'] ?? 0); ?></p>
                            <p class="text-xs text-danger-600 dark:text-danger-400 font-medium mt-1">Помилок</p>
                        </div>
                    </div>

                    
                    <?php if(!empty($importStats['error_messages'])): ?>
                    <div class="bg-danger-50 dark:bg-danger-400/10 border border-danger-200 dark:border-danger-400/20 rounded-lg p-3">
                        <p class="text-xs font-semibold text-danger-700 dark:text-danger-300 mb-2">Деталі помилок:</p>
                        <ul class="text-xs text-danger-600 dark:text-danger-400 space-y-1 list-disc list-inside max-h-40 overflow-y-auto">
                            <?php $__currentLoopData = $importStats['error_messages']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $errMsg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($errMsg); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                    <?php endif; ?>

                    
                    <div class="flex items-center justify-between pt-2">
                        <?php if (isset($component)) { $__componentOriginal6330f08526bbb3ce2a0da37da512a11f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => 'resetImport','color' => 'gray','icon' => 'heroicon-m-arrow-path','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'resetImport','color' => 'gray','icon' => 'heroicon-m-arrow-path','size' => 'sm']); ?>Імпортувати ще <?php echo $__env->renderComponent(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => '$set(\'showImportModal\', false)','color' => 'primary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => '$set(\'showImportModal\', false)','color' => 'primary']); ?>Закрити <?php echo $__env->renderComponent(); ?>
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
            </div>
        </div>
        <?php endif; ?>

        
        <?php if($showSeoModal): ?>
        <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" x-data x-on:keydown.escape.window="$wire.set('showSeoModal', false)">
            <div class="bg-white dark:bg-gray-900 rounded-xl max-w-lg w-full p-6 shadow-xl">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">МАСОВЕ SEO</h3>
                <div class="space-y-4">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Дія</label>
                        <select wire:model.live="seoAction" class="fi-select-input block w-full rounded-lg border-gray-300 text-sm dark:border-white/10 dark:bg-white/5 dark:text-white">
                            <option value="template">За шаблоном</option>
                            <option value="auto_generate">Авто-генерація (SeoMetaGenerator)</option>
                        </select>
                    </div>

                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Поле</label>
                        <select wire:model="seoField" class="fi-select-input block w-full rounded-lg border-gray-300 text-sm dark:border-white/10 dark:bg-white/5 dark:text-white">
                            <option value="meta_title">Meta Title</option>
                            <option value="meta_description">Meta Description</option>
                            <option value="meta_keywords">Meta Keywords</option>
                            <?php if($seoAction === 'auto_generate'): ?>
                            <option value="all">Всі поля</option>
                            <?php endif; ?>
                        </select>
                    </div>

                    
                    <?php if($seoAction === 'template'): ?>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Шаблон</label>
                        <textarea wire:model="seoTemplate" rows="3" class="fi-input block w-full rounded-lg border-gray-300 text-sm dark:border-white/10 dark:bg-white/5 dark:text-white"
                            placeholder="<?php echo e($seoField === 'meta_keywords' ? '{title}, купити {title}, {brand}, {category}' : 'Купити {title} від {brand} в {category} | Ціна {price} грн'); ?>"></textarea>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Плейсхолдери: {title}, {brand}, {category}, {price}, {sku}</p>
                        <?php if($seoField === 'meta_keywords'): ?>
                        <p class="text-xs text-warning-600 dark:text-warning-400 mt-1">Keywords розділяються комами</p>
                        <?php endif; ?>
                    </div>
                    <?php else: ?>
                    <div class="p-3 bg-info-50 dark:bg-info-400/10 border border-info-200 dark:border-info-400/20 rounded-lg">
                        <p class="text-xs text-info-700 dark:text-info-300">SEO буде згенеровано автоматично на основі назви товару, ціни, категорії та налаштувань магазину.</p>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <?php if (isset($component)) { $__componentOriginal6330f08526bbb3ce2a0da37da512a11f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => '$set(\'showSeoModal\', false)','color' => 'gray']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => '$set(\'showSeoModal\', false)','color' => 'gray']); ?>СКАСУВАТИ <?php echo $__env->renderComponent(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => 'applySeoMeta','wire:confirm' => 'Оновити SEO для '.e(count($selectedIds)).' товарів?','color' => 'primary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'applySeoMeta','wire:confirm' => 'Оновити SEO для '.e(count($selectedIds)).' товарів?','color' => 'primary']); ?>ЗАСТОСУВАТИ <?php echo $__env->renderComponent(); ?>
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
        <?php endif; ?>

        
        <?php if($showVariantModal): ?>
        <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" x-data x-on:keydown.escape.window="$wire.set('showVariantModal', false)">
            <div class="bg-white dark:bg-gray-900 rounded-xl max-w-2xl w-full max-h-[80vh] overflow-y-auto p-6 shadow-xl">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Генерація варіантів</h3>

                <?php if(empty($variantPreview)): ?>
                <div class="text-center py-8">
                    <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-cube'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mx-auto h-10 w-10 text-gray-300 dark:text-gray-600 mb-2']); ?>
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
                    <p class="text-sm text-gray-500 dark:text-gray-400">Жоден з вибраних товарів не має активних опцій для генерації варіантів.</p>
                </div>
                <?php else: ?>
                <div class="mb-4">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                        Буде згенеровано варіанти для <strong class="text-primary-600 dark:text-primary-400"><?php echo e(count($variantPreview)); ?></strong> товарів:
                    </p>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm border-collapse">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-white/5">
                                    <th class="border border-gray-200 dark:border-white/10 px-3 py-2 text-left text-xs font-semibold text-gray-600 dark:text-gray-400">Товар</th>
                                    <th class="border border-gray-200 dark:border-white/10 px-3 py-2 text-left text-xs font-semibold text-gray-600 dark:text-gray-400">Опції</th>
                                    <th class="border border-gray-200 dark:border-white/10 px-3 py-2 text-center text-xs font-semibold text-gray-600 dark:text-gray-400 w-24">Комбінацій</th>
                                    <th class="border border-gray-200 dark:border-white/10 px-3 py-2 text-center text-xs font-semibold text-gray-600 dark:text-gray-400 w-20">Вже є</th>
                                    <th class="border border-gray-200 dark:border-white/10 px-3 py-2 text-center text-xs font-semibold text-gray-600 dark:text-gray-400 w-20">Нових</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                                <?php $__currentLoopData = $variantPreview; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                                    <td class="border border-gray-200 dark:border-white/10 px-3 py-2 text-xs text-gray-700 dark:text-gray-300">
                                        <span class="font-mono text-gray-400 mr-1">#<?php echo e($item['id']); ?></span>
                                        <?php echo e(Str::limit($item['title'], 40)); ?>

                                    </td>
                                    <td class="border border-gray-200 dark:border-white/10 px-3 py-2 text-xs text-gray-500 dark:text-gray-400"><?php echo e($item['options']); ?></td>
                                    <td class="border border-gray-200 dark:border-white/10 px-3 py-2 text-center text-xs font-semibold text-gray-700 dark:text-gray-300"><?php echo e($item['combinations']); ?></td>
                                    <td class="border border-gray-200 dark:border-white/10 px-3 py-2 text-center text-xs text-gray-500 dark:text-gray-400"><?php echo e($item['existing']); ?></td>
                                    <td class="border border-gray-200 dark:border-white/10 px-3 py-2 text-center text-xs font-semibold <?php echo e($item['new'] > 0 ? 'text-success-600 dark:text-success-400' : 'text-gray-400'); ?>"><?php echo e($item['new']); ?></td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                            <tfoot>
                                <tr class="bg-gray-50 dark:bg-white/5">
                                    <td colspan="4" class="border border-gray-200 dark:border-white/10 px-3 py-2 text-xs font-semibold text-gray-700 dark:text-gray-300 text-right">Всього нових варіантів:</td>
                                    <td class="border border-gray-200 dark:border-white/10 px-3 py-2 text-center text-sm font-bold text-success-600 dark:text-success-400"><?php echo e(collect($variantPreview)->sum('new')); ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <?php endif; ?>

                <div class="mt-6 flex justify-end gap-3">
                    <?php if (isset($component)) { $__componentOriginal6330f08526bbb3ce2a0da37da512a11f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => '$set(\'showVariantModal\', false)','color' => 'gray']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => '$set(\'showVariantModal\', false)','color' => 'gray']); ?>Скасувати <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6330f08526bbb3ce2a0da37da512a11f)): ?>
<?php $attributes = $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f; ?>
<?php unset($__attributesOriginal6330f08526bbb3ce2a0da37da512a11f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6330f08526bbb3ce2a0da37da512a11f)): ?>
<?php $component = $__componentOriginal6330f08526bbb3ce2a0da37da512a11f; ?>
<?php unset($__componentOriginal6330f08526bbb3ce2a0da37da512a11f); ?>
<?php endif; ?>
                    <?php if(!empty($variantPreview) && collect($variantPreview)->sum('new') > 0): ?>
                    <?php if (isset($component)) { $__componentOriginal6330f08526bbb3ce2a0da37da512a11f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => 'generateVariants','wire:confirm' => 'Згенерувати '.e(collect($variantPreview)->sum('new')).' варіантів?','color' => 'info']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'generateVariants','wire:confirm' => 'Згенерувати '.e(collect($variantPreview)->sum('new')).' варіантів?','color' => 'info']); ?>
                        Генерувати (<?php echo e(collect($variantPreview)->sum('new')); ?>)
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
        </div>
        <?php endif; ?>

        
        <?php if(count($srPreview) > 0): ?>
        <div class="fi-section rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Результати заміни</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-white/10">
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 dark:text-gray-400">ID</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 dark:text-gray-400">Товар</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 dark:text-gray-400">Було</th>
                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 dark:text-gray-400">Стало</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        <?php $__currentLoopData = $srPreview; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td class="px-3 py-2 text-xs font-mono text-gray-500"><?php echo e($item['id']); ?></td>
                                <td class="px-3 py-2 text-xs text-gray-700 dark:text-gray-300"><?php echo e($item['title']); ?></td>
                                <td class="px-3 py-2 text-xs text-danger-600 dark:text-danger-400 line-through"><?php echo e($item['original']); ?></td>
                                <td class="px-3 py-2 text-xs text-success-600 dark:text-success-400"><?php echo e($item['new']); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-3 flex justify-end">
                <button wire:click="$set('srPreview', [])" class="text-xs text-gray-500 hover:text-primary-500">Закрити перегляд</button>
            </div>
        </div>
        <?php endif; ?>

        <?php endif; ?> 

        
        <?php if($activeTab === 'categories'): ?>

        
        <div class="fi-section rounded-xl bg-white p-3 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="flex flex-wrap items-center gap-2">
                <?php if (isset($component)) { $__componentOriginal6330f08526bbb3ce2a0da37da512a11f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => 'saveCategoryChanges','color' => 'success','icon' => 'heroicon-m-check','size' => 'sm','badge' => count($editedCategoryData) > 0 ? count($editedCategoryData) : null,'badgeColor' => 'danger']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'saveCategoryChanges','color' => 'success','icon' => 'heroicon-m-check','size' => 'sm','badge' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(count($editedCategoryData) > 0 ? count($editedCategoryData) : null),'badge-color' => 'danger']); ?>
                    Зберегти зміни
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

                <span class="h-6 w-px bg-gray-300 dark:bg-white/10"></span>

                <?php if (isset($component)) { $__componentOriginal6330f08526bbb3ce2a0da37da512a11f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => 'batchActivateCategories','color' => 'gray','icon' => 'heroicon-m-eye','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'batchActivateCategories','color' => 'gray','icon' => 'heroicon-m-eye','size' => 'sm']); ?>Активувати <?php echo $__env->renderComponent(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => 'batchDeactivateCategories','color' => 'gray','icon' => 'heroicon-m-eye-slash','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'batchDeactivateCategories','color' => 'gray','icon' => 'heroicon-m-eye-slash','size' => 'sm']); ?>Деактивувати <?php echo $__env->renderComponent(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => '$set(\'showParentCategoryModal\', true)','color' => 'gray','icon' => 'heroicon-m-folder','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => '$set(\'showParentCategoryModal\', true)','color' => 'gray','icon' => 'heroicon-m-folder','size' => 'sm']); ?>Змінити батьківську <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6330f08526bbb3ce2a0da37da512a11f)): ?>
<?php $attributes = $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f; ?>
<?php unset($__attributesOriginal6330f08526bbb3ce2a0da37da512a11f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6330f08526bbb3ce2a0da37da512a11f)): ?>
<?php $component = $__componentOriginal6330f08526bbb3ce2a0da37da512a11f; ?>
<?php unset($__componentOriginal6330f08526bbb3ce2a0da37da512a11f); ?>
<?php endif; ?>

                <?php if(count($selectedIds) > 0): ?>
                    <span class="ml-auto text-xs text-gray-500 dark:text-gray-400">
                        Вибрано: <strong class="text-primary-600 dark:text-primary-400"><?php echo e(count($selectedIds)); ?></strong>
                    </span>
                <?php endif; ?>
            </div>
        </div>

        
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-white/5 border-b border-gray-200 dark:border-white/10">
                            <th class="px-2 py-3 text-center w-10">
                                <input type="checkbox" wire:model.live="selectAll" wire:click="toggleSelectAll" class="fi-checkbox-input rounded border-gray-300 text-primary-600 shadow-sm dark:border-white/10 dark:bg-white/5">
                            </th>
                            <th class="px-2 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 w-16">ID</th>
                            <th class="px-2 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 min-w-[250px]">Назва</th>
                            <th class="px-2 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 w-48 hidden md:table-cell">Slug</th>
                            <th class="px-2 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 w-40">Батьківська</th>
                            <th class="px-2 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 w-24">Сортування</th>
                            <th class="px-2 py-3 text-center text-xs font-semibold text-gray-600 dark:text-gray-400 w-16">Акт.</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        <?php $__empty_1 = true; $__currentLoopData = $this->getCategoryItems(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition" wire:key="category-<?php echo e($category->id); ?>">
                                <td class="px-2 py-1.5 text-center">
                                    <input type="checkbox" value="<?php echo e($category->id); ?>" wire:model.live="selectedIds" class="fi-checkbox-input rounded border-gray-300 text-primary-600 shadow-sm dark:border-white/10 dark:bg-white/5">
                                </td>
                                <td class="px-2 py-1.5 text-xs text-gray-500 dark:text-gray-400 font-mono"><?php echo e($category->id); ?></td>
                                <td class="px-2 py-1.5">
                                    <input
                                        type="text"
                                        value="<?php echo e($editedCategoryData[$category->id]['title'] ?? $category->title); ?>"
                                        wire:change="updateCategoryField(<?php echo e($category->id); ?>, 'title', $event.target.value)"
                                        class="fi-input block w-full rounded-lg border-none bg-white px-3 py-1.5 text-sm text-gray-950 shadow-sm ring-1 ring-inset ring-gray-950/10 transition focus:ring-2 focus:ring-inset focus:ring-primary-500 dark:bg-white/5 dark:text-white dark:ring-white/10 <?php echo e(isset($editedCategoryData[$category->id]['title']) ? 'bg-warning-50 dark:bg-warning-400/10 ring-warning-400' : ''); ?>"
                                    >
                                </td>
                                <td class="px-2 py-1.5 text-xs text-gray-500 dark:text-gray-400 font-mono hidden md:table-cell"><?php echo e($category->slug); ?></td>
                                <td class="px-2 py-1.5 text-xs text-gray-600 dark:text-gray-400">
                                    <?php if($category->parent): ?>
                                        <?php if (isset($component)) { $__componentOriginal986dce9114ddce94a270ab00ce6c273d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal986dce9114ddce94a270ab00ce6c273d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.badge','data' => ['color' => 'primary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'primary']); ?><?php echo e($category->parent->title); ?> <?php echo $__env->renderComponent(); ?>
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
                                        <span class="text-gray-400">--</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-2 py-1.5">
                                    <input
                                        type="number"
                                        min="0"
                                        value="<?php echo e($editedCategoryData[$category->id]['sort_order'] ?? $category->sort_order); ?>"
                                        wire:change="updateCategoryField(<?php echo e($category->id); ?>, 'sort_order', $event.target.value)"
                                        class="fi-input block w-full rounded-lg border-none bg-white px-3 py-1.5 text-sm text-gray-950 shadow-sm ring-1 ring-inset ring-gray-950/10 transition focus:ring-2 focus:ring-inset focus:ring-primary-500 dark:bg-white/5 dark:text-white dark:ring-white/10 <?php echo e(isset($editedCategoryData[$category->id]['sort_order']) ? 'bg-warning-50 dark:bg-warning-400/10 ring-warning-400' : ''); ?>"
                                    >
                                </td>
                                <td class="px-2 py-1.5 text-center">
                                    <input
                                        type="checkbox"
                                        <?php echo e(($editedCategoryData[$category->id]['is_active'] ?? $category->is_active) ? 'checked' : ''); ?>

                                        wire:change="updateCategoryField(<?php echo e($category->id); ?>, 'is_active', $event.target.checked)"
                                        class="fi-checkbox-input rounded border-gray-300 text-primary-600 shadow-sm dark:border-white/10 dark:bg-white/5 <?php echo e(isset($editedCategoryData[$category->id]['is_active']) ? 'ring-2 ring-warning-400' : ''); ?>"
                                    >
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                    <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-inbox'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mx-auto h-10 w-10 text-gray-300 dark:text-gray-600 mb-2']); ?>
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
                                    Категорії не знайдено
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-gray-200 dark:border-white/10">
                <?php echo e($this->getCategoryItems()->links()); ?>

            </div>
        </div>

        
        <?php if($showParentCategoryModal): ?>
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" x-data x-on:keydown.escape.window="$wire.set('showParentCategoryModal', false)">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-gray-900">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Змінити батьківську категорію</h3>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Батьківська категорія</label>
                    <select wire:model="newParentCategoryId" class="fi-select-input block w-full rounded-lg border-gray-300 text-sm dark:border-white/10 dark:bg-white/5 dark:text-white">
                        <option value="">Коренева (без батьківської)</option>
                        <?php $__currentLoopData = $this->getCategories(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $title): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($id); ?>"><?php echo e($title); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <?php if (isset($component)) { $__componentOriginal6330f08526bbb3ce2a0da37da512a11f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => '$set(\'showParentCategoryModal\', false)','color' => 'gray']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => '$set(\'showParentCategoryModal\', false)','color' => 'gray']); ?>Скасувати <?php echo $__env->renderComponent(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => 'applyParentCategory','color' => 'primary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'applyParentCategory','color' => 'primary']); ?>Застосувати <?php echo $__env->renderComponent(); ?>
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
        <?php endif; ?>

        <?php endif; ?> 

        
        <?php if($activeTab === 'orders'): ?>

        
        <div class="fi-section rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div style="display:grid;gap:0.75rem;grid-template-columns:repeat(auto-fit,minmax(220px,1fr))">
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Статус замовлення</label>
                    <select wire:model.live="orderStatusFilter" class="fi-select-input block w-full rounded-lg border-gray-300 text-sm shadow-sm dark:border-white/10 dark:bg-white/5 dark:text-white">
                        <option value="">Всі</option>
                        <option value="pending">Очікує</option>
                        <option value="processing">В обробці</option>
                        <option value="shipped">Відправлено</option>
                        <option value="delivered">Доставлено</option>
                        <option value="cancelled">Скасовано</option>
                    </select>
                </div>
            </div>
        </div>

        
        <div class="fi-section rounded-xl bg-white p-3 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="flex flex-wrap items-center gap-2">
                <?php if (isset($component)) { $__componentOriginal6330f08526bbb3ce2a0da37da512a11f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => '$set(\'showOrderStatusModal\', true)','color' => 'gray','icon' => 'heroicon-m-arrow-path','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => '$set(\'showOrderStatusModal\', true)','color' => 'gray','icon' => 'heroicon-m-arrow-path','size' => 'sm']); ?>Змінити статус <?php echo $__env->renderComponent(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => 'exportOrders','color' => 'gray','icon' => 'heroicon-m-arrow-down-tray','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'exportOrders','color' => 'gray','icon' => 'heroicon-m-arrow-down-tray','size' => 'sm']); ?>Експорт CSV <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6330f08526bbb3ce2a0da37da512a11f)): ?>
<?php $attributes = $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f; ?>
<?php unset($__attributesOriginal6330f08526bbb3ce2a0da37da512a11f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6330f08526bbb3ce2a0da37da512a11f)): ?>
<?php $component = $__componentOriginal6330f08526bbb3ce2a0da37da512a11f; ?>
<?php unset($__componentOriginal6330f08526bbb3ce2a0da37da512a11f); ?>
<?php endif; ?>

                <?php if(count($selectedIds) > 0): ?>
                    <span class="ml-auto text-xs text-gray-500 dark:text-gray-400">
                        Вибрано: <strong class="text-primary-600 dark:text-primary-400"><?php echo e(count($selectedIds)); ?></strong>
                    </span>
                <?php endif; ?>
            </div>
        </div>

        
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-white/5 border-b border-gray-200 dark:border-white/10">
                            <th class="px-2 py-3 text-center w-10">
                                <input type="checkbox" wire:model.live="selectAll" wire:click="toggleSelectAll" class="fi-checkbox-input rounded border-gray-300 text-primary-600 shadow-sm dark:border-white/10 dark:bg-white/5">
                            </th>
                            <th class="px-2 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 w-16">ID</th>
                            <th class="px-2 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400">Клієнт</th>
                            <th class="px-2 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 hidden md:table-cell">Email</th>
                            <th class="px-2 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 hidden md:table-cell">Телефон</th>
                            <th class="px-2 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-400 w-28">Сума</th>
                            <th class="px-2 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 w-36">Статус</th>
                            <th class="px-2 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 w-32 hidden md:table-cell">Оплата</th>
                            <th class="px-2 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 w-32">Створено</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        <?php $__empty_1 = true; $__currentLoopData = $this->getOrderItems(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition" wire:key="order-<?php echo e($order->id); ?>">
                                <td class="px-2 py-1.5 text-center">
                                    <input type="checkbox" value="<?php echo e($order->id); ?>" wire:model.live="selectedIds" class="fi-checkbox-input rounded border-gray-300 text-primary-600 shadow-sm dark:border-white/10 dark:bg-white/5">
                                </td>
                                <td class="px-2 py-1.5 text-xs text-gray-500 dark:text-gray-400 font-mono"><?php echo e($order->id); ?></td>
                                <td class="px-2 py-1.5 text-xs text-gray-700 dark:text-gray-300"><?php echo e($order->name ?? ($order->user?->name ?? '--')); ?></td>
                                <td class="px-2 py-1.5 text-xs text-gray-500 dark:text-gray-400 hidden md:table-cell"><?php echo e($order->email ?? '--'); ?></td>
                                <td class="px-2 py-1.5 text-xs text-gray-500 dark:text-gray-400 hidden md:table-cell"><?php echo e($order->phone ?? '--'); ?></td>
                                <td class="px-2 py-1.5 text-xs text-right font-semibold text-gray-700 dark:text-gray-300"><?php echo e(number_format($order->total, 2)); ?> &#8372;</td>
                                <td class="px-2 py-1.5">
                                    <?php
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'processing' => 'info',
                                            'shipped' => 'primary',
                                            'delivered' => 'success',
                                            'cancelled' => 'danger',
                                        ];
                                        $statusLabels = [
                                            'pending' => 'Очікує',
                                            'processing' => 'В обробці',
                                            'shipped' => 'Відправлено',
                                            'delivered' => 'Доставлено',
                                            'cancelled' => 'Скасовано',
                                        ];
                                        $sc = $statusColors[$order->status] ?? 'gray';
                                    ?>
                                    <?php if (isset($component)) { $__componentOriginal986dce9114ddce94a270ab00ce6c273d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal986dce9114ddce94a270ab00ce6c273d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.badge','data' => ['color' => $sc]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($sc)]); ?>
                                        <?php echo e($statusLabels[$order->status] ?? $order->status); ?>

                                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal986dce9114ddce94a270ab00ce6c273d)): ?>
<?php $attributes = $__attributesOriginal986dce9114ddce94a270ab00ce6c273d; ?>
<?php unset($__attributesOriginal986dce9114ddce94a270ab00ce6c273d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal986dce9114ddce94a270ab00ce6c273d)): ?>
<?php $component = $__componentOriginal986dce9114ddce94a270ab00ce6c273d; ?>
<?php unset($__componentOriginal986dce9114ddce94a270ab00ce6c273d); ?>
<?php endif; ?>
                                </td>
                                <td class="px-2 py-1.5 text-xs text-gray-500 dark:text-gray-400 hidden md:table-cell"><?php echo e($order->payment_status ?? '--'); ?></td>
                                <td class="px-2 py-1.5 text-xs text-gray-500 dark:text-gray-400"><?php echo e($order->created_at?->format('d.m.Y H:i')); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="9" class="px-4 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                    <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-inbox'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mx-auto h-10 w-10 text-gray-300 dark:text-gray-600 mb-2']); ?>
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
                                    Замовлення не знайдено
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-gray-200 dark:border-white/10">
                <?php echo e($this->getOrderItems()->links()); ?>

            </div>
        </div>

        
        <?php if($showOrderStatusModal): ?>
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" x-data x-on:keydown.escape.window="$wire.set('showOrderStatusModal', false)">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-gray-900">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Змінити статус замовлень</h3>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Новий статус</label>
                    <select wire:model="orderBatchStatus" class="fi-select-input block w-full rounded-lg border-gray-300 text-sm dark:border-white/10 dark:bg-white/5 dark:text-white">
                        <option value="">Виберіть статус</option>
                        <option value="pending">Очікує</option>
                        <option value="processing">В обробці</option>
                        <option value="shipped">Відправлено</option>
                        <option value="delivered">Доставлено</option>
                        <option value="cancelled">Скасовано</option>
                    </select>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <?php if (isset($component)) { $__componentOriginal6330f08526bbb3ce2a0da37da512a11f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => '$set(\'showOrderStatusModal\', false)','color' => 'gray']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => '$set(\'showOrderStatusModal\', false)','color' => 'gray']); ?>Скасувати <?php echo $__env->renderComponent(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => 'batchChangeOrderStatus','color' => 'primary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'batchChangeOrderStatus','color' => 'primary']); ?>Застосувати <?php echo $__env->renderComponent(); ?>
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
        <?php endif; ?>

        <?php endif; ?> 

        
        <?php if($activeTab === 'reviews'): ?>

        
        <div class="fi-section rounded-xl bg-white p-3 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="flex flex-wrap items-center gap-2">
                <?php if (isset($component)) { $__componentOriginal6330f08526bbb3ce2a0da37da512a11f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => 'batchApproveReviews','color' => 'success','icon' => 'heroicon-m-check-circle','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'batchApproveReviews','color' => 'success','icon' => 'heroicon-m-check-circle','size' => 'sm']); ?>Схвалити всі вибрані <?php echo $__env->renderComponent(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => 'batchRejectReviews','wire:confirm' => 'Видалити вибрані відгуки назавжди?','color' => 'danger','icon' => 'heroicon-m-trash','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'batchRejectReviews','wire:confirm' => 'Видалити вибрані відгуки назавжди?','color' => 'danger','icon' => 'heroicon-m-trash','size' => 'sm']); ?>Видалити вибрані <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6330f08526bbb3ce2a0da37da512a11f)): ?>
<?php $attributes = $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f; ?>
<?php unset($__attributesOriginal6330f08526bbb3ce2a0da37da512a11f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6330f08526bbb3ce2a0da37da512a11f)): ?>
<?php $component = $__componentOriginal6330f08526bbb3ce2a0da37da512a11f; ?>
<?php unset($__componentOriginal6330f08526bbb3ce2a0da37da512a11f); ?>
<?php endif; ?>

                <?php if(count($selectedIds) > 0): ?>
                    <span class="ml-auto text-xs text-gray-500 dark:text-gray-400">
                        Вибрано: <strong class="text-primary-600 dark:text-primary-400"><?php echo e(count($selectedIds)); ?></strong>
                    </span>
                <?php endif; ?>
            </div>
        </div>

        
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-white/5 border-b border-gray-200 dark:border-white/10">
                            <th class="px-2 py-3 text-center w-10">
                                <input type="checkbox" wire:model.live="selectAll" wire:click="toggleSelectAll" class="fi-checkbox-input rounded border-gray-300 text-primary-600 shadow-sm dark:border-white/10 dark:bg-white/5">
                            </th>
                            <th class="px-2 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 w-16">ID</th>
                            <th class="px-2 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 min-w-[200px]">Товар</th>
                            <th class="px-2 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 w-40 hidden md:table-cell">Автор</th>
                            <th class="px-2 py-3 text-center text-xs font-semibold text-gray-600 dark:text-gray-400 w-24">Рейтинг</th>
                            <th class="px-2 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 min-w-[300px]">Текст</th>
                            <th class="px-2 py-3 text-center text-xs font-semibold text-gray-600 dark:text-gray-400 w-20">Схвалено</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        <?php $__empty_1 = true; $__currentLoopData = $this->getReviewItems(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition" wire:key="review-<?php echo e($review->id); ?>">
                                <td class="px-2 py-1.5 text-center">
                                    <input type="checkbox" value="<?php echo e($review->id); ?>" wire:model.live="selectedIds" class="fi-checkbox-input rounded border-gray-300 text-primary-600 shadow-sm dark:border-white/10 dark:bg-white/5">
                                </td>
                                <td class="px-2 py-1.5 text-xs text-gray-500 dark:text-gray-400 font-mono"><?php echo e($review->id); ?></td>
                                <td class="px-2 py-1.5 text-xs text-gray-700 dark:text-gray-300">
                                    <?php if($review->product): ?>
                                        <?php if (isset($component)) { $__componentOriginal986dce9114ddce94a270ab00ce6c273d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal986dce9114ddce94a270ab00ce6c273d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.badge','data' => ['color' => 'primary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'primary']); ?><?php echo e(Str::limit($review->product->title, 40)); ?> <?php echo $__env->renderComponent(); ?>
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
                                        <span class="text-gray-400">--</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-2 py-1.5 text-xs text-gray-600 dark:text-gray-400 hidden md:table-cell"><?php echo e($review->author_name ?? '--'); ?></td>
                                <td class="px-2 py-1.5 text-center">
                                    <span class="text-warning-500 text-xs">
                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                            <?php if($i <= $review->rating): ?>
                                                &#9733;
                                            <?php else: ?>
                                                &#9734;
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                    </span>
                                </td>
                                <td class="px-2 py-1.5 text-xs text-gray-600 dark:text-gray-400"><?php echo e(Str::limit($review->comment, 80)); ?></td>
                                <td class="px-2 py-1.5 text-center">
                                    <?php if($review->status === 'approved'): ?>
                                        <?php if (isset($component)) { $__componentOriginal986dce9114ddce94a270ab00ce6c273d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal986dce9114ddce94a270ab00ce6c273d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.badge','data' => ['color' => 'success','icon' => 'heroicon-m-check']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'success','icon' => 'heroicon-m-check']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal986dce9114ddce94a270ab00ce6c273d)): ?>
<?php $attributes = $__attributesOriginal986dce9114ddce94a270ab00ce6c273d; ?>
<?php unset($__attributesOriginal986dce9114ddce94a270ab00ce6c273d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal986dce9114ddce94a270ab00ce6c273d)): ?>
<?php $component = $__componentOriginal986dce9114ddce94a270ab00ce6c273d; ?>
<?php unset($__componentOriginal986dce9114ddce94a270ab00ce6c273d); ?>
<?php endif; ?>
                                    <?php elseif($review->status === 'pending'): ?>
                                        <?php if (isset($component)) { $__componentOriginal986dce9114ddce94a270ab00ce6c273d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal986dce9114ddce94a270ab00ce6c273d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.badge','data' => ['color' => 'warning','icon' => 'heroicon-m-clock']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'warning','icon' => 'heroicon-m-clock']); ?>
<?php echo $__env->renderComponent(); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.badge','data' => ['color' => 'danger','icon' => 'heroicon-m-x-mark']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'danger','icon' => 'heroicon-m-x-mark']); ?>
<?php echo $__env->renderComponent(); ?>
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
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                    <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-inbox'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mx-auto h-10 w-10 text-gray-300 dark:text-gray-600 mb-2']); ?>
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
                                    Відгуки не знайдено
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-gray-200 dark:border-white/10">
                <?php echo e($this->getReviewItems()->links()); ?>

            </div>
        </div>

        <?php endif; ?> 

        
        <?php if($activeTab === 'journal'): ?>

        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-white/5 border-b border-gray-200 dark:border-white/10">
                            <th class="px-3 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 w-36">Дата</th>
                            <th class="px-3 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 w-32">Користувач</th>
                            <th class="px-3 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 w-32">Дія</th>
                            <th class="px-3 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400">Опис</th>
                            <th class="px-3 py-3 text-center text-xs font-semibold text-gray-600 dark:text-gray-400 w-20">К-сть</th>
                            <th class="px-3 py-3 text-center text-xs font-semibold text-gray-600 dark:text-gray-400 w-24">Скасовано</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        <?php $__empty_1 = true; $__currentLoopData = $this->getJournalItems(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $logItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition" wire:key="log-<?php echo e($logItem->id); ?>">
                                <td class="px-3 py-2 text-xs text-gray-500 dark:text-gray-400"><?php echo e($logItem->created_at?->format('d.m.Y H:i:s')); ?></td>
                                <td class="px-3 py-2 text-xs text-gray-700 dark:text-gray-300"><?php echo e($logItem->user?->name ?? '--'); ?></td>
                                <td class="px-3 py-2">
                                    <?php
                                        $actionColors = [
                                            'price_change' => 'info',
                                            'sale' => 'warning',
                                            'status' => 'success',
                                            'category' => 'primary',
                                            'search_replace' => 'info',
                                            'delete' => 'danger',
                                            'import' => 'success',
                                        ];
                                        $ac = $actionColors[$logItem->action_type] ?? 'gray';
                                    ?>
                                    <?php if (isset($component)) { $__componentOriginal986dce9114ddce94a270ab00ce6c273d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal986dce9114ddce94a270ab00ce6c273d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.badge','data' => ['color' => $ac]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($ac)]); ?>
                                        <?php echo e($logItem->action_type); ?>

                                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal986dce9114ddce94a270ab00ce6c273d)): ?>
<?php $attributes = $__attributesOriginal986dce9114ddce94a270ab00ce6c273d; ?>
<?php unset($__attributesOriginal986dce9114ddce94a270ab00ce6c273d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal986dce9114ddce94a270ab00ce6c273d)): ?>
<?php $component = $__componentOriginal986dce9114ddce94a270ab00ce6c273d; ?>
<?php unset($__componentOriginal986dce9114ddce94a270ab00ce6c273d); ?>
<?php endif; ?>
                                </td>
                                <td class="px-3 py-2 text-xs text-gray-700 dark:text-gray-300"><?php echo e($logItem->description); ?></td>
                                <td class="px-3 py-2 text-center text-xs font-semibold text-gray-700 dark:text-gray-300"><?php echo e($logItem->affected_count); ?></td>
                                <td class="px-3 py-2 text-center">
                                    <?php if($logItem->rolled_back): ?>
                                        <?php if (isset($component)) { $__componentOriginal986dce9114ddce94a270ab00ce6c273d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal986dce9114ddce94a270ab00ce6c273d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.badge','data' => ['color' => 'warning']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'warning']); ?>Скасовано <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal986dce9114ddce94a270ab00ce6c273d)): ?>
<?php $attributes = $__attributesOriginal986dce9114ddce94a270ab00ce6c273d; ?>
<?php unset($__attributesOriginal986dce9114ddce94a270ab00ce6c273d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal986dce9114ddce94a270ab00ce6c273d)): ?>
<?php $component = $__componentOriginal986dce9114ddce94a270ab00ce6c273d; ?>
<?php unset($__componentOriginal986dce9114ddce94a270ab00ce6c273d); ?>
<?php endif; ?>
                                    <?php elseif(!empty($logItem->changes_data)): ?>
                                        <?php if (isset($component)) { $__componentOriginalf0029cce6d19fd6d472097ff06a800a1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf0029cce6d19fd6d472097ff06a800a1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.icon-button','data' => ['icon' => 'heroicon-m-arrow-uturn-left','size' => 'sm','wire:click' => 'rollbackAction('.e($logItem->id).')','wire:confirm' => 'Скасувати цю операцію?','color' => 'warning','label' => 'Скасувати']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::icon-button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'heroicon-m-arrow-uturn-left','size' => 'sm','wire:click' => 'rollbackAction('.e($logItem->id).')','wire:confirm' => 'Скасувати цю операцію?','color' => 'warning','label' => 'Скасувати']); ?>
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
                                    <?php else: ?>
                                        <span class="text-gray-400 text-xs">--</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php if(!empty($logItem->affected_ids)): ?>
                            <tr wire:key="log-detail-<?php echo e($logItem->id); ?>" x-data="{ showIds: false }">
                                <td colspan="6" class="px-3 py-0">
                                    <button @click="showIds = !showIds" class="text-xs text-primary-600 dark:text-primary-400 hover:underline py-1">
                                        <span x-text="showIds ? '&#9650; Сховати ID' : '&#9660; Показати ID (' + <?php echo e(count($logItem->affected_ids)); ?> + ')'"></span>
                                    </button>
                                    <div x-show="showIds" x-cloak class="pb-2 text-xs text-gray-500 dark:text-gray-400 font-mono">
                                        <?php echo e(implode(', ', array_slice($logItem->affected_ids, 0, 50))); ?>

                                        <?php if(count($logItem->affected_ids) > 50): ?>
                                            <span class="text-gray-400">... та ще <?php echo e(count($logItem->affected_ids) - 50); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                    <?php if (isset($component)) { $__componentOriginal643fe1b47aec0b76658e1a0200b34b2c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal643fe1b47aec0b76658e1a0200b34b2c = $attributes; } ?>
<?php $component = BladeUI\Icons\Components\Svg::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('heroicon-o-inbox'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\BladeUI\Icons\Components\Svg::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mx-auto h-10 w-10 text-gray-300 dark:text-gray-600 mb-2']); ?>
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
                                    Журнал порожній
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-gray-200 dark:border-white/10">
                <?php echo e($this->getJournalItems()->links()); ?>

            </div>
        </div>

        <?php endif; ?> 

        
        <?php if($showPreview && !empty($previewData)): ?>
        <div class="fixed inset-0 bg-black/50 z-[60] flex items-center justify-center p-4" x-data x-on:keydown.escape.window="$wire.cancelPreview()">
            <div class="bg-white dark:bg-gray-800 rounded-xl max-w-3xl w-full max-h-[80vh] overflow-y-auto p-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">
                    PREVIEW -- перегляд змін (<?php echo e(count($previewData)); ?> записів)
                </h3>
                <table class="w-full text-sm border-collapse mb-4">
                    <thead>
                        <tr class="bg-gray-100 dark:bg-gray-700">
                            <th class="border border-gray-200 dark:border-white/10 p-2 text-left text-xs font-semibold text-gray-600 dark:text-gray-400">ID</th>
                            <th class="border border-gray-200 dark:border-white/10 p-2 text-left text-xs font-semibold text-gray-600 dark:text-gray-400">Назва</th>
                            <th class="border border-gray-200 dark:border-white/10 p-2 text-left text-xs font-semibold text-gray-600 dark:text-gray-400">Було</th>
                            <th class="border border-gray-200 dark:border-white/10 p-2 text-left text-xs font-semibold text-gray-600 dark:text-gray-400">Стане</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $previewData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                            <td class="border border-gray-200 dark:border-white/10 p-2 text-xs font-mono text-gray-500 dark:text-gray-400"><?php echo e($row['id']); ?></td>
                            <td class="border border-gray-200 dark:border-white/10 p-2 text-xs text-gray-700 dark:text-gray-300"><?php echo e($row['title']); ?></td>
                            <td class="border border-gray-200 dark:border-white/10 p-2 text-xs text-danger-600 dark:text-danger-400"><?php echo e($row['old'] ?? $row['original'] ?? ''); ?></td>
                            <td class="border border-gray-200 dark:border-white/10 p-2 text-xs text-success-600 dark:text-success-400"><?php echo e($row['new']); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
                <div class="flex gap-3">
                    <?php if (isset($component)) { $__componentOriginal6330f08526bbb3ce2a0da37da512a11f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => 'confirmAndApply','wire:confirm' => 'Застосувати зміни для '.e(count($previewData)).' товарів? Цю дію можна скасувати через журнал.','color' => 'success']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'confirmAndApply','wire:confirm' => 'Застосувати зміни для '.e(count($previewData)).' товарів? Цю дію можна скасувати через журнал.','color' => 'success']); ?>
                        ЗАСТОСУВАТИ (<?php echo e(count($previewData)); ?>)
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['wire:click' => 'cancelPreview','color' => 'gray']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'cancelPreview','color' => 'gray']); ?>
                        СКАСУВАТИ
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
<?php /**PATH /home/lionex/projects/gazu-shop/resources/views/filament/pages/batch-editor.blade.php ENDPATH**/ ?>