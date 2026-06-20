<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title' => null,
    'subtitle' => null,
    'as' => 'section',
    'centered' => false,
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
    'title' => null,
    'subtitle' => null,
    'as' => 'section',
    'centered' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<<?php echo e($as); ?> <?php echo e($attributes->class('section-ui')); ?>>
    <?php if($title || $subtitle): ?>
        <header class="<?php echo e($centered ? 'text-center' : ''); ?> mb-8">
            <?php if($title): ?>
                <h2 class="section-ui__title"><?php echo e($title); ?></h2>
            <?php endif; ?>
            <?php if($subtitle): ?>
                <p class="section-ui__subtitle mt-2"><?php echo e($subtitle); ?></p>
            <?php endif; ?>
        </header>
    <?php endif; ?>

    <?php echo e($slot); ?>

</<?php echo e($as); ?>>
<?php /**PATH /home/lionex/projects/gazu-shop/resources/views/components/ui/section.blade.php ENDPATH**/ ?>