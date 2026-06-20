<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'as' => 'div',
    'padded' => true,    // applies --card-padding
    'elevated' => false, // adds drop shadow (default = no shadow per token)
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
    'as' => 'div',
    'padded' => true,    // applies --card-padding
    'elevated' => false, // adds drop shadow (default = no shadow per token)
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $classes = 'card-ui'
        .($padded ? ' card-ui--padded' : '')
        .($elevated ? ' card-ui--elevated' : '');
?>

<<?php echo e($as); ?> <?php echo e($attributes->class($classes)); ?>>
    <?php echo e($slot); ?>

</<?php echo e($as); ?>>
<?php /**PATH /home/lionex/projects/gazu-shop/resources/views/components/ui/card.blade.php ENDPATH**/ ?>