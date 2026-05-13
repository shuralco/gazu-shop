@extends('gazu.layout')
@section('title', 'Блог — GAZU')

@php
    $imgKinds = ['oil','wiper','pad','bearing','spark','filter','bulb','shock'];
    $defaultPosts = [
        ['title' => 'Як обрати масляний фільтр для двигуна TSI', 'cat' => 'Гайд', 'kind' => 'oil', 'read' => '8 хв', 'date' => '12.04.2024', 'slug' => null],
        ['title' => 'Підготовка авто до зими: чек-лист', 'cat' => 'Сезонне', 'kind' => 'wiper', 'read' => '12 хв', 'date' => '01.10.2024', 'slug' => null],
        ['title' => 'Заміна гальмівних колодок самостійно', 'cat' => 'Майстерня', 'kind' => 'pad', 'read' => '15 хв', 'date' => '22.03.2024', 'slug' => null],
        ['title' => 'Чим відрізняються оригінал та аналоги?', 'cat' => 'FAQ', 'kind' => 'bearing', 'read' => '6 хв', 'date' => '14.03.2024', 'slug' => null],
        ['title' => 'ТОП-5 виробників фільтрів 2024', 'cat' => 'Огляд', 'kind' => 'filter', 'read' => '10 хв', 'date' => '20.02.2024', 'slug' => null],
    ];
@endphp

@section('content')
<div class="gazu-container">
    <x-gazu.breadcrumbs :items="[['Головна', route('gazu.home')], 'Блог']"/>
    <h1 class="gazu-display text-4xl font-semibold m-0 mb-2">Блог</h1>
    <p class="text-sm text-[var(--gazu-graphite)] mb-7">Гайди по обслуговуванню, новини та поради від наших майстрів</p>

    @if(isset($posts) && $posts->isNotEmpty())
        <div class="grid md:grid-cols-3 gap-5">
            @foreach($posts as $i => $post)
                @php
                    $kind = $imgKinds[$i % count($imgKinds)];
                    $title = is_array($post->title) ? ($post->title['uk'] ?? '—') : ($post->title ?? '—');
                    $excerpt = is_array($post->excerpt ?? null) ? ($post->excerpt['uk'] ?? '') : ($post->excerpt ?? '');
                    $slug = $post->getLocalizedSlug('uk') ?: $post->id;
                    $cat = $post->menu_group ?? 'Стаття';
                    $date = $post->created_at?->format('d.m.Y') ?? '';
                @endphp
                <a wire:navigate href="{{ route('gazu.blog.show', ['slug' => $slug]) }}"
                   class="bg-white border border-[var(--gazu-line)] rounded-lg overflow-hidden no-underline text-[var(--gazu-ink)] flex flex-col hover:border-[var(--gazu-line-2)]">
                    <div class="aspect-video bg-[var(--gazu-paper)] flex items-center justify-center">
                        @if(! empty($post->og_image))
                            <img src="{{ \Str::startsWith($post->og_image, 'http') ? $post->og_image : asset('storage/'.$post->og_image) }}" alt="" class="w-full h-full object-cover">
                        @else
                            <x-gazu.part-image kind="{{ $kind }}" size="120"/>
                        @endif
                    </div>
                    <div class="p-5 flex flex-col gap-2 flex-1">
                        <div class="flex items-center gap-2 text-xs">
                            <span class="gazu-mono px-2 py-0.5 bg-[var(--gazu-mist)] text-[var(--gazu-blue)] rounded tracking-wider uppercase">{{ $cat }}</span>
                        </div>
                        <h3 class="gazu-display text-lg font-semibold m-0 leading-tight">{{ $title }}</h3>
                        @if($excerpt)
                            <p class="text-xs text-[var(--gazu-graphite)] m-0 line-clamp-3">{{ $excerpt }}</p>
                        @endif
                        <span class="flex-1"></span>
                        <div class="text-xs text-[var(--gazu-graphite)]">{{ $date }}</div>
                    </div>
                </a>
            @endforeach
        </div>

        @if(method_exists($posts, 'lastPage') && $posts->lastPage() > 1)
            <div class="mt-6">{{ $posts->links("vendor.pagination.gazu") }}</div>
        @endif
    @else
        {{-- Fallback демо-картки якщо в БД немає Page з template=blog_post --}}
        <div class="bg-[var(--gazu-mist)] rounded-lg p-4 mb-6 text-sm text-[var(--gazu-graphite)]">
            <strong>Адміністратору:</strong> щоб реальні статті відображались тут, додайте сторінки з полем <code>template = "blog_post"</code> у <a href="{{ url('/admin/pages') }}" class="text-[var(--gazu-blue)]">/admin/pages</a>.
        </div>
        <div class="grid md:grid-cols-3 gap-5">
            @foreach($defaultPosts as $post)
                <article class="bg-white border border-[var(--gazu-line)] rounded-lg overflow-hidden no-underline text-[var(--gazu-ink)] flex flex-col">
                    <div class="aspect-video bg-[var(--gazu-paper)] flex items-center justify-center">
                        <x-gazu.part-image kind="{{ $post['kind'] }}" size="120"/>
                    </div>
                    <div class="p-5 flex flex-col gap-2 flex-1">
                        <div class="flex items-center gap-2 text-xs">
                            <span class="gazu-mono px-2 py-0.5 bg-[var(--gazu-mist)] text-[var(--gazu-blue)] rounded tracking-wider uppercase">{{ $post['cat'] }}</span>
                            <span class="text-[var(--gazu-graphite)]">· {{ $post['read'] }}</span>
                        </div>
                        <h3 class="gazu-display text-lg font-semibold m-0 leading-tight">{{ $post['title'] }}</h3>
                        <span class="flex-1"></span>
                        <div class="text-xs text-[var(--gazu-graphite)]">{{ $post['date'] }}</div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>
@endsection
