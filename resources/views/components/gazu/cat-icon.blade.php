@props(['kind', 'size' => 22])
@php
    $g = [
        'engine'      => '<rect x="4" y="8" width="14" height="10" rx="1.5"/><path d="M18 11h2v4h-2M4 8V6h3v2M9 8V5h3v3"/>',
        'brakes'      => '<circle cx="12" cy="12" r="8"/><circle cx="12" cy="12" r="3"/><path d="M12 4v3M12 17v3M4 12h3M17 12h3"/>',
        'suspension'  => '<path d="M5 4v16M19 4v16"/><path d="M5 6h14M5 10h14M5 14h14M5 18h14" stroke-width="1.2"/>',
        'electric'    => '<path d="M13 2L4 14h6l-1 8 9-12h-6z"/>',
        'body'        => '<path d="M3 16l2-6 4-3h6l4 3 2 6v3H3z"/><circle cx="7" cy="17" r="2"/><circle cx="17" cy="17" r="2"/>',
        'interior'    => '<path d="M6 4h12v8l-2 8H8l-2-8z"/><path d="M9 12h6"/>',
        'filters'     => '<path d="M5 4h14l-3 7v6l-4 3h-2l-2-3v-6z"/>',
        'oils'        => '<rect x="7" y="8" width="10" height="12" rx="1"/><path d="M9 8V5h6v3M11 12h2v4h-2z"/>',
        'tires'       => '<circle cx="12" cy="12" r="8"/><circle cx="12" cy="12" r="3"/><path d="M12 4l1 5M12 20l1-5M4 12l5-1M20 12l-5-1"/>',
        'tools'       => '<path d="M14 4l-9 9 4 4 9-9V4z"/><circle cx="16" cy="6" r="1"/>',
        'lights'      => '<path d="M8 4h8l2 6-3 8H9l-3-8z"/><circle cx="12" cy="11" r="2.5"/>',
        'transmission'=> '<circle cx="8" cy="12" r="3"/><circle cx="16" cy="12" r="3"/><path d="M11 12h2M5 12H3M21 12h-2"/>',
    ];
    $svg = $g[$kind] ?? '';
@endphp
<svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" {{ $attributes }}>
    {!! $svg !!}
</svg>
