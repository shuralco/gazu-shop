@php
    $count = $count ?? 0;
    $view = $view ?? 'grid';
    $currentSort = $currentSort ?? 'popular';
    $sortLabels = [
        'popular' => 'Популярні',
        'price-asc' => 'Дешевше',
        'price-desc' => 'Дорожче',
        'new' => 'Нові',
    ];
@endphp
<div class="bg-white border border-[var(--gazu-line)] rounded-lg px-3.5 py-2.5 flex items-center gap-3 text-[13px] whitespace-nowrap font-text relative" x-data="{ openSort: false }">
    <span class="text-[var(--gazu-graphite)]"><span class="text-[var(--gazu-ink)] font-semibold">{{ $count }}</span> товарів</span>
    <span class="flex-1"></span>
    <span class="text-[var(--gazu-graphite)] hidden sm:inline">Сорт:</span>
    <div class="relative">
        <button type="button" @click="openSort = !openSort" @click.outside="openSort = false"
                class="px-2.5 py-1.5 bg-[var(--gazu-paper)] border border-[var(--gazu-line)] rounded text-[var(--gazu-ink)] inline-flex items-center gap-1.5 cursor-pointer">
            {{ $sortLabels[$currentSort] ?? 'Популярні' }} <x-gazu.icon name="chevron" size="14"/>
        </button>
        <div x-show="openSort" x-cloak x-transition.opacity
             class="absolute top-full right-0 mt-1 bg-white border border-[var(--gazu-line)] rounded-lg shadow-lg z-30 min-w-[160px] overflow-hidden">
            @foreach($sortLabels as $key => $label)
                <a href="{{ request()->fullUrlWithQuery(['sort' => $key, 'page' => null]) }}"
                   class="block px-3 py-2 text-[13px] no-underline {{ $currentSort === $key ? 'bg-[var(--gazu-paper)] text-[var(--gazu-ink)] font-medium' : 'text-[var(--gazu-graphite)] hover:bg-[var(--gazu-paper)]' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>
    <div class="flex border border-[var(--gazu-line)] rounded overflow-hidden">
        <a href="{{ request()->fullUrlWithQuery(['view' => 'grid']) }}"
           class="p-2 {{ $view === 'grid' ? 'bg-[var(--gazu-ink)] text-white' : 'bg-white text-[var(--gazu-graphite)]' }} flex items-center cursor-pointer no-underline">
            <x-gazu.icon name="grid" size="14"/>
        </a>
        <a href="{{ request()->fullUrlWithQuery(['view' => 'list']) }}"
           class="p-2 {{ $view === 'list' ? 'bg-[var(--gazu-ink)] text-white' : 'bg-white text-[var(--gazu-graphite)]' }} flex items-center border-l border-[var(--gazu-line)] cursor-pointer no-underline">
            <x-gazu.icon name="list" size="14"/>
        </a>
    </div>
</div>
