<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'variant' => 'default', // default | success | danger | warning | info | accent
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
    'variant' => 'default', // default | success | danger | warning | info | accent
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $variants = [
        'default' => 'badge-ui--default',
        'success' => 'badge-ui--success',
        'danger' => 'badge-ui--danger',
        'warning' => 'badge-ui--warning',
        'info' => 'badge-ui--info',
        'accent' => 'badge-ui--accent',
    ];
    $variantCls = $variants[$variant] ?? $variants['default'];
?>

<span <?php echo e($attributes->class('badge-ui inline-flex items-center '.$variantCls)); ?>>
    <?php echo e($slot); ?>

</span>
<?php /**PATH /home/lionex/projects/gazu-shop/resources/views/components/ui/badge.blade.php ENDPATH**/ ?>