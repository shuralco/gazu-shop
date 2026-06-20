<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['product']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['product']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $product->loadMissing(['brandModel', 'filters.filterGroup']);
    $url = locale_url($product->getLocalizedSlug());
    $showBadges = \App\Models\DisplaySetting::get('show_product_badges', true);
    $showBrand = \App\Models\DisplaySetting::get('show_brands_in_catalog', false);
    $showFilters = \App\Models\DisplaySetting::get('show_product_filters', true);
    $maxFilters = (int) \App\Models\DisplaySetting::get('max_product_filters_display', 3);
    $showAddToCart = \App\Models\DisplaySetting::get('show_add_to_cart_buttons', true);
?>

<?php if (isset($component)) { $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.card','data' => ['class' => 'product-card h-full flex flex-col relative','padded' => false]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'product-card h-full flex flex-col relative','padded' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(false)]); ?>
    <a wire:navigate href="<?php echo e($url); ?>" class="aspect-square w-full bg-gray-100 flex items-center justify-center overflow-hidden block relative">
        <div class="skeleton-shimmer absolute inset-0 z-10" wire:loading.flex wire:target="$refresh"></div>

        <?php if($product->image): ?>
            <img src="<?php echo e(asset($product->getImage())); ?>"
                 alt="<?php echo e($product->title); ?>"
                 class="w-full h-full object-cover"
                 width="400" height="400"
                 loading="lazy" decoding="async"
                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                 style="opacity: 1; transition: opacity 0.3s ease;">
            <div class="hidden items-center justify-center w-full h-full bg-gray-100">
                <span class="text-5xl md:text-6xl text-gray-400">📦</span>
            </div>
        <?php else: ?>
            <div class="flex items-center justify-center w-full h-full bg-gray-100">
                <span class="text-5xl md:text-6xl text-gray-400">📦</span>
            </div>
        <?php endif; ?>

        <?php if($showBadges): ?>
            <?php if($product->is_new): ?>
                <?php if (isset($component)) { $__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.badge','data' => ['variant' => 'default','class' => 'absolute top-4 left-4 z-10']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'default','class' => 'absolute top-4 left-4 z-10']); ?>
                    <?php echo e(__('general.new_badge')); ?>

                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4)): ?>
<?php $attributes = $__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4; ?>
<?php unset($__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4)): ?>
<?php $component = $__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4; ?>
<?php unset($__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4); ?>
<?php endif; ?>
            <?php endif; ?>
            <?php if($product->is_hit): ?>
                <?php if (isset($component)) { $__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.badge','data' => ['variant' => 'danger','class' => 'absolute top-4 right-4 z-10']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['variant' => 'danger','class' => 'absolute top-4 right-4 z-10']); ?>
                    <?php echo e(__('general.hit_badge')); ?>

                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4)): ?>
<?php $attributes = $__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4; ?>
<?php unset($__attributesOriginalab7baa01105b3dfe1e0cf1dfc58879b4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4)): ?>
<?php $component = $__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4; ?>
<?php unset($__componentOriginalab7baa01105b3dfe1e0cf1dfc58879b4); ?>
<?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>

        <?php if($showBrand && $product->brandModel): ?>
            <?php
                $brandName = mb_strtoupper($product->brandModel->name);
                $nameLength = mb_strlen($brandName);
                $fontSize = $nameLength <= 4 ? 'text-sm' : ($nameLength <= 8 ? 'text-xs' : 'text-[10px]');
            ?>
            <a wire:navigate href="<?php echo e(locale_url('brands/' . $product->brandModel->slug)); ?>"
               class="absolute top-1/2 right-4 transform -translate-y-1/2 w-16 h-16 bg-white border-2 border-(--color-fg) flex items-center justify-center hover:bg-gray-100 transition-colors p-2 z-10"
               title="<?php echo e($product->brandModel->name); ?>">
                <span class="<?php echo e($fontSize); ?> font-black text-(--color-fg) leading-tight text-center break-words"
                      style="word-break: break-word; hyphens: auto;"><?php echo e($brandName); ?></span>
            </a>
        <?php endif; ?>
    </a>

    <div class="p-3 md:p-4 flex-grow flex flex-col justify-between">
        <div>
            <a wire:navigate href="<?php echo e($url); ?>" class="hover:underline block mb-3">
                <h3 class="text-lg md:text-xl font-bold text-(--color-fg) line-clamp-2"><?php echo e($product->title); ?></h3>
            </a>

            <?php if($showFilters && $product->filters && $product->filters->count() > 0): ?>
                <div class="mb-3 flex flex-wrap gap-2">
                    <?php $__currentLoopData = $product->filters->take($maxFilters); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $filter): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <span class="inline-block px-2 py-1 text-xs font-medium border border-(--color-fg)">
                            <?php echo e($filter->filterGroup->title); ?>: <?php echo e($filter->title); ?>

                        </span>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php if($product->filters->count() > $maxFilters): ?>
                        <span class="inline-block px-2 py-1 text-xs font-medium border border-gray-400 text-gray-600">
                            +<?php echo e($product->filters->count() - $maxFilters); ?>

                        </span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <p class="text-xl md:text-2xl font-black text-(--color-fg) mb-3">
                <?php echo e(number_format($product->price, 0, ',', ' ')); ?> ₴
            </p>
        </div>

        <div class="flex items-center gap-2 mt-auto">
            <?php if($showAddToCart): ?>
                <?php if (isset($component)) { $__componentOriginala8bb031a483a05f647cb99ed3a469847 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala8bb031a483a05f647cb99ed3a469847 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.button','data' => ['size' => 'sm','class' => 'flex-1','wire:click' => 'add2Cart('.e($product->id).')','wire:loading.attr' => 'disabled','wire:target' => 'add2Cart('.e($product->id).')']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['size' => 'sm','class' => 'flex-1','wire:click' => 'add2Cart('.e($product->id).')','wire:loading.attr' => 'disabled','wire:target' => 'add2Cart('.e($product->id).')']); ?>
                    <span wire:loading.remove wire:target="add2Cart(<?php echo e($product->id); ?>)"><?php echo e(__('general.add_to_cart')); ?></span>
                    <span wire:loading wire:target="add2Cart(<?php echo e($product->id); ?>)"><?php echo e(__('general.adding')); ?></span>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $attributes = $__attributesOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__attributesOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala8bb031a483a05f647cb99ed3a469847)): ?>
<?php $component = $__componentOriginala8bb031a483a05f647cb99ed3a469847; ?>
<?php unset($__componentOriginala8bb031a483a05f647cb99ed3a469847); ?>
<?php endif; ?>
            <?php endif; ?>
            <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('product.comparison-button-component', ['productId' => $product->id]);

$__html = app('livewire')->mount($__name, $__params, 'cmp-'.$product->id, $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93)): ?>
<?php $attributes = $__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93; ?>
<?php unset($__attributesOriginaldae4cd48acb67888a4631e1ba48f2f93); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldae4cd48acb67888a4631e1ba48f2f93)): ?>
<?php $component = $__componentOriginaldae4cd48acb67888a4631e1ba48f2f93; ?>
<?php unset($__componentOriginaldae4cd48acb67888a4631e1ba48f2f93); ?>
<?php endif; ?>
<?php /**PATH /home/lionex/projects/gazu-shop/resources/views/components/ui/product-card.blade.php ENDPATH**/ ?>