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
        <?php
            $stats = $this->getSeoStats();
            $recentUpdates = $this->getRecentSeoUpdates();
            $issues = $this->getSeoIssues();
        ?>

        <!-- Hero Section -->
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
             <?php $__env->slot('heading', null, []); ?> 🎯 SEO Управління <?php $__env->endSlot(); ?>
             <?php $__env->slot('description', null, []); ?> Централізоване управління всіма SEO параметрами сайту <?php $__env->endSlot(); ?>

            <!-- Action Buttons -->
            <div style="display:grid;gap:0.75rem;grid-template-columns:repeat(auto-fit,minmax(160px,1fr))">
                <?php echo e($this->generate_all_seo); ?>

                <?php echo e($this->generate_categories_seo); ?>

                <?php echo e($this->generate_products_seo); ?>

                <?php echo e($this->generate_pages_seo); ?>

                <?php echo e($this->clear_seo_cache); ?>

                <?php echo e($this->clear_all_cache); ?>

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

        <!-- Stats Grid -->
        <div style="display:grid;gap:1rem;grid-template-columns:repeat(auto-fit,minmax(160px,1fr))">
            <div class="bg-white dark:bg-white/5 p-4 rounded-lg ring-1 ring-gray-950/10 dark:ring-white/10">
                <div class="text-2xl font-bold text-primary-600"><?php echo e($stats['total_seo']); ?></div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Всього SEO записів</div>
            </div>

            <div class="bg-white dark:bg-white/5 p-4 rounded-lg ring-1 ring-gray-950/10 dark:ring-white/10">
                <div class="text-2xl font-bold text-success-600"><?php echo e($stats['categories_with_seo']); ?>/<?php echo e($stats['categories_total']); ?></div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Категорії з SEO</div>
            </div>

            <div class="bg-white dark:bg-white/5 p-4 rounded-lg ring-1 ring-gray-950/10 dark:ring-white/10">
                <div class="text-2xl font-bold text-info-600"><?php echo e($stats['products_with_seo']); ?>/<?php echo e($stats['products_total']); ?></div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Товари з SEO</div>
            </div>

            <div class="bg-white dark:bg-white/5 p-4 rounded-lg ring-1 ring-gray-950/10 dark:ring-white/10">
                <div class="text-2xl font-bold text-warning-600"><?php echo e($stats['static_pages']); ?></div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Статичні сторінки</div>
            </div>
        </div>

        <!-- Quick Links -->
        <div style="display:grid;gap:1rem;grid-template-columns:repeat(auto-fit,minmax(220px,1fr))">
            <a href="<?php echo e(url('/admin/seo-metas')); ?>" class="block p-4 rounded-lg bg-primary-50 hover:bg-primary-100 ring-1 ring-primary-600/20 transition dark:bg-primary-400/10 dark:hover:bg-primary-400/20 dark:ring-primary-400/20">
                <div class="font-medium text-primary-700 dark:text-primary-300">📊 SEO Meta записи</div>
                <div class="text-sm text-primary-600 dark:text-primary-400">CRUD таблиця всіх SEO даних</div>
            </a>

            <a href="<?php echo e(url('/admin/products')); ?>" class="block p-4 rounded-lg bg-success-50 hover:bg-success-100 ring-1 ring-success-600/20 transition dark:bg-success-400/10 dark:hover:bg-success-400/20 dark:ring-success-400/20">
                <div class="font-medium text-success-700 dark:text-success-300">📦 Товари</div>
                <div class="text-sm text-success-600 dark:text-success-400">Редагування SEO товарів</div>
            </a>

            <a href="<?php echo e(url('/admin/categories')); ?>" class="block p-4 rounded-lg bg-info-50 hover:bg-info-100 ring-1 ring-info-600/20 transition dark:bg-info-400/10 dark:hover:bg-info-400/20 dark:ring-info-400/20">
                <div class="font-medium text-info-700 dark:text-info-300">🏷️ Категорії</div>
                <div class="text-sm text-info-600 dark:text-info-400">Редагування SEO категорій</div>
            </a>
        </div>

        <!-- SEO Issues -->
        <?php if(!empty($issues)): ?>
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
             <?php $__env->slot('heading', null, []); ?> ⚠️ Проблеми та попередження <?php $__env->endSlot(); ?>
            <div class="space-y-2">
                <?php $__currentLoopData = $issues; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $issue): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="p-3 rounded-lg <?php echo e($issue['type'] === 'error' ? 'bg-danger-50 ring-1 ring-danger-600/20 dark:bg-danger-400/10 dark:ring-danger-400/20' : 'bg-warning-50 ring-1 ring-warning-600/20 dark:bg-warning-400/10 dark:ring-warning-400/20'); ?>">
                    <div class="text-sm <?php echo e($issue['type'] === 'error' ? 'text-danger-700 dark:text-danger-400' : 'text-warning-700 dark:text-warning-400'); ?>">
                        <?php echo e($issue['message']); ?>

                    </div>
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
        <?php endif; ?>

        <!-- Recent Updates -->
        <?php if (isset($component)) { $__componentOriginalee08b1367eba38734199cf7829b1d1e9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalee08b1367eba38734199cf7829b1d1e9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.section.index','data' => ['icon' => 'heroicon-o-clock']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'heroicon-o-clock']); ?>
             <?php $__env->slot('heading', null, []); ?> 🕒 Останні оновлення <?php $__env->endSlot(); ?>
            <div class="space-y-2 max-h-60 overflow-y-auto">
                <?php $__currentLoopData = $recentUpdates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $update): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-white/5 rounded-lg">
                    <div style="flex:1 1 0%">
                        <div class="font-medium text-sm"><?php echo e($update['title']); ?></div>
                        <div class="text-xs text-gray-500 dark:text-gray-400"><?php echo e($update['type']); ?> - <?php echo e($update['updated_at']->diffForHumans()); ?></div>
                    </div>
                    <?php if (isset($component)) { $__componentOriginal986dce9114ddce94a270ab00ce6c273d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal986dce9114ddce94a270ab00ce6c273d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.badge','data' => ['color' => 'primary']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'primary']); ?><?php echo e($update['language']); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal986dce9114ddce94a270ab00ce6c273d)): ?>
<?php $attributes = $__attributesOriginal986dce9114ddce94a270ab00ce6c273d; ?>
<?php unset($__attributesOriginal986dce9114ddce94a270ab00ce6c273d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal986dce9114ddce94a270ab00ce6c273d)): ?>
<?php $component = $__componentOriginal986dce9114ddce94a270ab00ce6c273d; ?>
<?php unset($__componentOriginal986dce9114ddce94a270ab00ce6c273d); ?>
<?php endif; ?>
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
<?php endif; ?><?php /**PATH /home/lionex/projects/gazu-shop/resources/views/filament/pages/seo-management.blade.php ENDPATH**/ ?>