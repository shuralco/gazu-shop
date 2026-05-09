@props(['src', 'alt' => '', 'class' => '', 'width' => null, 'height' => null, 'lazy' => true])

<img
    src="{{ $src }}"
    alt="{{ $alt }}"
    @if($class) class="{{ $class }}" @endif
    @if($width) width="{{ $width }}" @endif
    @if($height) height="{{ $height }}" @endif
    @if($lazy) loading="lazy" decoding="async" @endif
>
