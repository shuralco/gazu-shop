@extends('gazu.layout')
@section('title', 'Усі бренди — GAZU')

@section('content')
<div class="gazu-container">
    <x-gazu.breadcrumbs :items="[['Головна', route('gazu.home')], 'Усі бренди']"/>
    <h1 class="gazu-display text-4xl font-semibold m-0 mb-2">Бренди</h1>
    <p class="text-sm text-[var(--gazu-graphite)] mb-7">{{ plural_uk_count($brands->count(), 'бренд', 'бренди', 'брендів') }} у каталозі</p>

    @if($brands->isEmpty())
        <div class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg p-10 text-center">
            <div class="gazu-display text-xl font-semibold mb-2">Брендів поки немає</div>
            <p class="text-sm text-[var(--gazu-graphite)]">Адміністратор може додати бренди у Filament адмінці.</p>
        </div>
    @else
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
            @foreach($brands as $b)
                <a wire:navigate href="{{ route('gazu.brand', ['slug' => $b->slug ?: \Str::slug($b->name)]) }}"
                   class="bg-[var(--gazu-surface)] border border-[var(--gazu-line)] rounded-lg p-5 no-underline text-[var(--gazu-ink)] hover:border-[var(--gazu-line-2)] flex flex-col items-center justify-center gap-2 aspect-[5/3]">
                    <div class="gazu-display font-bold text-lg text-center">{{ $b->name }}</div>
                    @if(($b->products_count ?? 0) > 0)
                        <div class="text-xs text-[var(--gazu-graphite)] gazu-mono">{{ plural_uk_count((int) $b->products_count, 'товар', 'товари', 'товарів') }}</div>
                    @endif
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
