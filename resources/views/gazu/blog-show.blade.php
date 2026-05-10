@extends('gazu.layout')

@php
    $title = is_array($page->title) ? ($page->title['uk'] ?? 'Стаття') : ($page->title ?? 'Стаття');
    $content = is_array($page->content ?? null) ? ($page->content['uk'] ?? '') : ($page->content ?? '');
    $excerpt = is_array($page->excerpt ?? null) ? ($page->excerpt['uk'] ?? '') : ($page->excerpt ?? '');
    $cat = $page->menu_group ?? 'Стаття';
    $date = $page->created_at?->format('d.m.Y');
    $img = $page->og_image;
@endphp

@section('title', $title.' — GAZU блог')

@section('content')
<div class="gazu-container">
    <x-gazu.breadcrumbs :items="[
        ['Головна', route('gazu.home')],
        ['Блог', route('gazu.blog')],
        $title,
    ]"/>

    <article class="max-w-3xl mx-auto">
        <div class="flex items-center gap-3 mb-4 text-xs">
            <span class="gazu-mono px-2 py-0.5 bg-[var(--gazu-mist)] text-[var(--gazu-blue)] rounded tracking-wider uppercase">{{ $cat }}</span>
            @if($date)<span class="text-[var(--gazu-graphite)]">· {{ $date }}</span>@endif
        </div>

        <h1 class="gazu-display text-4xl font-bold leading-tight m-0 mb-4">{{ $title }}</h1>

        @if($excerpt)
            <p class="text-lg text-[var(--gazu-graphite)] leading-relaxed mb-6">{{ $excerpt }}</p>
        @endif

        @if($img)
            <div class="aspect-video rounded-xl overflow-hidden mb-6 bg-[var(--gazu-paper)]">
                <img src="{{ \Str::startsWith($img, 'http') ? $img : asset('storage/'.$img) }}" alt="" class="w-full h-full object-cover">
            </div>
        @endif

        <div class="prose max-w-none text-[var(--gazu-ink)] leading-relaxed">
            {!! $content !!}
        </div>

        <div class="mt-10 pt-6 border-t border-[var(--gazu-line)] flex items-center justify-between">
            <a wire:navigate href="{{ route('gazu.blog') }}" class="gazu-btn-outline no-underline">← Усі статті</a>
            <div class="flex gap-2">
                <a wire:navigate href="{{ route('gazu.catalog') }}" class="gazu-btn-primary no-underline">Перейти до каталогу</a>
            </div>
        </div>
    </article>
</div>
@endsection
