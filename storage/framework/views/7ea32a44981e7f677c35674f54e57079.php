<?php $__env->startSection('title', 'GAZU mobile · home'); ?>

<?php
    $s = $gazuSettings ?? [];
    $kicker = $s['gazu_mobile_hero_kicker'] ?? ($shopStats['products_label'] ?? 'Каталог автозапчастин');
    $titleHtml = $s['gazu_mobile_hero_title_html'] ?? 'Знайди деталь за <span style="color:var(--gazu-blue)">Артикул</span>';
    $catsTitle = $s['gazu_mobile_categories_title'] ?? 'Категорії';
    $hitsTitle = $s['gazu_mobile_hits_title'] ?? 'Хіти';

    // Реальні корінні категорії (перші 4) з тих самих даних, що мега-меню
    $tree = app(\App\Services\Gazu\MegaMenuBuilder::class)->build();
    $kinds = ['oil','pad','shock','spark','bulb','filter','bearing','wiper'];
    $mobCats = [];
    foreach (array_slice((array) $tree, 0, 4) as $i => $node) {
        $mobCats[] = [
            'name' => $node['label'] ?? '—',
            'kind' => $kinds[$i % count($kinds)],
            'url'  => ! empty($node['slug']) ? url('/'.$node['slug']) : route('gazu.catalog'),
        ];
    }
?>

<?php $__env->startSection('content'); ?>
<div class="max-w-[420px] mx-auto py-4 px-4 pb-20">
    <div class="bg-[var(--gazu-mist)] rounded-xl p-5 mb-4">
        <div class="gazu-mono text-[10px] text-[var(--gazu-blue)] tracking-widest uppercase mb-2"><?php echo e($kicker); ?></div>
        <h1 class="gazu-display text-2xl font-bold leading-tight m-0"><?php echo $titleHtml; ?></h1>
        <form action="<?php echo e(route('gazu.search')); ?>" class="mt-4 flex bg-[var(--gazu-surface)] border border-[var(--gazu-ink)] rounded-md overflow-hidden">
            <input name="q" placeholder="Введіть код" class="flex-1 px-3 py-2.5 border-0 outline-none gazu-mono text-sm">
            <button type="submit" class="px-4 bg-[var(--gazu-ink)] text-[var(--gazu-on-brand)] border-0 cursor-pointer">
                <?php if (isset($component)) { $__componentOriginal6ccaa7247ed520b12783ad61ab722d64 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6ccaa7247ed520b12783ad61ab722d64 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.icon','data' => ['name' => 'search','size' => '16']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'search','size' => '16']); ?>
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
        </form>
    </div>

    <?php if(! empty($mobCats)): ?>
        <h2 class="gazu-display text-lg font-semibold mb-2"><?php echo e($catsTitle); ?></h2>
        <div class="grid grid-cols-2 gap-2 mb-5">
            <?php $__currentLoopData = $mobCats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a wire:navigate href="<?php echo e($c['url']); ?>" class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg p-3 no-underline text-[var(--gazu-ink)] flex items-center gap-2">
                    <div class="w-10 h-10 bg-[var(--gazu-paper)] rounded-md flex items-center justify-center">
                        <?php if (isset($component)) { $__componentOriginale68023f03052ea26bcc9e709ab0711bb = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginale68023f03052ea26bcc9e709ab0711bb = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.part-image','data' => ['kind' => ''.e($c['kind']).'','size' => '32']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.part-image'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['kind' => ''.e($c['kind']).'','size' => '32']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginale68023f03052ea26bcc9e709ab0711bb)): ?>
<?php $attributes = $__attributesOriginale68023f03052ea26bcc9e709ab0711bb; ?>
<?php unset($__attributesOriginale68023f03052ea26bcc9e709ab0711bb); ?>
<?php endif; ?>
<?php if (isset($__componentOriginale68023f03052ea26bcc9e709ab0711bb)): ?>
<?php $component = $__componentOriginale68023f03052ea26bcc9e709ab0711bb; ?>
<?php unset($__componentOriginale68023f03052ea26bcc9e709ab0711bb); ?>
<?php endif; ?>
                    </div>
                    <span class="font-medium text-sm"><?php echo e($c['name']); ?></span>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>

    <h2 class="gazu-display text-lg font-semibold mb-2"><?php echo e($hitsTitle); ?></h2>
    <div class="grid grid-cols-2 gap-2.5">
        <?php $__currentLoopData = $products->take(4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if (isset($component)) { $__componentOriginalb3aad9b8a2236f9a320278758e8a5f6c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb3aad9b8a2236f9a320278758e8a5f6c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.product-card','data' => ['p' => $p,'compact' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.product-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['p' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($p),'compact' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb3aad9b8a2236f9a320278758e8a5f6c)): ?>
<?php $attributes = $__attributesOriginalb3aad9b8a2236f9a320278758e8a5f6c; ?>
<?php unset($__attributesOriginalb3aad9b8a2236f9a320278758e8a5f6c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb3aad9b8a2236f9a320278758e8a5f6c)): ?>
<?php $component = $__componentOriginalb3aad9b8a2236f9a320278758e8a5f6c; ?>
<?php unset($__componentOriginalb3aad9b8a2236f9a320278758e8a5f6c); ?>
<?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>

<?php echo $__env->make('gazu.partials.mobile-nav', ['active' => 'home'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('gazu.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/lionex/projects/gazu-shop/resources/views/gazu/mobile/home.blade.php ENDPATH**/ ?>