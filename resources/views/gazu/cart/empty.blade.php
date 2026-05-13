@extends('gazu.layout')
@section('title', 'Порожній кошик — GAZU')

@section('content')
@php
    $s = $gazuSettings ?? [];
    $title = $s['gazu_cart_empty_title'] ?? 'Кошик порожній';
    $desc = $s['gazu_cart_empty_desc'] ?? 'Додайте товари з каталогу, щоб знайти точні запчастини для свого авто.';
@endphp
<div class="gazu-container py-20 text-center">
    <div class="inline-flex w-20 h-20 bg-[var(--gazu-mist)] rounded-full items-center justify-center mb-5 text-[var(--gazu-blue)]">
        <x-gazu.icon name="cart" size="36"/>
    </div>
    <h1 class="gazu-display text-3xl font-semibold text-[var(--gazu-ink)] m-0 mb-2">{{ $title }}</h1>
    <p class="text-sm text-[var(--gazu-graphite)] max-w-md mx-auto mb-7">{{ $desc }}</p>
    <div class="flex gap-2 justify-center">
        <a wire:navigate href="{{ route('gazu.catalog') }}" class="gazu-btn-primary no-underline">До каталогу</a>
    </div>
</div>
@endsection
