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
        
        <?php if (isset($component)) { $__componentOriginalee08b1367eba38734199cf7829b1d1e9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalee08b1367eba38734199cf7829b1d1e9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.section.index','data' => ['icon' => 'heroicon-o-document-text','iconColor' => 'primary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'heroicon-o-document-text','icon-color' => 'primary']); ?>
             <?php $__env->slot('heading', null, []); ?> SEO Шаблони та Налаштування <?php $__env->endSlot(); ?>
             <?php $__env->slot('description', null, []); ?> Налаштуйте шаблони для автоматичної генерації SEO метаданих <?php $__env->endSlot(); ?>
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
            $stats = [
                'total_products' => \App\Models\Product::count(),
                'products_with_seo' => \App\Models\SeoMeta::where('seoable_type', \App\Models\Product::class)->count(),
                'total_categories' => \App\Models\Category::count(),
                'categories_with_seo' => \App\Models\SeoMeta::where('seoable_type', \App\Models\Category::class)->count(),
                'total_pages' => \App\Models\SeoMeta::whereNotNull('page_type')->count(),
                'auto_generated' => \App\Models\SeoMeta::where('auto_generated', true)->count(),
            ];
            $productsCoverage = $stats['total_products'] > 0 ? ($stats['products_with_seo'] / $stats['total_products']) * 100 : 0;
            $categoriesCoverage = $stats['total_categories'] > 0 ? ($stats['categories_with_seo'] / $stats['total_categories']) * 100 : 0;
        ?>

        <?php if (isset($component)) { $__componentOriginalee08b1367eba38734199cf7829b1d1e9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalee08b1367eba38734199cf7829b1d1e9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.section.index','data' => ['icon' => 'heroicon-o-chart-bar','iconColor' => 'success']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'heroicon-o-chart-bar','icon-color' => 'success']); ?>
             <?php $__env->slot('heading', null, []); ?> Поточна статистика SEO <?php $__env->endSlot(); ?>

            <div class="overflow-hidden rounded-lg bg-gray-200 dark:bg-white/10" style="display:grid;gap:1px;grid-template-columns:repeat(auto-fit,minmax(110px,1fr))">
                <div class="bg-white p-3 dark:bg-gray-900">
                    <div class="text-xl font-bold text-primary-600 dark:text-primary-400"><?php echo e($stats['total_products']); ?></div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Всього товарів</div>
                </div>
                <div class="bg-white p-3 dark:bg-gray-900">
                    <div class="text-xl font-bold text-success-600 dark:text-success-400"><?php echo e($stats['products_with_seo']); ?></div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Товари з SEO</div>
                </div>
                <div class="bg-white p-3 dark:bg-gray-900">
                    <div class="text-xl font-bold text-info-600 dark:text-info-400"><?php echo e($stats['total_categories']); ?></div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Всього категорій</div>
                </div>
                <div class="bg-white p-3 dark:bg-gray-900">
                    <div class="text-xl font-bold text-warning-600 dark:text-warning-400"><?php echo e($stats['categories_with_seo']); ?></div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Категорії з SEO</div>
                </div>
            </div>

            
            <div class="mt-4 space-y-3">
                <div>
                    <div class="mb-1 flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Покриття товарів</span>
                        <span class="text-sm text-gray-600 dark:text-gray-400"><?php echo e(round($productsCoverage, 1)); ?>%</span>
                    </div>
                    <div class="h-2 w-full rounded-full bg-gray-200 dark:bg-white/10">
                        <div class="h-2 rounded-full bg-success-500" style="width: <?php echo e($productsCoverage); ?>%"></div>
                    </div>
                </div>
                <div>
                    <div class="mb-1 flex justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Покриття категорій</span>
                        <span class="text-sm text-gray-600 dark:text-gray-400"><?php echo e(round($categoriesCoverage, 1)); ?>%</span>
                    </div>
                    <div class="h-2 w-full rounded-full bg-gray-200 dark:bg-white/10">
                        <div class="h-2 rounded-full bg-info-500" style="width: <?php echo e($categoriesCoverage); ?>%"></div>
                    </div>
                </div>
            </div>

            
            <div class="mt-4 flex flex-wrap items-center gap-2">
                <?php if (isset($component)) { $__componentOriginal6330f08526bbb3ce2a0da37da512a11f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['tag' => 'a','href' => ''.e(route('filament.admin.pages.seo-management')).'','icon' => 'heroicon-o-rocket-launch','color' => 'primary','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['tag' => 'a','href' => ''.e(route('filament.admin.pages.seo-management')).'','icon' => 'heroicon-o-rocket-launch','color' => 'primary','size' => 'sm']); ?>
                    Генерувати SEO
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['tag' => 'a','href' => ''.e(route('filament.admin.resources.seo-metas.index')).'','icon' => 'heroicon-o-chart-bar','color' => 'gray','size' => 'sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['tag' => 'a','href' => ''.e(route('filament.admin.resources.seo-metas.index')).'','icon' => 'heroicon-o-chart-bar','color' => 'gray','size' => 'sm']); ?>
                    Переглянути всі SEO
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
                <?php if($stats['total_products'] > 0 && $stats['products_with_seo'] == 0): ?>
                    <?php if (isset($component)) { $__componentOriginal986dce9114ddce94a270ab00ce6c273d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal986dce9114ddce94a270ab00ce6c273d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.badge','data' => ['color' => 'warning','icon' => 'heroicon-o-exclamation-triangle']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'warning','icon' => 'heroicon-o-exclamation-triangle']); ?>
                        Немає SEO для товарів
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
            </div>

            
            <?php if($stats['products_with_seo'] > $stats['total_products'] || $stats['categories_with_seo'] > $stats['total_categories']): ?>
                <div class="mt-4">
                    <?php if (isset($component)) { $__componentOriginalee08b1367eba38734199cf7829b1d1e9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalee08b1367eba38734199cf7829b1d1e9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.section.index','data' => ['icon' => 'heroicon-o-exclamation-circle','iconColor' => 'danger']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'heroicon-o-exclamation-circle','icon-color' => 'danger']); ?>
                         <?php $__env->slot('heading', null, []); ?> Виявлено дублікати в SEO записах <?php $__env->endSlot(); ?>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Товарів з SEO (<?php echo e($stats['products_with_seo']); ?>) більше ніж загальна кількість (<?php echo e($stats['total_products']); ?>).
                            Натисніть «Очистити весь SEO», а потім «Повна перегенерація» для виправлення.
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
                </div>
            <?php elseif($stats['total_products'] > 0 && $stats['products_with_seo'] / $stats['total_products'] < 0.5): ?>
                <div class="mt-4">
                    <?php if (isset($component)) { $__componentOriginalee08b1367eba38734199cf7829b1d1e9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalee08b1367eba38734199cf7829b1d1e9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.section.index','data' => ['icon' => 'heroicon-o-exclamation-triangle','iconColor' => 'warning']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'heroicon-o-exclamation-triangle','icon-color' => 'warning']); ?>
                         <?php $__env->slot('heading', null, []); ?> Низьке покриття SEO <?php $__env->endSlot(); ?>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Поточне покриття <?php echo e(round(($stats['products_with_seo'] / $stats['total_products']) * 100, 1)); ?>%.
                            Рекомендуємо згенерувати SEO для всіх товарів.
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
                </div>
            <?php elseif($stats['total_products'] > 0 && $stats['products_with_seo'] == $stats['total_products']): ?>
                <div class="mt-4">
                    <?php if (isset($component)) { $__componentOriginalee08b1367eba38734199cf7829b1d1e9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalee08b1367eba38734199cf7829b1d1e9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.section.index','data' => ['icon' => 'heroicon-o-check-circle','iconColor' => 'success']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'heroicon-o-check-circle','icon-color' => 'success']); ?>
                         <?php $__env->slot('heading', null, []); ?> Відмінно <?php $__env->endSlot(); ?>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Всі товари мають SEO метадані.
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.section.index','data' => ['icon' => 'heroicon-o-eye','iconColor' => 'info']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'heroicon-o-eye','icon-color' => 'info']); ?>
             <?php $__env->slot('heading', null, []); ?> Приклади генерації <?php $__env->endSlot(); ?>
            <div style="display:grid;gap:1.5rem;grid-template-columns:repeat(auto-fit,minmax(340px,1fr))">
                <div class="rounded-lg p-4 ring-1 ring-gray-950/5 dark:ring-white/10">
                    <h4 class="mb-2 font-medium text-gray-950 dark:text-white">Приклад категорії</h4>
                    <p class="mb-1 text-sm text-gray-600 dark:text-gray-400"><strong>Заголовок:</strong> Смартфони | SimpleShop</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400"><strong>Опис:</strong> Великий вибір товарів у категорії Смартфони. Швидка доставка по Україні.</p>
                </div>
                <div class="rounded-lg p-4 ring-1 ring-gray-950/5 dark:ring-white/10">
                    <h4 class="mb-2 font-medium text-gray-950 dark:text-white">Приклад товару</h4>
                    <p class="mb-1 text-sm text-gray-600 dark:text-gray-400"><strong>Заголовок:</strong> Купити iPhone 15 Pro за 45000 грн | SimpleShop</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400"><strong>Опис:</strong> Купити iPhone 15 Pro за найкращою ціною 45000 грн. Новий флагман Apple.</p>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.section.index','data' => ['icon' => 'heroicon-o-code-bracket','iconColor' => 'warning']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'heroicon-o-code-bracket','icon-color' => 'warning']); ?>
             <?php $__env->slot('heading', null, []); ?> Доступні змінні для шаблонів <?php $__env->endSlot(); ?>
            <div style="display:grid;gap:1.5rem;grid-template-columns:repeat(auto-fit,minmax(220px,1fr))">
                <div>
                    <h4 class="mb-2 font-medium text-gray-950 dark:text-white">Категорії</h4>
                    <ul class="space-y-1 text-sm text-gray-600 dark:text-gray-400">
                        <li>• <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">%s</code> — назва категорії</li>
                        <li>• <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">%d</code> — кількість товарів</li>
                        <li>• <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">%site%</code> — назва сайту</li>
                    </ul>
                </div>
                <div>
                    <h4 class="mb-2 font-medium text-gray-950 dark:text-white">Товари</h4>
                    <ul class="space-y-1 text-sm text-gray-600 dark:text-gray-400">
                        <li>• <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">%s</code> — назва товару</li>
                        <li>• <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">%price%</code> — ціна</li>
                        <li>• <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">%category%</code> — категорія</li>
                        <li>• <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">%brand%</code> — бренд</li>
                    </ul>
                </div>
                <div>
                    <h4 class="mb-2 font-medium text-gray-950 dark:text-white">Сторінки</h4>
                    <ul class="space-y-1 text-sm text-gray-600 dark:text-gray-400">
                        <li>• <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">%s</code> — назва сторінки</li>
                        <li>• <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">%site%</code> — назва сайту</li>
                        <li>• <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs dark:bg-white/10">%year%</code> — поточний рік</li>
                    </ul>
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

        
        <form wire:submit="save">
            <?php echo e($this->form); ?>


            <div class="mt-6 flex flex-wrap gap-4">
                <?php if (isset($component)) { $__componentOriginal6330f08526bbb3ce2a0da37da512a11f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['type' => 'submit','icon' => 'heroicon-o-check']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit','icon' => 'heroicon-o-check']); ?>
                    Зберегти шаблони
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['type' => 'button','wire:click' => 'resetToDefaults','color' => 'gray','icon' => 'heroicon-o-arrow-path']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'button','wire:click' => 'resetToDefaults','color' => 'gray','icon' => 'heroicon-o-arrow-path']); ?>
                    Скинути до стандартних
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
        </form>

        
        <?php if (isset($component)) { $__componentOriginalee08b1367eba38734199cf7829b1d1e9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalee08b1367eba38734199cf7829b1d1e9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.section.index','data' => ['icon' => 'heroicon-o-light-bulb','iconColor' => 'info']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'heroicon-o-light-bulb','icon-color' => 'info']); ?>
             <?php $__env->slot('heading', null, []); ?> SEO Рекомендації Google 2025 <?php $__env->endSlot(); ?>
            <div style="display:grid;gap:1.5rem;grid-template-columns:repeat(auto-fit,minmax(340px,1fr))">
                <div>
                    <h4 class="mb-2 font-medium text-gray-950 dark:text-white">Заголовки (Title)</h4>
                    <ul class="space-y-1 text-sm text-gray-600 dark:text-gray-400">
                        <li>• Максимум 60 символів</li>
                        <li>• Ключові слова на початку</li>
                        <li>• Унікальність для кожної сторінки</li>
                        <li>• Бренд в кінці</li>
                    </ul>
                </div>
                <div>
                    <h4 class="mb-2 font-medium text-gray-950 dark:text-white">Описи (Description)</h4>
                    <ul class="space-y-1 text-sm text-gray-600 dark:text-gray-400">
                        <li>• 150-160 символів</li>
                        <li>• Заклик до дії (CTA)</li>
                        <li>• Переваги для користувача</li>
                        <li>• Ключові слова природно</li>
                    </ul>
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
<?php /**PATH /home/lionex/projects/gazu-shop/resources/views/filament/pages/seo-templates.blade.php ENDPATH**/ ?>