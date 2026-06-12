@extends('gazu.layout')

@php
    $title = is_array($page->title) ? ($page->title['uk'] ?? 'Стаття') : ($page->title ?? 'Стаття');
    $content = is_array($page->content ?? null) ? ($page->content['uk'] ?? '') : ($page->content ?? '');
    $excerpt = is_array($page->excerpt ?? null) ? ($page->excerpt['uk'] ?? '') : ($page->excerpt ?? '');
    $cat = $page->blogCategory
        ? (is_array($page->blogCategory->name) ? ($page->blogCategory->name['uk'] ?? 'Стаття') : $page->blogCategory->name)
        : ($page->menu_group ?? 'Стаття');
    $catSlug = $page->blogCategory?->slug;
    $date = ($page->published_date)?->format('d.m.Y');
    $img = $page->og_image;
    $readingMin = $page->reading_minutes;
    $author = $page->author;
    $views = $page->views ?? 0;
@endphp

@php
    $postMetaTitle = $page->meta_title ? (is_array($page->meta_title) ? ($page->meta_title['uk'] ?? '') : $page->meta_title) : '';
@endphp
@section('title', $postMetaTitle !== '' ? $postMetaTitle : \App\Support\SeoTemplates::title('blog_post', ['name' => $title]))
@section('description', ($excerpt !== '' ? $excerpt : \App\Support\SeoTemplates::description('blog_post', ['name' => $title])))

@section('content')
<div class="gazu-container py-6 sm:py-10">
    <x-gazu.breadcrumbs :items="[
        ['Головна', route('gazu.home')],
        ['Блог', route('gazu.blog')],
        $title,
    ]"/>

    <article class="max-w-5xl mx-auto">
        <div class="flex items-center gap-3 mb-5 text-xs flex-wrap">
            @if($catSlug)
                <a wire:navigate href="{{ route('gazu.blog.category', ['categorySlug' => $catSlug]) }}" class="gazu-mono px-2 py-0.5 bg-[var(--gazu-mist)] text-[var(--gazu-blue)] rounded tracking-wider uppercase no-underline hover:bg-[var(--gazu-line)]">{{ $cat }}</a>
            @else
                <span class="gazu-mono px-2 py-0.5 bg-[var(--gazu-mist)] text-[var(--gazu-blue)] rounded tracking-wider uppercase">{{ $cat }}</span>
            @endif
            @if($author)<span class="text-[var(--gazu-graphite)]">· {{ $author }}</span>@endif
            @if($date)<span class="text-[var(--gazu-graphite)]">· {{ $date }}</span>@endif
            @if($readingMin)<span class="text-[var(--gazu-graphite)]">· {{ $readingMin }} хв читання</span>@endif
            @if($views > 0)<span class="text-[var(--gazu-graphite)]">· {{ $views }} переглядів</span>@endif
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

        {{-- Схожі статті --}}
        @if(! empty($related) && count($related) > 0)
            <div class="mt-12 pt-8 border-t border-[var(--gazu-line)]">
                <h2 class="gazu-display text-2xl font-semibold m-0 mb-5">Читайте також</h2>
                <div class="grid sm:grid-cols-3 gap-5">
                    @foreach($related as $r)
                        @php
                            $rTitle = is_array($r->title) ? ($r->title['uk'] ?? '—') : ($r->title ?? '—');
                            $rSlug = $r->getLocalizedSlug('uk') ?: $r->id;
                            $rImg = $r->og_image;
                            $rImgSrc = $rImg
                                ? (\Str::startsWith($rImg, ['http://','https://']) ? $rImg : ($rImg[0] === '/' ? asset(ltrim($rImg, '/')) : asset('storage/'.$rImg)))
                                : null;
                        @endphp
                        <a wire:navigate href="{{ route('gazu.blog.show', ['slug' => $rSlug]) }}"
                           class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg overflow-hidden no-underline text-[var(--gazu-ink)] flex flex-col hover:border-[var(--gazu-line-2)]">
                            <div class="aspect-video bg-[var(--gazu-paper)] flex items-center justify-center">
                                @if($rImgSrc)<img src="{{ $rImgSrc }}" alt="" class="w-full h-full object-cover">@else<x-gazu.icon name="box" size="40" stroke="var(--gazu-line-2)"/>@endif
                            </div>
                            <div class="p-4">
                                <h3 class="gazu-display text-[15px] font-semibold m-0 leading-snug line-clamp-2">{{ $rTitle }}</h3>
                                <div class="text-xs text-[var(--gazu-graphite)] mt-1.5">{{ ($r->published_date)?->format('d.m.Y') }} · {{ $r->reading_minutes }} хв</div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </article>
</div>
@endsection
