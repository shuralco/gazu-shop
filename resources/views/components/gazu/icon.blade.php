{{-- GAZU icon set — line-icons 24×24, stroke 1.6
     Usage: <x-gazu-icon name="search" size="20" stroke="currentColor" /> --}}
@props(['name', 'size' => 20, 'stroke' => 'currentColor', 'fill' => 'none', 'strokeWidth' => '1.6'])
@php
    $paths = [
        'search'   => '<path d="M11 19a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm10 2-4.35-4.35"/>',
        'cart'     => '<circle cx="9" cy="20" r="1.4"/><circle cx="18" cy="20" r="1.4"/><path d="M3 4h2l2.6 11.4A2 2 0 0 0 9.55 17H18a2 2 0 0 0 2-1.6L21.5 8H6"/>',
        'user'     => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
        'heart'    => '<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78Z"/>',
        'chevron'  => '<path d="m6 9 6 6 6-6"/>',
        'menu'     => '<path d="M3 6h18"/><path d="M3 12h18"/><path d="M3 18h18"/>',
        'close'    => '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>',
        'check'    => '<path d="m5 12 5 5L20 7"/>',
        'phone'    => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7a2 2 0 0 1 1.72 2.03Z"/>',
        'location' => '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0Z"/><circle cx="12" cy="10" r="3"/>',
        'truck'    => '<rect x="1" y="6" width="14" height="11" rx="1"/><path d="M15 9h4l3 4v4h-7"/><circle cx="6" cy="19" r="2"/><circle cx="18" cy="19" r="2"/>',
        'shield'   => '<path d="M12 2 4 5v6c0 5 3.5 9.5 8 11 4.5-1.5 8-6 8-11V5l-8-3Z"/>',
        'star'     => '<path d="m12 2 3.09 6.26L22 9.27l-5 4.87L18.18 22 12 18.56 5.82 22 7 14.14 2 9.27l6.91-1.01L12 2Z"/>',
        'filter'   => '<path d="M3 6h18M6 12h12M10 18h4"/>',
        'grid'     => '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>',
        'list'     => '<path d="M8 6h13"/><path d="M8 12h13"/><path d="M8 18h13"/><path d="M3 6h.01"/><path d="M3 12h.01"/><path d="M3 18h.01"/>',
        'arrow-r'  => '<path d="M5 12h14M13 5l7 7-7 7"/>',
        'arrow-l'  => '<path d="M19 12H5M11 5l-7 7 7 7"/>',
        'car'      => '<path d="M3 14h18l-2-7H5l-2 7Z"/><path d="M3 14v4h2v-2"/><path d="M21 14v4h-2v-2"/><circle cx="7" cy="17" r="2"/><circle cx="17" cy="17" r="2"/>',
        'wrench'   => '<path d="M14.7 6.3a4 4 0 0 1 5 5L9 22l-7-7L12.7 4.3a4 4 0 0 1 2-1Z"/>',
        'box'      => '<path d="m3 7 9-4 9 4-9 4-9-4Z"/><path d="M3 7v10l9 4 9-4V7"/><path d="M12 11v10"/>',
        'return'   => '<path d="M3 12a9 9 0 1 0 3-6.7"/><polyline points="3 4 3 9 8 9"/>',
        'chat'     => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2Z"/>',
        'plus'     => '<path d="M12 5v14M5 12h14"/>',
        'minus'    => '<path d="M5 12h14"/>',
        'trash'    => '<path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M6 6v14a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V6"/>',
        'edit'     => '<path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7M18.5 2.5a2.12 2.12 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5Z"/>',
        'home'     => '<path d="M3 12 12 4l9 8"/><path d="M5 10v10h14V10"/>',
        'mail'     => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 6-10 7L2 6"/>',
        'clock'    => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/>',
    ];
    $svg = $paths[$name] ?? '';
@endphp
<svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="{{ $fill }}" stroke="{{ $stroke }}" stroke-width="{{ $strokeWidth }}" stroke-linecap="round" stroke-linejoin="round" {{ $attributes }}>
    {!! $svg !!}
</svg>
