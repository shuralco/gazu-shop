<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['value' => 'Новий']));

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

foreach (array_filter((['value' => 'Новий']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<?php
    $map = [
        'Новий' => ['bg' => 'var(--gazu-success-bg)', 'c' => 'var(--gazu-success)'],
        'Б/у' => ['bg' => 'var(--gazu-warn-bg)', 'c' => 'var(--gazu-warn)'],
        'Відновл.' => ['bg' => 'var(--gazu-mist)', 'c' => 'var(--gazu-blue)'],
    ];
    $s = $map[$value] ?? $map['Новий'];
?>
<span class="text-[11px] gazu-mono px-2 py-0.5 rounded inline-flex items-center"
      style="background: <?php echo e($s['bg']); ?>; color: <?php echo e($s['c']); ?>; letter-spacing: 0.04em;"><?php echo e($value); ?></span>
<?php /**PATH /home/lionex/projects/gazu-shop/resources/views/components/gazu/condition-badge.blade.php ENDPATH**/ ?>