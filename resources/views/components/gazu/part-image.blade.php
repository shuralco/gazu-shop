@props(['kind' => 'filter', 'size' => 160])
@php
    $T = (object)[
        'ink' => '#0E1B2C', 'bone' => '#F5F2EC', 'paper' => '#FBFAF7',
        'blue' => '#2453A6', 'line2' => '#CFD4DB', 'graphite' => '#5A6573',
    ];
    $ill = [
        'filter'  => '<rect x="40" y="50" width="80" height="60" rx="4" stroke="'.$T->ink.'" stroke-width="2" fill="'.$T->bone.'"/><path d="M50 60h60M50 70h60M50 80h60M50 90h60M50 100h60" stroke="'.$T->line2.'"/><circle cx="80" cy="80" r="8" fill="'.$T->blue.'"/>',
        'pad'     => '<path d="M30 70 Q80 50 130 70 L130 100 Q80 80 30 100 Z" fill="'.$T->bone.'" stroke="'.$T->ink.'" stroke-width="2"/><path d="M30 80 Q80 60 130 80" stroke="'.$T->line2.'"/><circle cx="50" cy="85" r="3" fill="'.$T->blue.'"/><circle cx="110" cy="85" r="3" fill="'.$T->blue.'"/>',
        'shock'   => '<rect x="70" y="20" width="20" height="120" rx="4" fill="'.$T->bone.'" stroke="'.$T->ink.'" stroke-width="2"/><circle cx="80" cy="35" r="10" fill="'.$T->ink.'"/><circle cx="80" cy="125" r="10" fill="'.$T->ink.'"/><path d="M75 50v60M85 50v60" stroke="'.$T->line2.'"/>',
        'bulb'    => '<circle cx="80" cy="65" r="28" fill="'.$T->bone.'" stroke="'.$T->ink.'" stroke-width="2"/><rect x="68" y="90" width="24" height="30" fill="'.$T->ink.'"/><path d="M70 100h20M70 108h20M70 116h20" stroke="'.$T->bone.'"/>',
        'oil'     => '<rect x="55" y="30" width="50" height="100" rx="6" fill="'.$T->blue.'" stroke="'.$T->ink.'" stroke-width="2"/><rect x="65" y="20" width="30" height="14" rx="3" fill="'.$T->ink.'"/><rect x="62" y="60" width="36" height="40" fill="#fff"/><text x="80" y="78" text-anchor="middle" font-family="Space Grotesk" font-weight="700" font-size="9" fill="'.$T->ink.'">5W-30</text><text x="80" y="92" text-anchor="middle" font-family="Space Grotesk" font-size="7" fill="'.$T->graphite.'">4L</text>',
        'spark'   => '<rect x="74" y="25" width="12" height="40" fill="'.$T->ink.'"/><polygon points="68,65 92,65 88,90 72,90" fill="'.$T->line2.'" stroke="'.$T->ink.'" stroke-width="2"/><rect x="76" y="90" width="8" height="30" fill="'.$T->ink.'"/><circle cx="80" cy="125" r="5" fill="none" stroke="'.$T->ink.'" stroke-width="2"/>',
        'bearing' => '<circle cx="80" cy="80" r="42" fill="'.$T->bone.'" stroke="'.$T->ink.'" stroke-width="2"/><circle cx="80" cy="80" r="30" fill="#fff" stroke="'.$T->ink.'" stroke-width="1.5"/><circle cx="80" cy="80" r="12" fill="'.$T->blue.'"/>',
        'wiper'   => '<path d="M30 110 L130 60" stroke="'.$T->ink.'" stroke-width="3" fill="none"/><rect x="22" y="106" width="14" height="14" rx="2" fill="'.$T->ink.'"/><path d="M40 100 L130 55" stroke="'.$T->line2.'" stroke-width="6" fill="none"/>',
    ];
    if ($kind === 'bearing') {
        $extra = '';
        for ($i = 0; $i < 8; $i++) {
            $a = $i / 8 * pi() * 2;
            $cx = 80 + cos($a) * 36;
            $cy = 80 + sin($a) * 36;
            $extra .= '<circle cx="'.$cx.'" cy="'.$cy.'" r="3" fill="'.$T->ink.'"/>';
        }
        $ill['bearing'] .= $extra;
    }
    $svg = $ill[$kind] ?? $ill['filter'];
@endphp
<svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 160 160" {{ $attributes->merge(['class' => 'block']) }}>
    <rect width="160" height="160" fill="{{ $T->paper }}"/>
    {!! $svg !!}
</svg>
