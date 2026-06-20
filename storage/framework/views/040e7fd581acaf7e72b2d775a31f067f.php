<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'type' => 'text',
    'name' => null,
    'id' => null,
    'value' => null,
    'placeholder' => null,
    'label' => null,
    'error' => null,
    'required' => false,
    'autocomplete' => 'off',
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
    'type' => 'text',
    'name' => null,
    'id' => null,
    'value' => null,
    'placeholder' => null,
    'label' => null,
    'error' => null,
    'required' => false,
    'autocomplete' => 'off',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $resolvedId = $id ?? $name;
    $hasError = ! empty($error);
?>

<div class="space-y-1">
    <?php if($label): ?>
        <label <?php if($resolvedId): ?> for="<?php echo e($resolvedId); ?>" <?php endif; ?> class="block font-bold mb-1">
            <?php echo e($label); ?>

            <?php if($required): ?><span class="text-red-600">*</span><?php endif; ?>
        </label>
    <?php endif; ?>

    <input
        type="<?php echo e($type); ?>"
        <?php if($name): ?> name="<?php echo e($name); ?>" <?php endif; ?>
        <?php if($resolvedId): ?> id="<?php echo e($resolvedId); ?>" <?php endif; ?>
        <?php if($value !== null): ?> value="<?php echo e($value); ?>" <?php endif; ?>
        <?php if($placeholder): ?> placeholder="<?php echo e($placeholder); ?>" <?php endif; ?>
        <?php if($required): ?> required <?php endif; ?>
        autocomplete="<?php echo e($autocomplete); ?>"
        <?php echo e($attributes->class('input-ui w-full focus:outline-none focus:ring-2 '.($hasError ? 'border-red-500' : ''))); ?>

    />

    <?php if($hasError): ?>
        <div class="text-sm text-red-600"><?php echo e($error); ?></div>
    <?php endif; ?>
</div>
<?php /**PATH /home/lionex/projects/gazu-shop/resources/views/components/ui/input.blade.php ENDPATH**/ ?>