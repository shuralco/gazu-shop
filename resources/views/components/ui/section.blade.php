@props([
    'title' => null,
    'subtitle' => null,
    'as' => 'section',
    'centered' => false,
])

<{{ $as }} {{ $attributes->class('section-ui') }}>
    @if($title || $subtitle)
        <header class="{{ $centered ? 'text-center' : '' }} mb-8">
            @if($title)
                <h2 class="section-ui__title">{{ $title }}</h2>
            @endif
            @if($subtitle)
                <p class="section-ui__subtitle mt-2">{{ $subtitle }}</p>
            @endif
        </header>
    @endif

    {{ $slot }}
</{{ $as }}>
