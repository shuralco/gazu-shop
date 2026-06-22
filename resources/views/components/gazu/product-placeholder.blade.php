@props([
    'name' => null,
    'code' => null,
    'seed' => null,
    'kind' => null,
    'label' => null,
])
{{-- Генеративне демо-фото товару: детерміноване за seed (id/код/назва), тож
     один товар = одна й та сама картинка, різні товари = різні. Замість
     оманливих стокових фото або однакової мінімалістичної заглушки. --}}
@php
    $gazuPhSrc = \App\Support\PartImage::monogram(
        (string) ($name ?? ''),
        $seed ?? $code ?? $name,
        $code ? (string) $code : null,
    );
@endphp
<img src="{{ $gazuPhSrc }}" alt="{{ $name ?: 'GAZU' }}" loading="lazy" decoding="async"
     {{ $attributes->merge(['class' => 'w-full h-full object-cover select-none']) }}>
