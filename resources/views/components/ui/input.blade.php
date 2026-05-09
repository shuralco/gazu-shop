@props([
    'type' => 'text',
    'name' => null,
    'id' => null,
    'value' => null,
    'placeholder' => null,
    'label' => null,
    'error' => null,
    'required' => false,
    'autocomplete' => 'off',
])

@php
    $resolvedId = $id ?? $name;
    $hasError = ! empty($error);
@endphp

<div class="space-y-1">
    @if($label)
        <label @if($resolvedId) for="{{ $resolvedId }}" @endif class="block font-bold mb-1">
            {{ $label }}
            @if($required)<span class="text-red-600">*</span>@endif
        </label>
    @endif

    <input
        type="{{ $type }}"
        @if($name) name="{{ $name }}" @endif
        @if($resolvedId) id="{{ $resolvedId }}" @endif
        @if($value !== null) value="{{ $value }}" @endif
        @if($placeholder) placeholder="{{ $placeholder }}" @endif
        @if($required) required @endif
        autocomplete="{{ $autocomplete }}"
        {{ $attributes->class('input-ui w-full focus:outline-none focus:ring-2 '.($hasError ? 'border-red-500' : '')) }}
    />

    @if($hasError)
        <div class="text-sm text-red-600">{{ $error }}</div>
    @endif
</div>
