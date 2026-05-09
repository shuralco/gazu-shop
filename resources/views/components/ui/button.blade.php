@props([
    'variant' => 'primary',  // primary | secondary | ghost
    'size' => 'md',           // sm | md | lg
    'href' => null,
    'type' => 'button',
    'disabled' => false,
])

@php
    $base = 'inline-flex items-center justify-center font-bold transition-all duration-150 select-none touch-manipulation';
    $sizes = [
        'sm' => 'text-xs px-3 py-1.5',
        'md' => 'text-sm',
        'lg' => 'text-base',
    ];
    $sizeCls = $sizes[$size] ?? $sizes['md'];

    $variants = [
        'primary' => 'btn-ui--primary',
        'secondary' => 'btn-ui--secondary',
        'ghost' => 'btn-ui--ghost',
    ];
    $variantCls = $variants[$variant] ?? $variants['primary'];

    $disabledCls = $disabled ? 'opacity-50 cursor-not-allowed pointer-events-none' : 'hover:-translate-y-0.5 active:translate-y-0';

    $classes = $base.' '.$sizeCls.' '.$variantCls.' '.$disabledCls;
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" @if($disabled) disabled @endif {{ $attributes->class($classes) }}>
        {{ $slot }}
    </button>
@endif
