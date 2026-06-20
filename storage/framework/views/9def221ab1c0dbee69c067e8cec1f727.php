<?php
    $groups = \App\Support\DashboardMetrics::arrangedGroups();
?>

<?php if (isset($component)) { $__componentOriginal166a02a7c5ef5a9331faf66fa665c256 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal166a02a7c5ef5a9331faf66fa665c256 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-panels::components.page.index','data' => ['class' => 'fi-dashboard-page']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament-panels::page'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'fi-dashboard-page']); ?>
    <style>
        .gz-bar{display:flex;align-items:center;justify-content:space-between;gap:.75rem;margin-bottom:.25rem;flex-wrap:wrap}
        .gz-bar-hint{font-size:.8rem;color:rgb(113 113 122);display:flex;align-items:center;gap:.4rem}
        .dark .gz-bar-hint{color:rgb(161 161 170)}
        .gz-reset{font-size:.78rem;font-weight:600;color:rgb(82 82 91);background:rgb(244 244 245);border:1px solid rgb(228 228 231);border-radius:.5rem;padding:.3rem .7rem;cursor:pointer;transition:.15s;text-decoration:none;display:inline-flex;align-items:center;gap:.35rem}
        .gz-reset:hover{background:rgb(228 228 231)}
        .dark .gz-reset{color:rgb(212 212 216);background:rgb(39 39 42);border-color:rgb(63 63 70)}
        .dark .gz-reset:hover{background:rgb(63 63 70)}

        .gz-group-title{margin:1.1rem 0 .15rem;font-size:.95rem;font-weight:700;color:rgb(39 39 42);letter-spacing:-.01em}
        .dark .gz-group-title{color:#e4e4e7}
        .gz-group-title:first-of-type{margin-top:.25rem}

        .gz-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(215px,1fr));gap:.75rem}
        .gz-card{position:relative;display:flex;flex-direction:column;gap:.35rem;padding:.95rem 1rem;background:#fff;border:1px solid rgb(228 228 231);border-radius:.85rem;box-shadow:0 1px 2px rgba(0,0,0,.04);user-select:none;transition:box-shadow .15s,border-color .15s;overflow:hidden}
        .gz-card:hover{box-shadow:0 4px 14px rgba(0,0,0,.08);border-color:rgb(212 212 216)}
        .dark .gz-card{background:rgb(24 24 27);border-color:rgb(39 39 42);box-shadow:none}
        .dark .gz-card:hover{border-color:rgb(63 63 70)}
        .gz-card::before{content:"";position:absolute;left:0;top:0;bottom:0;width:4px;background:var(--gz-accent,#71717a)}

        .gz-top{display:flex;align-items:center;justify-content:space-between;gap:.5rem}
        .gz-ico{display:flex;align-items:center;justify-content:center;width:38px;height:38px;border-radius:.6rem;background:color-mix(in srgb,var(--gz-accent,#71717a) 14%,transparent);color:var(--gz-accent,#71717a);flex-shrink:0}
        .gz-ico svg{width:20px;height:20px}

        .gz-val{font-size:1.65rem;line-height:1.1;font-weight:700;color:rgb(24 24 27);letter-spacing:-.02em}
        .dark .gz-val{color:#fafafa}
        .gz-label{font-size:.82rem;font-weight:600;color:rgb(63 63 70)}
        .dark .gz-label{color:rgb(212 212 216)}
        .gz-sub{font-size:.74rem;color:rgb(113 113 122)}
        .dark .gz-sub{color:rgb(161 161 170)}
        .gz-spark{margin-top:.15rem}
        .gz-spark svg{display:block;width:100%;height:26px;overflow:visible}

        .gz-c-success{--gz-accent:#16a34a}
        .gz-c-warning{--gz-accent:#d97706}
        .gz-c-danger{--gz-accent:#dc2626}
        .gz-c-info{--gz-accent:#2563eb}
        .gz-c-primary{--gz-accent:rgb(var(--primary-600,37 99 235))}
        .gz-c-gray{--gz-accent:#71717a}

        @media (max-width:640px){.gz-grid{grid-template-columns:repeat(auto-fill,minmax(150px,1fr))}.gz-val{font-size:1.4rem}}
    </style>

    <div class="gz-bar">
        <div class="gz-bar-hint">
            <?php if (isset($component)) { $__componentOriginalbfc641e0710ce04e5fe02876ffc6f950 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbfc641e0710ce04e5fe02876ffc6f950 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.icon','data' => ['icon' => 'heroicon-m-squares-2x2','style' => 'width:1rem;height:1rem']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'heroicon-m-squares-2x2','style' => 'width:1rem;height:1rem']); ?>
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
            Видимість, порядок і групи карток — у налаштуваннях дашборду
        </div>
        <?php if(\Illuminate\Support\Facades\Route::has('filament.admin.pages.dashboard-settings')): ?>
            <a href="<?php echo e(route('filament.admin.pages.dashboard-settings')); ?>" class="gz-reset" wire:navigate>
                <?php if (isset($component)) { $__componentOriginalbfc641e0710ce04e5fe02876ffc6f950 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbfc641e0710ce04e5fe02876ffc6f950 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.icon','data' => ['icon' => 'heroicon-o-cog-6-tooth','style' => 'width:.95rem;height:.95rem']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'heroicon-o-cog-6-tooth','style' => 'width:.95rem;height:.95rem']); ?>
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
                Налаштувати дашборд
            </a>
        <?php endif; ?>
    </div>

    <?php $__empty_1 = true; $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="gz-group-title"><?php echo e($group['label']); ?></div>
        <div class="gz-grid">
            <?php $__currentLoopData = $group['cards']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="gz-card gz-c-<?php echo e($m['color'] ?? 'gray'); ?>">
                    <div class="gz-top">
                        <div class="gz-ico">
                            <?php if (isset($component)) { $__componentOriginalbfc641e0710ce04e5fe02876ffc6f950 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalbfc641e0710ce04e5fe02876ffc6f950 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.icon','data' => ['icon' => $m['icon'] ?? 'heroicon-o-chart-bar']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($m['icon'] ?? 'heroicon-o-chart-bar')]); ?>
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
                        </div>
                    </div>
                    <div class="gz-val"><?php echo e($m['value']); ?></div>
                    <div class="gz-label"><?php echo e($m['label']); ?></div>
                    <?php if(!empty($m['sub'])): ?>
                        <div class="gz-sub"><?php echo e($m['sub']); ?></div>
                    <?php endif; ?>
                    <?php if(!empty($m['spark']) && is_array($m['spark'])): ?>
                        <?php
                            $vals = array_map('floatval', $m['spark']);
                            $max = max($vals) ?: 1; $min = min($vals);
                            $range = ($max - $min) ?: 1; $n = count($vals); $w = 100; $h = 26;
                            $pts = [];
                            foreach ($vals as $k => $v) {
                                $x = $n > 1 ? round($k / ($n - 1) * $w, 2) : 0;
                                $y = round($h - (($v - $min) / $range) * $h, 2);
                                $pts[] = "$x,$y";
                            }
                            $line = implode(' ', $pts);
                        ?>
                        <div class="gz-spark">
                            <svg viewBox="0 0 <?php echo e($w); ?> <?php echo e($h); ?>" preserveAspectRatio="none">
                                <polyline points="<?php echo e($line); ?>" fill="none" stroke="var(--gz-accent)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke"/>
                            </svg>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        
        <?php if($group['key'] === 'sales'): ?>
            <div style="margin-top:1rem">
                <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split(\App\Filament\Widgets\LatestOrders::class);

$__html = app('livewire')->mount($__name, $__params, 'lw-1500760794-0', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
            </div>
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="gz-bar-hint" style="margin-top:1rem">Усі картки сховані — увімкніть у «Налаштувати дашборд».</div>
    <?php endif; ?>

    
    <?php if (isset($component)) { $__componentOriginal7259e9ea993f43cfa75aaa166dfee38d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7259e9ea993f43cfa75aaa166dfee38d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-widgets::components.widgets','data' => ['columns' => $this->getColumns(),'data' => $this->getWidgetData(),'widgets' => $this->getVisibleWidgets()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament-widgets::widgets'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['columns' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($this->getColumns()),'data' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($this->getWidgetData()),'widgets' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($this->getVisibleWidgets())]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7259e9ea993f43cfa75aaa166dfee38d)): ?>
<?php $attributes = $__attributesOriginal7259e9ea993f43cfa75aaa166dfee38d; ?>
<?php unset($__attributesOriginal7259e9ea993f43cfa75aaa166dfee38d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7259e9ea993f43cfa75aaa166dfee38d)): ?>
<?php $component = $__componentOriginal7259e9ea993f43cfa75aaa166dfee38d; ?>
<?php unset($__componentOriginal7259e9ea993f43cfa75aaa166dfee38d); ?>
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
<?php /**PATH /home/lionex/projects/gazu-shop/resources/views/filament/pages/gazu-dashboard.blade.php ENDPATH**/ ?>