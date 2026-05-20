@props(['size' => 26, 'color' => null, 'accent' => null])
@php
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
@endphp
@if($customLogo)
    <span {{ $attributes->merge(['class' => 'inline-flex items-center shrink-0']) }}>
        <img src="{{ $customLogo }}" alt="{{ $brandName }}" style="height: {{ (int) round($size * 1.3) }}px; width: auto; display: block; object-fit: contain;">
    </span>
@else
<span {{ $attributes->merge(['class' => 'inline-flex items-center shrink-0']) }} style="gap: {{ $gap }}px; font-family: var(--gazu-font-archivo); line-height: .85;">
    <svg width="{{ $markSize }}" height="{{ $markSize }}" viewBox="0 0 100 100" fill="none" style="display:block">
        <polygon points="50,6 90,28 90,72 50,94 10,72 10,28"
                 fill="{{ $ac }}" stroke="{{ $ac }}" stroke-width="6" stroke-linejoin="round"/>
        <text x="50" y="64" text-anchor="middle"
              font-family="Archivo Black, Space Grotesk, sans-serif" font-weight="900" font-size="36"
              fill="{{ $textInner }}" letter-spacing="-2">GZ</text>
    </svg>
    <span style="font-family: var(--gazu-font-archivo); font-weight: 900; font-size: {{ $wordSize }}px; color: {{ $fg }}; letter-spacing: -0.04em; text-transform: uppercase;">{{ strtoupper($brandName) }}</span>
</span>
@endif
