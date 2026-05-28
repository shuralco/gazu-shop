<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['size' => 26, 'color' => null, 'accent' => null]));

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

foreach (array_filter((['size' => 26, 'color' => null, 'accent' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<?php
    $fg = $color ?? 'var(--gazu-ink)';
    $ac = $accent ?? 'var(--gazu-blue)';
    $markSize = (int) round($size * 1.15);
    $isDark = $color === '#fff' || $color === 'white';
    $textInner = $isDark ? '#0E1B2C' : '#fff';
    $wordSize = (int) round($size * 1.0);
    $gap = (int) round($size * 0.32);

    // Admin-uploaded logo (gazu_logo) takes priority over the built-in GZ mark.
    $brandName = $gazuSettings['gazu_brand_name'] ?? 'GAZU';
    $customLogo = $gazuSettings['gazu_logo'] ?? null;
    if ($customLogo) {
        $customLogo = \Illuminate\Support\Str::startsWith($customLogo, ['http://', 'https://'])
            ? $customLogo
            : (\Illuminate\Support\Str::startsWith($customLogo, '/') ? url($customLogo) : asset('storage/'.ltrim($customLogo, '/')));
    }
?>
<?php if($customLogo): ?>
    <span <?php echo e($attributes->merge(['class' => 'inline-flex items-center shrink-0'])); ?>>
        <img src="<?php echo e($customLogo); ?>" alt="<?php echo e($brandName); ?>" style="height: <?php echo e((int) round($size * 1.3)); ?>px; width: auto; display: block; object-fit: contain;">
    </span>
<?php else: ?>
<span <?php echo e($attributes->merge(['class' => 'inline-flex items-center shrink-0'])); ?> style="gap: <?php echo e($gap); ?>px; font-family: var(--gazu-font-archivo); line-height: .85;">
    <svg width="<?php echo e($markSize); ?>" height="<?php echo e($markSize); ?>" viewBox="0 0 100 100" fill="none" style="display:block">
        <polygon points="50,6 90,28 90,72 50,94 10,72 10,28"
                 fill="<?php echo e($ac); ?>" stroke="<?php echo e($ac); ?>" stroke-width="6" stroke-linejoin="round"/>
        <text x="50" y="64" text-anchor="middle"
              font-family="Archivo Black, Space Grotesk, sans-serif" font-weight="900" font-size="36"
              fill="<?php echo e($textInner); ?>" letter-spacing="-2">GZ</text>
    </svg>
    <span style="font-family: var(--gazu-font-archivo); font-weight: 900; font-size: <?php echo e($wordSize); ?>px; color: <?php echo e($fg); ?>; letter-spacing: -0.04em; text-transform: uppercase;"><?php echo e(strtoupper($brandName)); ?></span>
</span>
<?php endif; ?>
<?php /**PATH /home/lionex/projects/gazu-shop/resources/views/components/gazu/logo.blade.php ENDPATH**/ ?>