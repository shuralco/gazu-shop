
<?php
    $skipHome = $skipHome ?? false;
    $crumbs = $skipHome ? [] : [['Головна', route('gazu.home')]];
    $crumbs[] = ['Каталог', route('gazu.catalog')];
    if (is_object($p) && ($cat = $p->category ?? null)) {
        $chain = [];
        $seen = [];
        while ($cat && !isset($seen[$cat->id])) {
            $seen[$cat->id] = true;
            array_unshift($chain, $cat);
            $cat = $cat->parent ?? null;
        }
        foreach ($chain as $c) {
            $title = method_exists($c, 'getTranslation')
                ? ($c->getTranslation('title', app()->getLocale(), false)
                    ?: ($c->getTranslation('title', 'uk', false) ?: ($c->name ?? '')))
                : ($c->title ?? $c->name ?? '');
            $slug = method_exists($c, 'getLocalizedSlug') ? $c->getLocalizedSlug() : ($c->slug ?? $c->id);
            $crumbs[] = [(string) $title, url('/'.($slug ?: $c->id))];
        }
    }
    // Last crumb is the product NAME (not brand + article). The article is
    // already shown below in the central column's meta block — duplicating
    // it in the breadcrumb above the gallery was redundant and made the
    // article appear above the product photo on mobile.
    $crumbs[] = $name ?: (trim(($brand ?? '').' '.($oem ?? '')) ?: 'Товар');
?>
<?php if (isset($component)) { $__componentOriginaldd75f73904e8d7e4a617b590234b9aa0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldd75f73904e8d7e4a617b590234b9aa0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.gazu.breadcrumbs','data' => ['items' => $crumbs]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('gazu.breadcrumbs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($crumbs)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldd75f73904e8d7e4a617b590234b9aa0)): ?>
<?php $attributes = $__attributesOriginaldd75f73904e8d7e4a617b590234b9aa0; ?>
<?php unset($__attributesOriginaldd75f73904e8d7e4a617b590234b9aa0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldd75f73904e8d7e4a617b590234b9aa0)): ?>
<?php $component = $__componentOriginaldd75f73904e8d7e4a617b590234b9aa0; ?>
<?php unset($__componentOriginaldd75f73904e8d7e4a617b590234b9aa0); ?>
<?php endif; ?>
<?php /**PATH /home/lionex/projects/gazu-shop/resources/views/gazu/partials/product-breadcrumbs.blade.php ENDPATH**/ ?>