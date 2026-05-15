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

@php
    // Rough reading time — ~200 wpm; str_word_count doesn't count Cyrillic, use a unicode-aware regex.
    $plain = trim(strip_tags((string) $content));
    $wordCount = $plain !== '' ? preg_match_all('/[\pL\pN]+/u', $plain) : 0;
    $readingMin = $wordCount > 200 ? max(1, (int) round($wordCount / 200)) : null;
@endphp

@section('content')
<div class="gazu-container py-6 sm:py-10">
    <x-gazu.breadcrumbs :items="[
        ['Головна', route('gazu.home')],
        ['Блог', route('gazu.blog')],
        $title,
    ]"/>

    <article class="max-w-3xl mx-auto">
        <div class="flex items-center gap-3 mb-5 text-xs flex-wrap">
            <span class="gazu-mono px-2 py-0.5 bg-[var(--gazu-mist)] text-[var(--gazu-blue)] rounded tracking-wider uppercase">{{ $cat }}</span>
            @if($date)<span class="text-[var(--gazu-graphite)]">· {{ $date }}</span>@endif
            @if($readingMin)<span class="text-[var(--gazu-graphite)]">· {{ $readingMin }} хв читання</span>@endif
        </div>

        <h1 class="gazu-display text-[32px] sm:text-[40px] font-bold leading-[1.15] tracking-[-0.01em] m-0 mb-5 text-[var(--gazu-ink)]">{{ $title }}</h1>

        @if($excerpt)
            <p class="text-[18px] text-[var(--gazu-graphite)] leading-relaxed mb-7 max-w-[60ch]">{{ $excerpt }}</p>
        @endif

        @php
            $imgSrc = $img
                ? (\Str::startsWith($img, ['http://','https://'])
                    ? $img
                    : ($img[0] === '/' ? asset(ltrim($img, '/')) : asset('storage/'.$img)))
                : null;
        @endphp
        @if($imgSrc)
            <figure class="aspect-video rounded-xl overflow-hidden mb-8 bg-[var(--gazu-paper)]">
                <img src="{{ $imgSrc }}" alt="{{ $title }}" loading="eager" class="w-full h-full object-cover">
            </figure>
        @endif

        <div class="gazu-prose">
            {!! $content !!}
        </div>

        <div class="mt-12 pt-6 border-t border-[var(--gazu-line)] flex items-center justify-between flex-wrap gap-3">
            <a wire:navigate href="{{ route('gazu.blog') }}" class="gazu-btn-outline no-underline">← Усі статті</a>
            <div class="flex gap-2">
                <a wire:navigate href="{{ route('gazu.catalog') }}" class="gazu-btn-primary no-underline">Перейти до каталогу</a>
            </div>
        </div>
    </article>
</div>
@endsection
