@extends('gazu.layout')
@section('title', '404 — GAZU')

@section('content')
@php
    $s = $gazuSettings ?? [];
    $title = $s['gazu_404_title'] ?? 'Запчастину не знайдено';
    $desc = $s['gazu_404_desc'] ?? 'Можливо, сторінку перенесли або URL застарів. Спробуйте знайти потрібну деталь через каталог.';
@endphp
<div class="gazu-container py-20 text-center">
    <div class="gazu-display font-bold text-[var(--gazu-ink)] m-0" style="font-size: 120px; letter-spacing: -0.05em; line-height: 1;">404</div>
    <h2 class="gazu-display text-2xl font-semibold mt-3 mb-2">{{ $title }}</h2>
    <p class="text-sm text-[var(--gazu-graphite)] max-w-md mx-auto mb-7">{{ $desc }}</p>
    <div class="flex gap-2 justify-center flex-wrap">
        <a wire:navigate href="{{ route('gazu.home') }}" class="gazu-btn-primary no-underline">На головну</a>
        <a wire:navigate href="{{ route('gazu.catalog') }}" class="gazu-btn-outline no-underline">Каталог</a>
    </div>
</div>
@endsection
