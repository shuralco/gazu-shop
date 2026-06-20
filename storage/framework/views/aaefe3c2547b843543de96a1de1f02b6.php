<?php
    // Контекстна кнопка довідки: за поточним admin-розділом (admin/{seg}) шукаємо
    // статтю з match_path і ведемо одразу на неї; інакше — на загальну довідку.
    $helpUrl = url('/admin/help');
    try {
        $seg = request()->segment(2); // admin/{seg}/...
        if ($seg && $seg !== 'help' && \Illuminate\Support\Facades\Schema::hasTable('help_articles')) {
            $slug = \App\Models\HelpArticle::query()->where('is_active', true)
                ->where('match_path', $seg)->value('slug');
            if ($slug) {
                $helpUrl = url('/admin/help?topic='.$slug);
            }
        }
    } catch (\Throwable $e) {
        // лишаємо загальний URL
    }
?>
<a href="<?php echo e($helpUrl); ?>"
   title="Інструкції / довідка по розділу"
   class="flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-primary-600 dark:text-gray-300 dark:hover:bg-white/5 dark:hover:text-primary-400">
    <?php if (isset($component)) { $__componentOriginalbfc641e0710ce04e5fe02876ffc6f950 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbfc641e0710ce04e5fe02876ffc6f950 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.icon','data' => ['icon' => 'heroicon-o-question-mark-circle','class' => 'h-5 w-5']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'heroicon-o-question-mark-circle','class' => 'h-5 w-5']); ?>
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
    <span class="hidden md:inline">Довідка</span>
</a>
<?php /**PATH /home/lionex/projects/gazu-shop/resources/views/filament/partials/help-button.blade.php ENDPATH**/ ?>