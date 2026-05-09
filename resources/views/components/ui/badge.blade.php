@props([
    'variant' => 'default', // default | success | danger | warning | info | accent
])

@php
    $variants = [
        'default' => 'badge-ui--default',
        'success' => 'badge-ui--success',
        'danger' => 'badge-ui--danger',
        'warning' => 'badge-ui--warning',
        'info' => 'badge-ui--info',
        'accent' => 'badge-ui--accent',
    ];
    $variantCls = $variants[$variant] ?? $variants['default'];
@endphp

<span {{ $attributes->class('badge-ui inline-flex items-center '.$variantCls) }}>
    {{ $slot }}
</span>
