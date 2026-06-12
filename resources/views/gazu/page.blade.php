@extends('gazu.layout')

@php
    $title = is_array($page->title) ? ($page->title['uk'] ?? 'Сторінка') : (string) ($page->title ?? 'Сторінка');
    $content = is_array($page->content ?? null) ? ($page->content['uk'] ?? '') : (string) ($page->content ?? '');
    $excerpt = is_array($page->excerpt ?? null) ? ($page->excerpt['uk'] ?? '') : (string) ($page->excerpt ?? '');
    $pageMetaTitle = $page->meta_title ? (is_array($page->meta_title) ? ($page->meta_title['uk'] ?? '') : (string) $page->meta_title) : '';
    $pageMetaDescription = $page->meta_description ? (is_array($page->meta_description) ? ($page->meta_description['uk'] ?? '') : (string) $page->meta_description) : '';
    $slugUk = is_array($page->slug) ? ($page->slug['uk'] ?? '') : (string) $page->slug;
    $isNarrow = ($page->layout ?? 'full') !== 'full';
@endphp

@section('title', $pageMetaTitle !== '' ? $pageMetaTitle : \App\Support\SeoTemplates::title('page', ['name' => $title]))
@section('description', $pageMetaDescription !== '' ? $pageMetaDescription : ($excerpt ?: \App\Support\SeoTemplates::description('page', ['name' => $title])))

@if(! ($page->is_indexable ?? true))
    @section('robots', 'noindex,' . (($page->is_followable ?? true) ? 'follow' : 'nofollow'))
@endif

@section('content')
{{-- Зона «Сторінка — верх»: блоки з Конструктора зон (OpenCart-стиль).
     Блок можна обмежити конкретними сторінками через config.pages. --}}
@hookAction('layout.page.top', $slugUk)

<div class="gazu-container py-8 {{ $isNarrow ? 'max-w-3xl' : '' }}">
    <x-gazu.breadcrumbs :items="[['Головна', route('gazu.home')], $title]"/>
    <h1 class="gazu-display text-4xl font-semibold text-[var(--gazu-ink)] m-0 mb-3">{{ $title }}</h1>

    @if($excerpt !== '')
        <p class="text-base text-[var(--gazu-graphite)] leading-relaxed mb-7 max-w-2xl">{{ $excerpt }}</p>
    @endif

    @if($content !== '')
        <article class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-xl p-8 text-[15px] leading-relaxed text-[var(--gazu-ink)] lb-html-content">
            {!! $content !!}
        </article>
    @endif
</div>

{{-- Зона «Сторінка — низ» --}}
@hookAction('layout.page.bottom', $slugUk)
@endsection
