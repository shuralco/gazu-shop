@props([
    'as' => 'div',
    'padded' => true,    // applies --card-padding
    'elevated' => false, // adds drop shadow (default = no shadow per token)
])

@php
    $classes = 'card-ui'
        .($padded ? ' card-ui--padded' : '')
        .($elevated ? ' card-ui--elevated' : '');
@endphp

<{{ $as }} {{ $attributes->class($classes) }}>
    {{ $slot }}
</{{ $as }}>
