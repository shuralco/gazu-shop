<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['kind' => 'filter', 'size' => 160, 'fit' => false, 'seed' => null, 'title' => null, 'eager' => false]));

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

foreach (array_filter((['kind' => 'filter', 'size' => 160, 'fit' => false, 'seed' => null, 'title' => null, 'eager' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>
<?php
    // eager=true для above-the-fold карток (перший ряд каталогу/головної):
    // native loading="lazy" відкладав навіть видимі картинки → на першому екрані
    // вони були порожні до скролу («картинок у фільтрації немає»). Перший ряд
    // вантажимо одразу + fetchpriority=high.
    $imgLoading = $eager ? 'eager' : 'lazy';
    $imgPriority = $eager ? 'high' : 'auto';
?>
<?php
    // Real demo photos (Pexels) take priority over the vector illustration.
    // Priority: per-kind pool (public/img/parts/<kind>/NN.webp) → single file
    // (public/img/parts/<kind>.webp) → monogram (if title given) → SVG.
    static $partPoolCache = [];
    if (! array_key_exists($kind, $partPoolCache)) {
        $dir = public_path("img/parts/{$kind}");
        $files = is_dir($dir) ? glob($dir.'/*.webp') : [];
        sort($files);
        $partPoolCache[$kind] = array_map('basename', $files);
    }
    $pool = $partPoolCache[$kind];
    $partPhoto = null;
    if (! empty($pool)) {
        $idx = $seed !== null ? abs((int) $seed) % count($pool) : 0;
        $partPhoto = asset("img/parts/{$kind}/".$pool[$idx]);
    } elseif (is_file(public_path("img/parts/{$kind}.webp"))) {
        $partPhoto = asset("img/parts/{$kind}.webp");
    }
?>
<?php if($partPhoto): ?>
    <?php if($fit): ?>
        <img src="<?php echo e($partPhoto); ?>" alt="<?php echo e($kind); ?>" loading="<?php echo e($imgLoading); ?>" fetchpriority="<?php echo e($imgPriority); ?>" decoding="async"
             <?php echo e($attributes->merge(['class' => 'block w-full h-full object-cover'])); ?>>
    <?php else: ?>
        <img src="<?php echo e($partPhoto); ?>" alt="<?php echo e($kind); ?>" loading="<?php echo e($imgLoading); ?>" fetchpriority="<?php echo e($imgPriority); ?>" decoding="async"
             width="<?php echo e($size); ?>" height="<?php echo e($size); ?>"
             <?php echo e($attributes->merge(['class' => 'block object-cover'])); ?>>
    <?php endif; ?>
<?php elseif($title): ?>
    
    <?php
        $monogramUrl = \App\Support\PartImage::monogram((string) $title, $seed);
    ?>
    <?php if($fit): ?>
        <img src="<?php echo e($monogramUrl); ?>" alt="<?php echo e($title); ?>" loading="<?php echo e($imgLoading); ?>" fetchpriority="<?php echo e($imgPriority); ?>" decoding="async"
             <?php echo e($attributes->merge(['class' => 'block w-full h-full object-cover'])); ?>>
    <?php else: ?>
        <img src="<?php echo e($monogramUrl); ?>" alt="<?php echo e($title); ?>" loading="<?php echo e($imgLoading); ?>" fetchpriority="<?php echo e($imgPriority); ?>" decoding="async"
             width="<?php echo e($size); ?>" height="<?php echo e($size); ?>"
             <?php echo e($attributes->merge(['class' => 'block object-cover'])); ?>>
    <?php endif; ?>
<?php else: ?>
<?php
    $T = (object)[
        'ink' => '#0E1B2C', 'bone' => '#F5F2EC', 'paper' => '#FBFAF7',
        'blue' => '#2453A6', 'red' => '#B83232', 'green' => '#3A8C5C',
        'gold' => '#D4A24A', 'line2' => '#CFD4DB', 'graphite' => '#5A6573',
    ];
    $ill = [
        'filter'  => '<rect x="40" y="50" width="80" height="60" rx="4" stroke="'.$T->ink.'" stroke-width="2" fill="'.$T->bone.'"/><path d="M50 60h60M50 70h60M50 80h60M50 90h60M50 100h60" stroke="'.$T->line2.'"/><circle cx="80" cy="80" r="8" fill="'.$T->blue.'"/>',
        'pad'     => '<path d="M30 70 Q80 50 130 70 L130 100 Q80 80 30 100 Z" fill="'.$T->bone.'" stroke="'.$T->ink.'" stroke-width="2"/><path d="M30 80 Q80 60 130 80" stroke="'.$T->line2.'"/><circle cx="50" cy="85" r="3" fill="'.$T->blue.'"/><circle cx="110" cy="85" r="3" fill="'.$T->blue.'"/>',
        'shock'   => '<rect x="70" y="20" width="20" height="120" rx="4" fill="'.$T->bone.'" stroke="'.$T->ink.'" stroke-width="2"/><circle cx="80" cy="35" r="10" fill="'.$T->ink.'"/><circle cx="80" cy="125" r="10" fill="'.$T->ink.'"/><path d="M75 50v60M85 50v60" stroke="'.$T->line2.'"/>',
        'bulb'    => '<circle cx="80" cy="65" r="28" fill="'.$T->bone.'" stroke="'.$T->ink.'" stroke-width="2"/><rect x="68" y="90" width="24" height="30" fill="'.$T->ink.'"/><path d="M70 100h20M70 108h20M70 116h20" stroke="'.$T->bone.'"/><circle cx="80" cy="58" r="6" fill="'.$T->gold.'"/>',
        'oil'     => '<rect x="55" y="30" width="50" height="100" rx="6" fill="'.$T->blue.'" stroke="'.$T->ink.'" stroke-width="2"/><rect x="65" y="20" width="30" height="14" rx="3" fill="'.$T->ink.'"/><rect x="62" y="60" width="36" height="40" fill="#fff"/><text x="80" y="78" text-anchor="middle" font-family="Space Grotesk" font-weight="700" font-size="9" fill="'.$T->ink.'">5W-30</text><text x="80" y="92" text-anchor="middle" font-family="Space Grotesk" font-size="7" fill="'.$T->graphite.'">4L</text>',
        'spark'   => '<rect x="74" y="25" width="12" height="40" fill="'.$T->ink.'"/><polygon points="68,65 92,65 88,90 72,90" fill="'.$T->line2.'" stroke="'.$T->ink.'" stroke-width="2"/><rect x="76" y="90" width="8" height="30" fill="'.$T->ink.'"/><circle cx="80" cy="125" r="5" fill="none" stroke="'.$T->ink.'" stroke-width="2"/>',
        'bearing' => '<circle cx="80" cy="80" r="42" fill="'.$T->bone.'" stroke="'.$T->ink.'" stroke-width="2"/><circle cx="80" cy="80" r="30" fill="#fff" stroke="'.$T->ink.'" stroke-width="1.5"/><circle cx="80" cy="80" r="12" fill="'.$T->blue.'"/>',
        'wiper'   => '<path d="M30 110 L130 60" stroke="'.$T->ink.'" stroke-width="3" fill="none"/><rect x="22" y="106" width="14" height="14" rx="2" fill="'.$T->ink.'"/><path d="M40 100 L130 55" stroke="'.$T->line2.'" stroke-width="6" fill="none"/>',
        'battery' => '<rect x="35" y="40" width="90" height="80" rx="4" fill="'.$T->bone.'" stroke="'.$T->ink.'" stroke-width="2"/><rect x="50" y="30" width="20" height="14" rx="2" fill="'.$T->ink.'"/><rect x="90" y="30" width="20" height="14" rx="2" fill="'.$T->ink.'"/><text x="60" y="42" text-anchor="middle" font-family="Space Grotesk" font-weight="700" font-size="11" fill="#fff">+</text><text x="100" y="42" text-anchor="middle" font-family="Space Grotesk" font-weight="700" font-size="11" fill="#fff">−</text><text x="80" y="85" text-anchor="middle" font-family="Space Grotesk" font-weight="700" font-size="14" fill="'.$T->ink.'">12V</text><text x="80" y="105" text-anchor="middle" font-family="Space Grotesk" font-size="9" fill="'.$T->graphite.'">60Ah</text>',
        'alternator' => '<circle cx="80" cy="80" r="40" fill="'.$T->bone.'" stroke="'.$T->ink.'" stroke-width="2"/><circle cx="80" cy="80" r="22" fill="#fff" stroke="'.$T->ink.'" stroke-width="1.5"/><circle cx="80" cy="80" r="8" fill="'.$T->ink.'"/><path d="M40 80h10M110 80h10M80 40v10M80 110v10" stroke="'.$T->ink.'" stroke-width="2"/>',
        'headlight' => '<path d="M30 60 Q30 40 60 40 H120 Q140 40 140 60 V100 Q140 120 120 120 H60 Q30 120 30 100 Z" fill="'.$T->bone.'" stroke="'.$T->ink.'" stroke-width="2"/><circle cx="70" cy="80" r="20" fill="#fff" stroke="'.$T->ink.'" stroke-width="1.5"/><circle cx="70" cy="80" r="10" fill="'.$T->gold.'"/><circle cx="115" cy="80" r="8" fill="#fff" stroke="'.$T->ink.'" stroke-width="1.5"/>',
        'taillight' => '<rect x="30" y="55" width="100" height="50" rx="8" fill="'.$T->red.'" stroke="'.$T->ink.'" stroke-width="2"/><rect x="40" y="65" width="80" height="30" rx="4" fill="#E85A5A"/><rect x="50" y="72" width="60" height="3" fill="#fff" opacity="0.4"/><rect x="50" y="82" width="60" height="3" fill="#fff" opacity="0.4"/>',
        'cv-joint' => '<circle cx="80" cy="80" r="32" fill="'.$T->bone.'" stroke="'.$T->ink.'" stroke-width="2"/><rect x="74" y="40" width="12" height="80" fill="'.$T->ink.'"/><circle cx="80" cy="80" r="14" fill="'.$T->graphite.'"/><circle cx="80" cy="80" r="6" fill="'.$T->ink.'"/>',
        'brake-disc' => '<circle cx="80" cy="80" r="44" fill="'.$T->bone.'" stroke="'.$T->ink.'" stroke-width="2"/><circle cx="80" cy="80" r="34" fill="#fff"/><circle cx="80" cy="80" r="12" fill="'.$T->ink.'"/>',
        'tire' => '<circle cx="80" cy="80" r="50" fill="'.$T->ink.'"/><circle cx="80" cy="80" r="36" fill="'.$T->graphite.'"/><circle cx="80" cy="80" r="20" fill="'.$T->bone.'"/><circle cx="80" cy="80" r="8" fill="'.$T->ink.'"/>',
        'spring' => '<path d="M70 30 Q90 35 80 50 Q70 60 90 65 Q90 75 70 80 Q90 90 80 100 Q70 110 90 115 Q80 125 70 130" fill="none" stroke="'.$T->ink.'" stroke-width="3"/><rect x="60" y="22" width="40" height="6" fill="'.$T->ink.'"/><rect x="60" y="132" width="40" height="6" fill="'.$T->ink.'"/>',
        'coolant' => '<rect x="55" y="30" width="50" height="100" rx="6" fill="'.$T->green.'" stroke="'.$T->ink.'" stroke-width="2"/><rect x="65" y="20" width="30" height="14" rx="3" fill="'.$T->ink.'"/><rect x="62" y="60" width="36" height="40" fill="#fff"/><text x="80" y="78" text-anchor="middle" font-family="Space Grotesk" font-weight="700" font-size="11" fill="'.$T->ink.'">G12</text><text x="80" y="92" text-anchor="middle" font-family="Space Grotesk" font-size="7" fill="'.$T->graphite.'">5L</text>',
        'mirror' => '<ellipse cx="80" cy="70" rx="40" ry="30" fill="'.$T->bone.'" stroke="'.$T->ink.'" stroke-width="2"/><ellipse cx="80" cy="70" rx="32" ry="22" fill="'.$T->blue.'" opacity="0.7"/><rect x="76" y="100" width="8" height="30" fill="'.$T->ink.'"/><rect x="60" y="125" width="40" height="6" fill="'.$T->ink.'"/>',
        'mat' => '<path d="M30 40 L130 40 L120 130 L40 130 Z" fill="'.$T->bone.'" stroke="'.$T->ink.'" stroke-width="2"/><path d="M40 50 L120 50 M40 70 L120 70 M45 90 L115 90 M50 110 L110 110" stroke="'.$T->graphite.'" stroke-width="0.6" opacity="0.5"/><text x="80" y="115" text-anchor="middle" font-family="Space Grotesk" font-weight="700" font-size="10" fill="'.$T->graphite.'">3D</text>',
        'sensor' => '<rect x="55" y="50" width="50" height="40" rx="4" fill="'.$T->bone.'" stroke="'.$T->ink.'" stroke-width="2"/><circle cx="80" cy="70" r="10" fill="'.$T->blue.'"/><circle cx="80" cy="70" r="4" fill="'.$T->ink.'"/><path d="M80 90 L80 120" stroke="'.$T->ink.'" stroke-width="3"/><path d="M70 120 L90 120" stroke="'.$T->ink.'" stroke-width="3"/>',
        'horn' => '<path d="M30 80 L70 50 L70 110 Z" fill="'.$T->bone.'" stroke="'.$T->ink.'" stroke-width="2"/><circle cx="60" cy="80" r="14" fill="'.$T->blue.'"/><circle cx="60" cy="80" r="6" fill="'.$T->ink.'"/><path d="M75 75 L95 70 M75 80 L100 80 M75 85 L95 90" stroke="'.$T->ink.'" stroke-width="1.5"/>',
        'belt' => '<ellipse cx="80" cy="80" rx="50" ry="20" fill="none" stroke="'.$T->ink.'" stroke-width="6"/><circle cx="55" cy="80" r="12" fill="'.$T->bone.'" stroke="'.$T->ink.'" stroke-width="2"/><circle cx="105" cy="80" r="12" fill="'.$T->bone.'" stroke="'.$T->ink.'" stroke-width="2"/><circle cx="55" cy="80" r="4" fill="'.$T->ink.'"/><circle cx="105" cy="80" r="4" fill="'.$T->ink.'"/>',
        'tool' => '<rect x="68" y="20" width="24" height="80" rx="4" fill="'.$T->bone.'" stroke="'.$T->ink.'" stroke-width="2"/><rect x="60" y="100" width="40" height="40" rx="3" fill="'.$T->ink.'"/><rect x="74" y="106" width="12" height="28" fill="'.$T->blue.'"/><path d="M68 30h24M68 40h24M68 50h24" stroke="'.$T->line2.'"/>',
    ];
    // Brake-disc gets bolt circles
    if ($kind === 'brake-disc') {
        for ($i = 0; $i < 12; $i++) {
            $a = $i / 12 * pi() * 2;
            $x = 80 + cos($a) * 24;
            $y = 80 + sin($a) * 24;
            $ill['brake-disc'] .= '<circle cx="'.$x.'" cy="'.$y.'" r="1.5" fill="'.$T->line2.'"/>';
        }
    }
    // Bearing balls
    if ($kind === 'bearing') {
        for ($i = 0; $i < 8; $i++) {
            $a = $i / 8 * pi() * 2;
            $ill['bearing'] .= '<circle cx="'.(80 + cos($a) * 36).'" cy="'.(80 + sin($a) * 36).'" r="3" fill="'.$T->ink.'"/>';
        }
    }
    // Tire tread blocks
    if ($kind === 'tire') {
        for ($i = 0; $i < 12; $i++) {
            $a = $i / 12 * pi() * 2;
            $x = 80 + cos($a) * 42;
            $y = 80 + sin($a) * 42;
            $ill['tire'] .= '<rect x="'.($x - 3).'" y="'.($y - 4).'" width="6" height="8" fill="'.$T->bone.'" transform="rotate('.($i * 30).' '.$x.' '.$y.')"/>';
        }
    }
    // Clutch fingers
    if ($kind === 'clutch') {
        $ill['clutch'] = '<circle cx="80" cy="80" r="44" fill="'.$T->bone.'" stroke="'.$T->ink.'" stroke-width="2"/>';
        for ($i = 0; $i < 8; $i++) {
            $ill['clutch'] .= '<rect x="76" y="40" width="8" height="80" fill="'.$T->graphite.'" transform="rotate('.($i * 45).' 80 80)"/>';
        }
        $ill['clutch'] .= '<circle cx="80" cy="80" r="14" fill="'.$T->ink.'"/><circle cx="80" cy="80" r="6" fill="'.$T->blue.'"/>';
    }
    $svg = $ill[$kind] ?? $ill['filter'];
?>
<?php if($fit): ?>

<svg width="100%" height="100%" viewBox="22 18 116 124" preserveAspectRatio="xMidYMid meet" <?php echo e($attributes->merge(['class' => 'block max-w-full max-h-full'])); ?>>
<?php else: ?>
<svg width="<?php echo e($size); ?>" height="<?php echo e($size); ?>" viewBox="0 0 160 160" <?php echo e($attributes->merge(['class' => 'block'])); ?>>
<?php endif; ?>
    <rect width="160" height="160" fill="#EEF1F4"/>
    
    <g opacity="0.06" transform="translate(80 80)">
        <path d="M0 -50 L6 -42 L18 -45 L20 -33 L31 -28 L26 -18 L34 -10 L24 -3 L24 8 L13 11 L9 22 L-3 21 L-12 28 L-18 19 L-29 17 L-28 5 L-37 -2 L-30 -11 L-32 -22 L-21 -25 L-15 -35 L-3 -34 Z" fill="#0E1B2C"/>
        <circle cx="0" cy="0" r="15" fill="#EEF1F4"/>
    </g>
    <?php echo $svg; ?>

</svg>
<?php endif; ?>
<?php /**PATH /home/lionex/projects/gazu-shop/resources/views/components/gazu/part-image.blade.php ENDPATH**/ ?>