<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'variant' => 'info',  // info | success | warning | danger
    'dismissible' => false,
    'icon' => null,        // null | heroicon name | true (auto-pick by variant)
    'title' => null,
]));

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

foreach (array_filter(([
    'variant' => 'info',  // info | success | warning | danger
    'dismissible' => false,
    'icon' => null,        // null | heroicon name | true (auto-pick by variant)
    'title' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $variantClasses = [
        'info' => 'alert-ui--info',
        'success' => 'alert-ui--success',
        'warning' => 'alert-ui--warning',
        'danger' => 'alert-ui--danger',
    ];
    $variantCls = $variantClasses[$variant] ?? $variantClasses['info'];

    $autoIcons = [
        'info' => 'heroicon-o-information-circle',
        'success' => 'heroicon-o-check-circle',
        'warning' => 'heroicon-o-exclamation-triangle',
        'danger' => 'heroicon-o-x-circle',
    ];
    $resolvedIcon = $icon === true ? ($autoIcons[$variant] ?? null) : $icon;
?>

<div
    <?php echo e($attributes->class('alert-ui flex gap-3 p-4 border-2 ' . $variantCls)); ?>

    <?php if($dismissible): ?> x-data="{ shown: true }" x-show="shown" <?php endif; ?>
    role="alert"
>
    <?php if($resolvedIcon): ?>
        <div class="shrink-0 mt-0.5">
            <?php if(str_starts_with($resolvedIcon, 'heroicon-')): ?>
                <span class="text-xl">⚠</span>
            <?php else: ?>
                <span class="text-xl"><?php echo e($resolvedIcon); ?></span>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="flex-1 min-w-0">
        <?php if($title): ?>
            <div class="font-bold mb-1"><?php echo e($title); ?></div>
        <?php endif; ?>
        <div class="text-sm">
            <?php echo e($slot); ?>

        </div>
    </div>

    <?php if($dismissible): ?>
        <button
            type="button"
            @click="shown = false"
            class="shrink-0 text-xl leading-none px-1 hover:opacity-70 transition-opacity"
            aria-label="Закрити"
        >×</button>
    <?php endif; ?>
</div>
<?php /**PATH /home/lionex/projects/gazu-shop/resources/views/components/ui/alert.blade.php ENDPATH**/ ?>