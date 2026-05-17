@extends('gazu.layout')
@section('title', 'Порівняння товарів — GAZU')
@section('description', 'Порівняйте характеристики, ціни та наявність обраних товарів.')

@section('content')
<section class="gazu-container py-10">
    <div class="flex items-end justify-between mb-7 flex-wrap gap-3">
        <div>
            <div class="gazu-mono text-[11px] text-[var(--gazu-blue)] tracking-widest uppercase mb-2">Порівняння</div>
            <h1 class="gazu-display text-[28px] sm:text-[36px] font-semibold text-[var(--gazu-ink)] m-0 leading-tight">
                Порівняння товарів <span class="text-[var(--gazu-graphite)] text-base font-normal">({{ $products->count() }})</span>
            </h1>
        </div>
        @if($products->isNotEmpty())
            <button type="button" onclick="document.cookie='gazu_compare=; path=/; max-age=0'; window.location.reload()"
                    class="text-[13px] text-[var(--gazu-graphite)] hover:text-[var(--gazu-danger)] bg-transparent border-0 cursor-pointer inline-flex items-center gap-1.5">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
                Очистити порівняння
            </button>
        @endif
    </div>

    @if($products->isEmpty())
        <div class="bg-white border border-[var(--gazu-line)] rounded-lg p-10 text-center">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--gazu-graphite)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-3 opacity-40"><path d="M3 6h18"/><path d="M3 12h18"/><path d="M3 18h18"/></svg>
            <div class="gazu-display text-2xl font-semibold mb-2 text-[var(--gazu-ink)]">Список порівняння порожній</div>
            <p class="text-sm text-[var(--gazu-graphite)] mb-5 max-w-md mx-auto">Додавайте товари до порівняння через кнопку «Порівняти» у карточці товару — і повертайтесь сюди коли наберете 2-4 шт.</p>
            <a wire:navigate href="{{ route('gazu.catalog') }}" class="gazu-btn-primary no-underline">Перейти до каталогу</a>
        </div>
    @else
        <div class="overflow-x-auto -mx-4 sm:mx-0">
            <table class="w-full min-w-[640px] border-collapse text-sm">
                <thead>
                    <tr>
                        <th class="w-[180px] p-3 text-left text-[12px] text-[var(--gazu-muted)] gazu-mono tracking-wider uppercase">Параметр</th>
                        @foreach($products as $p)
                            @php
                                $title = is_array($p->title) ? ($p->title['uk'] ?? $p->name) : ($p->title ?: $p->name);
                                $slug = is_array($p->slug) ? ($p->slug['uk'] ?? $p->id) : ($p->slug ?: $p->id);
                            @endphp
                            <th class="p-3 text-left bg-white border border-[var(--gazu-line)] align-top">
                                <a wire:navigate href="{{ url('/'.$slug) }}" class="no-underline">
                                    <div class="aspect-square bg-[var(--gazu-paper)] rounded-md overflow-hidden mb-3 flex items-center justify-center">
                                        <x-gazu.part-image kind="filter" :seed="$p->id" fit/>
                                    </div>
                                    <div class="text-[14px] font-medium text-[var(--gazu-ink)] leading-snug line-clamp-2 mb-1">{{ $title }}</div>
                                </a>
                                <div class="gazu-mono text-[11px] text-[var(--gazu-graphite)] mb-2">{{ $p->sku }}</div>
                                <button type="button"
                                        onclick="(function(id){var c=document.cookie.match(/(?:^|; )gazu_compare=([^;]+)/);var ids=c?c[1].split(','):[];ids=ids.filter(x=>x!=String(id));document.cookie='gazu_compare='+ids.join(',')+'; path=/; max-age='+(60*60*24*30);window.location.reload();})({{ $p->id }})"
                                        class="text-[11px] text-[var(--gazu-muted)] hover:text-[var(--gazu-danger)] bg-transparent border-0 cursor-pointer underline">прибрати</button>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    {{-- Brand --}}
                    <tr>
                        <td class="p-3 font-medium text-[var(--gazu-graphite)] bg-[var(--gazu-paper)] border-l border-[var(--gazu-line)]">Бренд</td>
                        @foreach($products as $p)
                            @php $brandName = $p->brand?->name ?: $p->manufacturer ?: '—'; @endphp
                            <td class="p-3 bg-white border border-[var(--gazu-line)]">{{ $brandName }}</td>
                        @endforeach
                    </tr>
                    {{-- Price --}}
                    <tr>
                        <td class="p-3 font-medium text-[var(--gazu-graphite)] bg-[var(--gazu-paper)] border-l border-[var(--gazu-line)]">Ціна</td>
                        @foreach($products as $p)
                            <td class="p-3 bg-white border border-[var(--gazu-line)] gazu-display font-bold text-[var(--gazu-ink)]">{{ number_format((float) $p->price, 0, '.', ' ') }} ₴</td>
                        @endforeach
                    </tr>
                    {{-- Stock --}}
                    <tr>
                        <td class="p-3 font-medium text-[var(--gazu-graphite)] bg-[var(--gazu-paper)] border-l border-[var(--gazu-line)]">Наявність</td>
                        @foreach($products as $p)
                            <td class="p-3 bg-white border border-[var(--gazu-line)] text-[13px]">
                                @if(($p->quantity ?? 0) > 0)
                                    <span class="text-[var(--gazu-success)]">● В наявності</span>
                                @else
                                    <span class="text-[var(--gazu-graphite)]">○ Під замовлення</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                    {{-- Rating --}}
                    <tr>
                        <td class="p-3 font-medium text-[var(--gazu-graphite)] bg-[var(--gazu-paper)] border-l border-[var(--gazu-line)]">Рейтинг</td>
                        @foreach($products as $p)
                            <td class="p-3 bg-white border border-[var(--gazu-line)] text-[13px]">
                                @if(($p->rating ?? 0) > 0)
                                    {{ number_format((float) $p->rating, 1) }} <span class="text-[var(--gazu-muted)]">({{ $p->reviews_count ?? 0 }})</span>
                                @else
                                    —
                                @endif
                            </td>
                        @endforeach
                    </tr>
                    {{-- Specs (union) --}}
                    @foreach($specRows as $key => $valuesByPid)
                        <tr>
                            <td class="p-3 font-medium text-[var(--gazu-graphite)] bg-[var(--gazu-paper)] border-l border-[var(--gazu-line)]">{{ $key }}</td>
                            @foreach($products as $p)
                                <td class="p-3 bg-white border border-[var(--gazu-line)] text-[13px]">{{ $valuesByPid[$p->id] ?? '—' }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                    {{-- Buy buttons --}}
                    <tr>
                        <td class="p-3 bg-[var(--gazu-paper)] border-l border-[var(--gazu-line)]"></td>
                        @foreach($products as $p)
                            @php $slug2 = is_array($p->slug) ? ($p->slug['uk'] ?? $p->id) : ($p->slug ?: $p->id); @endphp
                            <td class="p-3 bg-white border border-[var(--gazu-line)]">
                                <a wire:navigate href="{{ url('/'.$slug2) }}" class="block w-full text-center py-2 bg-[var(--gazu-ink)] hover:bg-[var(--gazu-ink-2)] text-white rounded-md text-[13px] font-medium no-underline transition-colors">Деталі</a>
                            </td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>
    @endif
</section>
@endsection
