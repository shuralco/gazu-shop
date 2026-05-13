@extends('gazu.layout')
@section('title', $title . ' — GAZU')
@section('description', $intro ?? $title)

@section('content')
<div class="gazu-container py-8 max-w-3xl">
    <x-gazu.breadcrumbs :items="[['Головна', route('gazu.home')], $title]"/>
    <h1 class="gazu-display text-4xl font-semibold text-[var(--gazu-ink)] m-0 mb-3">{{ $title }}</h1>
    @if($intro ?? false)
        <p class="text-base text-[var(--gazu-graphite)] leading-relaxed mb-7 max-w-2xl">{{ $intro }}</p>
    @endif

    <article class="bg-white border border-[var(--gazu-line)] rounded-xl p-8 space-y-5 text-[15px] leading-relaxed text-[var(--gazu-ink)]">
        @if(! empty($content_html ?? null))
            <div class="prose max-w-none">{!! $content_html !!}</div>
        @endif
        @foreach($sections ?? [] as $sec)
            @if(isset($sec['title']))
                <h2 class="gazu-display text-xl font-semibold m-0 mt-2">{{ $sec['title'] }}</h2>
            @endif
            @if(isset($sec['body']))
                <div class="text-[var(--gazu-graphite)]">{!! nl2br(e($sec['body'])) !!}</div>
            @endif
            @if(isset($sec['list']) && is_array($sec['list']))
                <ul class="list-disc list-inside text-[var(--gazu-graphite)] space-y-1.5">
                    @foreach($sec['list'] as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
            @endif
        @endforeach
    </article>

    <div class="mt-6 text-sm text-[var(--gazu-muted)]">
        Оновлено: {{ \Carbon\Carbon::parse($updated ?? now())->translatedFormat('d F Y') }}
    </div>
</div>
@endsection
