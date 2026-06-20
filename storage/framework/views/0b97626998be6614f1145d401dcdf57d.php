<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'variant' => 'primary',  // primary | secondary | ghost
    'size' => 'md',           // sm | md | lg
    'href' => null,
    'type' => 'button',
    'disabled' => false,
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
    'variant' => 'primary',  // primary | secondary | ghost
    'size' => 'md',           // sm | md | lg
    'href' => null,
    'type' => 'button',
    'disabled' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $base = 'inline-flex items-center justify-center font-bold transition-all duration-150 select-none touch-manipulation';
    $sizes = [
        'sm' => 'text-xs px-3 py-1.5',
        'md' => 'text-sm',
        'lg' => 'text-base',
    ];
    $sizeCls = $sizes[$size] ?? $sizes['md'];

    $variants = [
        'primary' => 'btn-ui--primary',
        'secondary' => 'btn-ui--secondary',
        'ghost' => 'btn-ui--ghost',
    ];
    $variantCls = $variants[$variant] ?? $variants['primary'];

    $disabledCls = $disabled ? 'opacity-50 cursor-not-allowed pointer-events-none' : 'hover:-translate-y-0.5 active:translate-y-0';

    $classes = $base.' '.$sizeCls.' '.$variantCls.' '.$disabledCls;
?>

<?php if($href): ?>
    <a href="<?php echo e($href); ?>" <?php echo e($attributes->class($classes)); ?>>
        <?php echo e($slot); ?>

    </a>
<?php else: ?>
    <button type="<?php echo e($type); ?>" <?php if($disabled): ?> disabled <?php endif; ?> <?php echo e($attributes->class($classes)); ?>>
        <?php echo e($slot); ?>

    </button>
<?php endif; ?>
<?php /**PATH /home/lionex/projects/gazu-shop/resources/views/components/ui/button.blade.php ENDPATH**/ ?>