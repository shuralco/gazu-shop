<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'name' => null,
    'code' => null,
    'seed' => null,
    'kind' => null,
    'label' => null,
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
    'name' => null,
    'code' => null,
    'seed' => null,
    'kind' => null,
    'label' => null,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $gazuPhSrc = \App\Support\PartImage::monogram(
        (string) ($name ?? ''),
        $seed ?? $code ?? $name,
        $code ? (string) $code : null,
    );
?>
<img src="<?php echo e($gazuPhSrc); ?>" alt="<?php echo e($name ?: 'GAZU'); ?>" loading="lazy" decoding="async"
     <?php echo e($attributes->merge(['class' => 'w-full h-full object-cover select-none'])); ?>>
<?php /**PATH /home/lionex/projects/gazu-shop/resources/views/components/gazu/product-placeholder.blade.php ENDPATH**/ ?>