@php
    $s = $gazuSettings ?? [];
    $cities = $s['gazu_topbar_cities'] ?? ($shopStats['cities_with_count'] ?? 'Україна');
    $hours = $s['gazu_topbar_hours'] ?? 'Пн-Нд 8:00–20:00';
    $links = $s['gazu_topbar_links'] ?? [
        ['label' => 'Гуртом', 'url' => route('gazu.wholesale')],
        ['label' => 'Доставка та оплата', 'url' => route('gazu.delivery')],
        ['label' => 'Гарантія', 'url' => route('gazu.warranty')],
        ['label' => 'Контакти', 'url' => route('gazu.contacts')],
    ];
@endphp
{{-- GAZU top bar — тонка темна смуга з адресами/посиланнями. Редагується у /admin/gazu-visual --}}
<div class="bg-[var(--gazu-ink)] text-[#CDD3DC] text-xs">
    <div class="gazu-container py-2 flex items-center gap-6">
        @if($cities)
            <span class="inline-flex items-center gap-1.5"><x-gazu.icon name="location" size="14"/> {{ $cities }}</span>
        @endif
        @if($hours)
            <span class="hidden md:inline">{{ $hours }}</span>
        @endif
        <span class="flex-1"></span>
        @foreach((array) $links as $link)
            <a wire:navigate href="{{ $link['url'] ?? '#' }}" class="hidden md:inline text-[#CDD3DC] no-underline hover:text-white">{{ $link['label'] ?? '' }}</a>
        @endforeach
        <span class="hidden md:inline text-[#5A6573]">|</span>
        <span class="text-white">UA</span><span class="text-[#5A6573]">RU</span>
    </div>
</div>
