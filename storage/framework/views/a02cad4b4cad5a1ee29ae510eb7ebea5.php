
<div class="layout-builder-zone layout-builder-zone--<?php echo e(\Illuminate\Support\Str::slug($zone)); ?>" data-lb-zone="<?php echo e($zone); ?>">
    <?php $__currentLoopData = $blocks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $block): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            $cfg = is_array($block->config) ? $block->config : [];
            $type = $block->type ?: 'html';
        ?>

        <?php if($type === 'banner'): ?>
            <?php
                $img = $cfg['image_url'] ?? null;
                $link = $cfg['link_url'] ?? null;
                $alt = $cfg['alt'] ?? ($block->title ?? '');
            ?>
            <?php if($img): ?>
                <div class="lb-block lb-block--banner" style="margin: 16px 0;">
                    <div class="gazu-container">
                        <?php if($link): ?>
                            <a href="<?php echo e($link); ?>" class="block no-underline">
                                <img src="<?php echo e($img); ?>" alt="<?php echo e($alt); ?>" loading="lazy"
                                     style="width:100%;height:auto;border-radius:10px;display:block;">
                            </a>
                        <?php else: ?>
                            <img src="<?php echo e($img); ?>" alt="<?php echo e($alt); ?>" loading="lazy"
                                 style="width:100%;height:auto;border-radius:10px;display:block;">
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

        <?php elseif($type === 'featured'): ?>
            <?php
                $limit = (int) ($cfg['limit'] ?? 4);
                $limit = max(1, min($limit, 12));
                $source = $cfg['source'] ?? 'new'; // new | promo | latest
                $items = collect();
                try {
                    if (\Illuminate\Support\Facades\Schema::hasTable('products')) {
                        $q = \App\Models\Product::query()->where('is_active', true);
                        if ($source === 'promo') {
                            $q->whereNotNull('old_price')->whereColumn('old_price', '>', 'price');
                        } elseif ($source === 'new') {
                            $q->where('is_new', true);
                        }
                        $items = $q->orderByDesc('updated_at')->limit($limit)->get();
                    }
                } catch (\Throwable $e) {
                    $items = collect();
                }
            ?>
            <?php if($items->isNotEmpty()): ?>
                <?php if (isset($component)) { $__componentOriginal84e34a75febd89fe14c65c1c82086628 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal84e34a75febd89fe14c65c1c82086628 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.featured-row','data' => ['title' => $block->title ?: 'Рекомендовані товари','items' => $items]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.featured-row'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($block->title ?: 'Рекомендовані товари'),'items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($items)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal84e34a75febd89fe14c65c1c82086628)): ?>
<?php $attributes = $__attributesOriginal84e34a75febd89fe14c65c1c82086628; ?>
<?php unset($__attributesOriginal84e34a75febd89fe14c65c1c82086628); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal84e34a75febd89fe14c65c1c82086628)): ?>
<?php $component = $__componentOriginal84e34a75febd89fe14c65c1c82086628; ?>
<?php unset($__componentOriginal84e34a75febd89fe14c65c1c82086628); ?>
<?php endif; ?>
            <?php endif; ?>

        <?php else: ?>
            
            <?php if(filled($block->content) || filled($block->title)): ?>
                <div class="lb-block lb-block--html" style="margin: 16px 0;">
                    <div class="gazu-container">
                        <?php if(filled($block->title)): ?>
                            <h2 class="gazu-display" style="font-size:22px;font-weight:600;margin:0 0 10px;color:var(--gazu-ink,#111);"><?php echo e($block->title); ?></h2>
                        <?php endif; ?>
                        <?php if(filled($block->content)): ?>
                            <div class="lb-html-content"><?php echo $block->content; ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php /**PATH /home/lionex/projects/gazu-shop/modules/layout_builder/resources/views/zone.blade.php ENDPATH**/ ?>