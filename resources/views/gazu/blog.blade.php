@extends('gazu.layout')
@section('title', \App\Support\SeoTemplates::title('blog'))
@section('description', \App\Support\SeoTemplates::description('blog'))

@php
    $imgKinds = ['oil','wiper','pad','bearing','spark','filter','bulb','shock'];
@endphp

@section('content')
<div class="gazu-container">
    <x-gazu.breadcrumbs :items="[['Головна', route('gazu.home')], 'Блог']"/>
    <h1 class="gazu-display text-4xl font-semibold m-0 mb-2">{{ $activeCategory ? (is_array($activeCategory->name) ? ($activeCategory->name['uk'] ?? 'Блог') : $activeCategory->name) : 'Блог' }}</h1>
    <p class="text-sm text-[var(--gazu-graphite)] mb-5">Гайди по обслуговуванню, новини та поради від наших майстрів</p>

    {{-- Рубрики (категорії блогу) — фільтр-чипи --}}
    @if(! empty($categories) && count($categories) > 0)
        <div class="flex flex-wrap gap-2 mb-7">
            <a wire:navigate href="{{ route('gazu.blog') }}"
               class="px-3.5 py-1.5 rounded-full text-[13px] no-underline transition-colors {{ ! $activeCategory ? 'bg-[var(--gazu-ink)] text-[var(--gazu-on-brand)]' : 'bg-[var(--gazu-surface)] border border-[var(--gazu-line)] text-[var(--gazu-graphite)] hover:border-[var(--gazu-line-2)]' }}">
                Усі статті
            </a>
            @foreach($categories as $c)
                @php $cName = is_array($c->name) ? ($c->name['uk'] ?? '') : $c->name; @endphp
                <a wire:navigate href="{{ route('gazu.blog.category', ['categorySlug' => $c->slug]) }}"
                   class="px-3.5 py-1.5 rounded-full text-[13px] no-underline transition-colors {{ $activeCategory && $activeCategory->id === $c->id ? 'bg-[var(--gazu-ink)] text-[var(--gazu-on-brand)]' : 'bg-[var(--gazu-surface)] border border-[var(--gazu-line)] text-[var(--gazu-graphite)] hover:border-[var(--gazu-line-2)]' }}">
                    {{ $cName }}<span class="opacity-60 ml-1">{{ $c->posts_count }}</span>
                </a>
            @endforeach
        </div>
    @endif

    @if(isset($posts) && $posts->isNotEmpty())
        <div class="grid md:grid-cols-3 gap-5">
            @foreach($posts as $i => $post)
                @php
                    $kind = $imgKinds[$i % count($imgKinds)];
                    $title = is_array($post->title) ? ($post->title['uk'] ?? '—') : ($post->title ?? '—');
                    $excerpt = is_array($post->excerpt ?? null) ? ($post->excerpt['uk'] ?? '') : ($post->excerpt ?? '');
                    $slug = $post->getLocalizedSlug('uk') ?: $post->id;
                    $catName = $post->blogCategory
                        ? (is_array($post->blogCategory->name) ? ($post->blogCategory->name['uk'] ?? 'Стаття') : $post->blogCategory->name)
                        : ($post->menu_group ?? 'Стаття');
                    $date = ($post->published_date)?->format('d.m.Y') ?? '';
                    $readMin = $post->reading_minutes;
                @endphp
                <a wire:navigate href="{{ route('gazu.blog.show', ['slug' => $slug]) }}"
                   class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg overflow-hidden no-underline text-[var(--gazu-ink)] flex flex-col hover:border-[var(--gazu-line-2)]">
                    @php
                        $img = $post->og_image;
                        // Handle three forms: full URL, absolute path "/img/...", relative "img/..." stored in /storage
                        $imgSrc = $img
                            ? (\Str::startsWith($img, ['http://','https://'])
                                ? $img
                                : ($img[0] === '/' ? asset(ltrim($img, '/')) : asset('storage/'.$img)))
                            : null;
                    @endphp
                    <div class="aspect-video bg-[var(--gazu-paper)] flex items-center justify-center">
                        @if($imgSrc)
                            <img src="{{ $imgSrc }}" alt="" class="w-full h-full object-cover">
                        @else
                            <x-gazu.part-image kind="{{ $kind }}" size="120"/>
                        @endif
                    </div>
                    <div class="p-5 flex flex-col gap-2 flex-1">
                        <div class="flex items-center gap-2 text-xs">
                            <span class="gazu-mono px-2 py-0.5 bg-[var(--gazu-mist)] text-[var(--gazu-blue)] rounded tracking-wider uppercase">{{ $catName }}</span>
                        </div>
                        <h3 class="gazu-display text-lg font-semibold m-0 leading-tight">{{ $title }}</h3>
                        @if($excerpt)
                            <p class="text-xs text-[var(--gazu-graphite)] m-0 line-clamp-3">{{ $excerpt }}</p>
                        @endif
                        <span class="flex-1"></span>
                        <div class="flex items-center gap-2 text-xs text-[var(--gazu-graphite)]">
                            @if($post->author)<span>{{ $post->author }}</span><span class="opacity-40">·</span>@endif
                            <span>{{ $date }}</span>
                            <span class="opacity-40">·</span>
                            <span>{{ $readMin }} хв</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        @if(method_exists($posts, 'lastPage') && $posts->lastPage() > 1)
            <div class="mt-6">{{ $posts->links("vendor.pagination.gazu") }}</div>
        @endif
    @else
        <div class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg p-12 text-center">
            <div class="inline-flex w-14 h-14 bg-[var(--gazu-mist)] rounded-full items-center justify-center mb-4">
                <x-gazu.icon name="search" size="24" stroke="var(--gazu-graphite)"/>
            </div>
            <h2 class="gazu-display text-xl font-semibold m-0 mb-2">Скоро тут будуть статті</h2>
            <p class="text-sm text-[var(--gazu-graphite)] max-w-md mx-auto mb-5">Готуємо гайди з підбору запчастин, сезонного обслуговування та інших корисних тем.</p>
            <a wire:navigate href="{{ route('gazu.catalog') }}" class="gazu-btn-outline no-underline">Поки що — каталог</a>
        </div>
    @endif
</div>
@endsection
