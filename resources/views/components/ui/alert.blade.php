@props([
    'variant' => 'info',  // info | success | warning | danger
    'dismissible' => false,
    'icon' => null,        // null | heroicon name | true (auto-pick by variant)
    'title' => null,
])

@php
    $variantClasses = [
        'info' => 'alert-ui--info',
        'success' => 'alert-ui--success',
        'warning' => 'alert-ui--warning',
        'danger' => 'alert-ui--danger',
    ];
    $variantCls = $variantClasses[$variant] ?? $variantClasses['info'];

    $autoIcons = [
        'info' => 'heroicon-o-information-circle',
        'success' => 'heroicon-o-check-circle',
        'warning' => 'heroicon-o-exclamation-triangle',
        'danger' => 'heroicon-o-x-circle',
    ];
    $resolvedIcon = $icon === true ? ($autoIcons[$variant] ?? null) : $icon;
@endphp

<div
    {{ $attributes->class('alert-ui flex gap-3 p-4 border-2 ' . $variantCls) }}
    @if($dismissible) x-data="{ shown: true }" x-show="shown" @endif
    role="alert"
>
    @if($resolvedIcon)
        <div class="shrink-0 mt-0.5">
            @if(str_starts_with($resolvedIcon, 'heroicon-'))
                <span class="text-xl">⚠</span>
            @else
                <span class="text-xl">{{ $resolvedIcon }}</span>
            @endif
        </div>
    @endif

    <div class="flex-1 min-w-0">
        @if($title)
            <div class="font-bold mb-1">{{ $title }}</div>
        @endif
        <div class="text-sm">
            {{ $slot }}
        </div>
    </div>

    @if($dismissible)
        <button
            type="button"
            @click="shown = false"
            class="shrink-0 text-xl leading-none px-1 hover:opacity-70 transition-opacity"
            aria-label="Закрити"
        >×</button>
    @endif
</div>
