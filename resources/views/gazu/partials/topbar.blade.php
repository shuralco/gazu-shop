@php
    $s = $gazuSettings ?? [];
    $cities = $s['gazu_topbar_cities'] ?? ($shopStats['cities_with_count'] ?? 'Україна');
    $hours = $s['gazu_topbar_hours'] ?? 'Пн-Нд 8:00–20:00';

    // Fallback map: коли admin зберіг label без URL — підставляємо з label-to-route.
    $labelToRoute = [
        'Гуртом'             => 'gazu.wholesale',
        'Гуртовим клієнтам'  => 'gazu.wholesale',
        'Доставка та оплата' => 'gazu.delivery',
        'Доставка'           => 'gazu.delivery',
        'Гарантія'           => 'gazu.warranty',
        'Гарантія та повернення' => 'gazu.warranty',
        'Контакти'           => 'gazu.contacts',
        'Про нас'            => 'gazu.about',
        'Блог'               => 'gazu.blog',
        'СТО'                => 'gazu.sto',
        'FAQ'                => 'gazu.faq',
    ];

    $rawLinks = $s['gazu_topbar_links'] ?? [
        ['label' => 'Гуртом', 'url' => route('gazu.wholesale')],
        ['label' => 'Доставка та оплата', 'url' => route('gazu.delivery')],
        ['label' => 'Гарантія', 'url' => route('gazu.warranty')],
        ['label' => 'Контакти', 'url' => route('gazu.contacts')],
    ];

    // Normalize: завжди мати валідний href. Pusty url + знайомий label → route.
    $links = collect((array) $rawLinks)
        ->map(function ($link) use ($labelToRoute) {
            $label = trim((string) ($link['label'] ?? ''));
            $url = trim((string) ($link['url'] ?? ''));
            if ($url === '' || $url === '#') {
                $routeName = $labelToRoute[$label] ?? null;
                if ($routeName && \Illuminate\Support\Facades\Route::has($routeName)) {
                    $url = route($routeName);
                }
            }
            return ['label' => $label, 'url' => $url ?: null];
        })
        ->filter(fn ($l) => $l['label'] !== '' && $l['url'] !== null)
        ->values()
        ->all();
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
        @foreach($links as $link)
            <a wire:navigate href="{{ $link['url'] }}" class="hidden md:inline text-[#CDD3DC] no-underline hover:text-white">{{ $link['label'] }}</a>
        @endforeach
        {{-- Language switcher hidden: проєкт зараз UA-only --}}
    </div>
</div>
